<?php

namespace App\Http\Controllers;

use App\Models\Operator;
use App\Models\OperatorAdmin;
use App\Models\OperatorAudit;
use App\Models\OperatorInvite;
use App\Services\GraphMailService;
use App\Support\ConversationLog;
use App\Support\ShopAccess;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\Str;

/**
 * Platform admin: email shops an invite to manage their own page (Zach,
 * 2026-11-22: "send an email to every single one of them with an admin
 * link").
 *
 * How it is sent, and why not the newsletter tool:
 *   - It goes out from the real support@ mailbox (GraphMailService), the
 *     same way Message management sends email, and is logged to the same
 *     conversation threads. A shop's reply lands in Message management.
 *   - The newsletter tool is for subscribed divers: its recipients are
 *     users with newsletter_subscribed, and its unsubscribe link is signed
 *     for a user. A shop's contact address is neither.
 *
 * Rules built in:
 *   - The link is made and the email sent in one step. Only a link's hash
 *     is ever stored, so a link cannot be looked up again later.
 *   - One live link per shop. Sending again to a shop that still has an
 *     unused link from here does nothing unless "send again" is ticked,
 *     and then the old link is switched off first. A failed send switches
 *     its link off.
 *   - A real send is only possible after a test of the exact same wording
 *     has gone to the admin's own inbox.
 *   - Shops already managed, shops that asked not to be emailed, and
 *     shops with no address are never recipients.
 *   - Every email says who it is from and how to stop them.
 *
 * Nothing new in the database: a send and an opt-out are rows in
 * operator_admin_audit (actions outreach.sent, outreach.failed,
 * outreach.opt_out), and the link is an ordinary operator_invites row.
 *
 * Movement (Zach, 2026-11-22): what a shop did after being emailed is
 * recorded the same way and shown in Shop management.
 *   outreach.opened  the email's hidden image was loaded. A HINT, not
 *                    proof: some mail apps load images before anyone
 *                    looks, and others never load them at all.
 *   invite.viewed    the invite link was opened (OperatorInviteController).
 *                    Stronger, though a mail scanner can follow links too.
 *   accepted         certain: an account now manages the shop.
 */
class ShopOutreachController extends Controller
{
    /** Bulk links last longer than a hand-made one: a shop may not open the email for weeks. */
    public const LINK_DAYS = 60;

    /** Listed as operators but not shops to write to. */
    private const NEVER = ['Deco Divers'];

    public const DEFAULT_SUBJECT = 'Your shop\'s page on Divers Hub';

    public const DEFAULT_BODY = <<<'TXT'
Hi,

I'm Zach, one of the two divers who run Divers Hub. It's a free site South Florida divers use to find boats, trips and conditions. {shop} is already listed, with your trips pulled from your own calendar. You can see your page here:

{page}

We just opened it up so you can manage your own page: address and hours, trip prices, fills, boats, and a QR code for your counter or your boat. A diver who scans it and signs up sees your trips first.

Use this link to take over your page. It works once and is good until {expires}:

{link}

It will ask you to sign in or make a free account, then press Accept. If anything on your page is wrong, fix it there or just reply to this email.

Thanks,
Zach Patterson
Divers Hub
TXT;

    public function index(Request $request)
    {
        $draft = $this->draft();
        $shops = $this->shops();

        return view('pages.shop.outreach', [
            'draft' => $draft,
            'shops' => $shops,
            'groups' => $this->groups($shops),
            'tested' => $this->tested($draft),
            'postalAddress' => $this->postalAddress(),
            'preview' => $this->compose($draft, $this->previewShops($shops), fn ($shop) => url('/operator-invite/EXAMPLE-LINK-FOR-' . Str::upper(Str::slug($shop['name'])))),
            'previewHtml' => $draft['style'] === 'branded'
                ? $this->branded($draft, $this->previewShops($shops), fn ($shop) => url('/operator-invite/EXAMPLE-LINK-FOR-' . Str::upper(Str::slug($shop['name']))), [])
                : null,
            'onTestSite' => $this->onTestSite($request),
            'linkDays' => self::LINK_DAYS,
        ]);
    }

    /** Save the wording. Changing it means a new test is needed before a real send. */
    public function saveDraft(Request $request)
    {
        $data = $request->validate([
            'subject' => 'required|string|max:150',
            'body' => 'required|string|max:5000',
            'link_mode' => 'required|in:open,email',
            'track' => 'nullable|boolean',
            'style' => 'required|in:branded,plain',
        ]);
        // Counting opens needs an image, and only the branded email has one.
        $data['track'] = $request->boolean('track') && $data['style'] === 'branded';
        if (!str_contains($data['body'], '{link}')) {
            return back()->withInput()->withErrors(['body' => 'The message needs {link} in it, or nobody gets their link.']);
        }
        session(['shop_outreach.draft' => $data]);

        return redirect()->route('shop-outreach')->with('shop_status', 'Wording saved. Send yourself a test before sending it to shops.');
    }

    /** The exact wording, to the admin's own address, with a link that goes nowhere. */
    public function sendTest(Request $request)
    {
        $draft = $this->draft();
        $shops = $this->shops();
        [$subject, $body] = $this->compose($draft, $this->previewShops($shops), fn ($shop) => url('/operator-invite/TEST-ONLY-NOT-A-REAL-LINK'));

        $to = (string) $request->user()->email;
        // Same format the shops will get, without the counting image.
        $html = $draft['style'] === 'branded'
            ? $this->branded($draft, $this->previewShops($shops), fn ($shop) => url('/operator-invite/TEST-ONLY-NOT-A-REAL-LINK'), [])
            : null;
        if (!GraphMailService::send($to, '[TEST] ' . $subject, $body, $html)) {
            return back()->withErrors(['send' => 'The test did not send. The support mailbox may not be connected on this site; check the logs.']);
        }
        session(['shop_outreach.tested' => $this->fingerprint($draft)]);

        return back()->with('shop_status', 'Test sent to ' . $to . '. Read it there. If it is right, you can send to the shops.');
    }

    /** The real send, to the ticked addresses only. */
    public function send(Request $request)
    {
        $draft = $this->draft();
        $data = $request->validate([
            'emails' => 'required|array|min:1',
            'emails.*' => 'string|max:190',
            'again' => 'nullable|boolean',
            'confirm' => 'accepted',
        ]);
        if (!$this->tested($draft)) {
            return back()->withErrors(['send' => 'Send yourself a test of this exact wording first.']);
        }
        if (!$this->postalAddress()) {
            return back()->withErrors(['send' => 'Set a postal address for the footer first (config divehub.postal_address).']);
        }
        if ($this->onTestSite($request)) {
            return back()->withErrors(['send' => 'This is the test site, so every link in these emails would point at its test address. Send from divers-hub.com.']);
        }

        // One send at a time, so a double tap cannot mail anyone twice.
        $lock = Cache::lock('shop-outreach-send', 600);
        if (!$lock->get()) {
            return back()->withErrors(['send' => 'A send is already running. Wait for it to finish.']);
        }

        set_time_limit(600);
        $sent = 0; $skipped = 0; $failed = [];
        try {
            $wanted = array_map('strtolower', $data['emails']);
            foreach ($this->groups($this->shops()) as $email => $group) {
                if (!in_array($email, $wanted, true)) {
                    continue;
                }
                // Fresh check per address: who is still owed an email.
                $shops = array_values(array_filter($group, fn ($shop) => $shop['can_send'] || ($request->boolean('again') && $shop['state'] === 'emailed')));
                if (!$shops) {
                    $skipped++;
                    continue;
                }

                $links = [];
                $invites = [];
                foreach ($shops as $shop) {
                    // One live link per shop: switch off any unused one first.
                    OperatorInvite::where('operator_id', $shop['id'])->whereNull('accepted_at')->whereNull('revoked_at')->update(['revoked_at' => now()]);
                    $token = Str::random(48);
                    $invites[$shop['id']] = OperatorInvite::create([
                        'operator_id' => $shop['id'],
                        'token_hash' => hash('sha256', $token),
                        'email' => $draft['link_mode'] === 'email' ? $email : null,
                        'created_by' => $request->user()->id,
                        'expires_at' => now()->addDays(self::LINK_DAYS),
                    ]);
                    $links[$shop['id']] = route('operator-invite.show', ['token' => $token]);
                }

                [$subject, $body] = $this->compose($draft, $shops, fn ($shop) => $links[$shop['id']]);
                $pixels = $draft['track']
                    ? array_map(fn ($invite) => URL::signedRoute('shop-outreach.opened', ['invite' => $invite->id]), array_values($invites))
                    : [];
                $ok = GraphMailService::send($email, $subject, $body, $draft['style'] === 'branded'
                    ? $this->branded($draft, $shops, fn ($shop) => $links[$shop['id']], $pixels)
                    : null);

                // In Message management too, so a reply has its thread. The
                // links are left out of that copy: they are single use keys.
                ConversationLog::log('email', 'outbound', $email, preg_replace('#https?://\S*/operator-invite/\S+#', '[their invite link]', $body), [
                    'subject' => $subject,
                    'admin_id' => $request->user()->id,
                    'status' => $ok ? 'sent' : 'failed',
                ]);

                foreach ($shops as $shop) {
                    $invite = $invites[$shop['id']];
                    if (!$ok) {
                        $invite->update(['revoked_at' => now()]);
                    }
                    ShopAccess::audit($shop['id'], (int) $request->user()->id, $ok ? 'outreach.sent' : 'outreach.failed', 'invite', (int) $invite->id, ['email' => $email]);
                }
                $ok ? $sent++ : $failed[] = $email;

                // Three failures in a row is a mailbox problem, not an address problem.
                if (count($failed) >= 3 && $sent === 0) {
                    break;
                }
            }
        } finally {
            $lock->release();
        }

        $summary = "Sent to {$sent} " . Str::plural('address', $sent) . '.'
            . ($skipped ? " {$skipped} skipped (already emailed, managed or opted out)." : '')
            . ($failed ? ' Failed: ' . implode(', ', $failed) . '. Their links were switched off; send to them again.' : '');

        return redirect()->route('shop-outreach')->with('shop_status', $summary);
    }

    /**
     * The hidden image in a counted email. Loading it records that the
     * email was (probably) opened, once per link, and answers with a
     * one-pixel transparent GIF whatever happens.
     */
    public function opened(Request $request, int $invite)
    {
        try {
            $row = OperatorInvite::find($invite);
            if ($row && !OperatorAudit::where('action', 'outreach.opened')->where('subject_type', 'invite')->where('subject_id', $row->id)->exists()) {
                ShopAccess::audit((int) $row->operator_id, 0, 'outreach.opened', 'invite', (int) $row->id);
            }
        } catch (\Throwable $e) {
            // Counting is never worth a broken image.
        }

        return response(base64_decode('R0lGODlhAQABAIAAAAAAAP///yH5BAEAAAAALAAAAAABAAEAAAIBRAA7'), 200, [
            'Content-Type' => 'image/gif',
            'Cache-Control' => 'no-store, no-cache, must-revalidate, max-age=0',
        ]);
    }

    /** The link in every email's footer. Signed, so it cannot be guessed for another shop. */
    public function optOut(Request $request, int $operator)
    {
        $op = Operator::find($operator);
        if ($op && !OperatorAudit::where('operator_id', $op->id)->where('action', 'outreach.opt_out')->exists()) {
            ShopAccess::audit((int) $op->id, 0, 'outreach.opt_out', 'operator', (int) $op->id, ['email' => $op->email]);
        }

        return response()->view('pages.shop.outreach-stopped', ['shopName' => $op ? $op->operatorName : 'your shop']);
    }

    // ------------------------------------------------------------------

    /** @return array{subject:string,body:string,link_mode:string} */
    private function draft(): array
    {
        return (array) session('shop_outreach.draft', []) + [
            'subject' => self::DEFAULT_SUBJECT,
            'body' => self::DEFAULT_BODY,
            // "open": whoever has the link can accept it. Most addresses on
            // file are shared inboxes (info@, sales@), and an owner signs up
            // with their own address, so a link tied to the inbox's address
            // would refuse them.
            'link_mode' => 'open',
            // Count opens with a hidden image. Only possible in the branded email.
            'track' => true,
            // "branded": the Divers Hub header, a button and the shop's own
            // page to check, so it reads as coming from a real, known site
            // (Zach, 2026-11-22: bare text with a link is "scammer 101").
            // "plain": text only.
            'style' => 'branded',
        ];
    }

    private function fingerprint(array $draft): string
    {
        return md5($draft['subject'] . '|' . $draft['body'] . '|' . $draft['link_mode'] . '|' . (int) !empty($draft['track']) . '|' . ($draft['style'] ?? 'branded'));
    }

    private function tested(array $draft): bool
    {
        return session('shop_outreach.tested') === $this->fingerprint($draft);
    }

    /**
     * The redesign test slot. An email sent from there would carry links
     * to its azurewebsites.net address, which is exactly what a careful
     * shop owner should refuse to click. Tests are fine; real sends are not.
     */
    private function onTestSite(Request $request): bool
    {
        return str_contains(strtolower($request->getHost()), 'azurewebsites.net');
    }

    private function postalAddress(): ?string
    {
        $address = trim((string) config('divehub.postal_address'));
        return $address !== '' ? $address : null;
    }

    /**
     * Every operator with where it stands: can it be emailed, and if not, why.
     *
     * @return array<int, array{id:int,name:string,city:?string,email:?string,state:string,note:string,can_send:bool}>
     */
    private function shops(): array
    {
        $managed = OperatorAdmin::whereNull('revoked_at')->pluck('operator_id')->flip();
        $optedOut = OperatorAudit::where('action', 'outreach.opt_out')->pluck('operator_id')->flip();
        $lastSent = OperatorAudit::where('action', 'outreach.sent')->orderBy('id')->get(['operator_id', 'subject_id', 'created_at'])->keyBy('operator_id');
        $invites = OperatorInvite::whereIn('id', $lastSent->pluck('subject_id'))->get()->keyBy('id');
        $moved = OperatorAudit::whereIn('action', ['outreach.opened', 'invite.viewed'])->whereIn('subject_id', $lastSent->pluck('subject_id'))
            ->where('subject_type', 'invite')->orderBy('id')->get(['subject_id', 'action', 'created_at'])->groupBy('subject_id');

        $out = [];
        foreach (Operator::orderBy('operatorName')->get(['id', 'operatorName', 'slug', 'cityAddress', 'email']) as $op) {
            $email = strtolower(trim((string) $op->email));
            $valid = $email !== '' && filter_var($email, FILTER_VALIDATE_EMAIL);
            $sent = $lastSent->get($op->id);
            $invite = $sent ? $invites->get($sent->subject_id) : null;

            if (in_array($op->operatorName, self::NEVER, true)) {
                [$state, $note] = ['never', 'Not an active shop'];
            } elseif (!$valid) {
                [$state, $note] = ['no_email', 'No email address on file'];
            } elseif (isset($optedOut[$op->id])) {
                [$state, $note] = ['opted_out', 'Asked not to be emailed'];
            } elseif (isset($managed[$op->id])) {
                [$state, $note] = ['managed', 'Already has an admin'];
            } elseif ($invite && $invite->accepted_at) {
                [$state, $note] = ['managed', 'Accepted ' . $invite->accepted_at->format('M j')];
            } elseif ($invite && !$invite->revoked_at && $invite->expires_at && $invite->expires_at->isFuture()) {
                $did = $moved->get($invite->id, collect());
                $clicked = $did->firstWhere('action', 'invite.viewed');
                $opened = $did->firstWhere('action', 'outreach.opened');
                [$state, $note] = ['emailed', 'Emailed ' . $sent->created_at->format('M j')
                    . ($clicked ? ', link opened ' . $clicked->created_at->format('M j') . ', not accepted yet'
                        : ($opened ? ', probably opened ' . $opened->created_at->format('M j') . ', link not used yet' : ', link not used yet'))];
            } elseif ($sent) {
                [$state, $note] = ['ready', 'Emailed ' . $sent->created_at->format('M j') . ', link expired or switched off'];
            } else {
                [$state, $note] = ['ready', 'Not emailed yet'];
            }

            $out[] = [
                'id' => (int) $op->id,
                'name' => (string) $op->operatorName,
                'city' => $op->cityAddress,
                'page' => route('OperatorDetails', ['id' => $op->slug ?: $op->id]),
                'email' => $valid ? $email : null,
                'state' => $state,
                'note' => $note,
                'can_send' => $state === 'ready',
            ];
        }
        return $out;
    }

    /** One email per address: two shops that share an inbox get one email with both links. */
    private function groups(array $shops): array
    {
        $groups = [];
        foreach ($shops as $shop) {
            if ($shop['email'] && in_array($shop['state'], ['ready', 'emailed'], true)) {
                $groups[$shop['email']][] = $shop;
            }
        }
        ksort($groups);
        return $groups;
    }

    /** What the preview and the test are written for: the first shop that can be emailed. */
    private function previewShops(array $shops): array
    {
        foreach ($shops as $shop) {
            if ($shop['can_send']) {
                return [$shop];
            }
        }
        return [['id' => 0, 'name' => 'Example Dive Shop', 'city' => null, 'page' => url('/OperatorDetails/example-dive-shop'), 'email' => 'shop@example.com', 'state' => 'ready', 'note' => '', 'can_send' => true]];
    }

    /** "Shop A", or "Shop A and Shop B" for two shops on one address. */
    private function shopText(array $shops): string
    {
        $names = array_column($shops, 'name');
        return count($names) > 1 ? implode(', ', array_slice($names, 0, -1)) . ' and ' . end($names) : $names[0];
    }

    /** The values the wording's placeholders stand for, all as plain text. */
    private function values(array $shops, callable $linkFor): array
    {
        $many = count($shops) > 1;
        return [
            '{shop}' => $this->shopText($shops),
            '{link}' => $many ? implode("\n\n", array_map(fn ($shop) => $shop['name'] . ":\n" . $linkFor($shop), $shops)) : $linkFor($shops[0]),
            '{page}' => $many ? implode("\n", array_map(fn ($shop) => $shop['name'] . ': ' . $shop['page'], $shops)) : $shops[0]['page'],
            '{expires}' => now()->addDays(self::LINK_DAYS)->format('F j'),
        ];
    }

    /** The "no more emails like this" link for one shop. */
    private function stopLink(array $shop): string
    {
        return $shop['id'] ? URL::signedRoute('shop-outreach.stop', ['operator' => $shop['id']]) : url('/shop-email/stop/EXAMPLE');
    }

    /**
     * Subject and plain text body for one address. Used as the email
     * itself in the plain style, and as the copy kept in Message
     * management in both styles.
     *
     * @param callable $linkFor fn (array $shop): string
     * @return array{0:string,1:string}
     */
    private function compose(array $draft, array $shops, callable $linkFor): array
    {
        $values = $this->values($shops, $linkFor);
        $body = strtr($draft['body'], $values);

        // The footer is not editable: who this is from, why they got it, how to stop it.
        $stop = implode("\n", array_map(fn ($shop) => (count($shops) > 1 ? $shop['name'] . ': ' : '') . $this->stopLink($shop), $shops));
        $body .= "\n\n--\n"
            . 'You are getting this because ' . $values['{shop}'] . (count($shops) > 1 ? ' are' : ' is') . " listed on Divers Hub (" . url('/') . ").\n"
            . 'Divers Hub' . ($this->postalAddress() ? ', ' . $this->postalAddress() : '') . "\n"
            . "No more emails like this:\n" . $stop;

        return [strtr($draft['subject'], $values), $body];
    }

    /**
     * The same message as a branded email (Zach, 2026-11-22).
     *
     * What makes it read as real, in order of how much it matters:
     *   - it names the shop and links to the shop's own page on
     *     divers-hub.com, which the owner can check before clicking anything
     *   - the button and every link go to the same site the email is from
     *   - a Divers Hub header, a named person signing it, a postal address
     *
     * Built to survive a mail app that blocks images, which many do by
     * default: the header is text beside the logo, the button is a
     * coloured table cell with a real link (not a picture), and the link
     * is printed in full under it. Tables and inline styles only, because
     * that is what mail apps reliably draw.
     *
     * The words are the saved wording. A paragraph that is just {link}
     * becomes the button; {page} becomes a small "your page today" box.
     *
     * @param string[] $pixels signed image addresses for counting opens
     */
    private function branded(array $draft, array $shops, callable $linkFor, array $pixels): string
    {
        $ink = '#0b2a3a'; $sea = '#0e7c9e'; $muted = '#5d6b73';
        $font = "font-family:Arial,Helvetica,sans-serif;";
        $values = $this->values($shops, $linkFor);
        $many = count($shops) > 1;

        $button = implode('', array_map(function ($shop) use ($linkFor, $many, $ink, $muted, $font) {
            $link = e($linkFor($shop));
            $label = e($many ? 'Take over ' . $shop['name'] : 'Take over your page');
            return '<table role="presentation" cellpadding="0" cellspacing="0" border="0" style="margin:0 0 8px;"><tr>'
                . '<td bgcolor="' . $ink . '" style="border-radius:8px;"><a href="' . $link . '" style="display:inline-block;padding:13px 24px;' . $font . 'font-size:16px;font-weight:bold;color:#ffffff;text-decoration:none;">' . $label . '</a></td>'
                . '</tr></table>'
                . '<p style="margin:0 0 18px;' . $font . 'font-size:12px;line-height:1.5;color:' . $muted . ';">Or paste this into your browser:<br><a href="' . $link . '" style="color:' . $muted . ';word-break:break-all;">' . $link . '</a></p>';
        }, $shops));

        $pageBox = '<table role="presentation" cellpadding="0" cellspacing="0" border="0" width="100%" style="margin:0 0 16px;"><tr><td style="padding:12px 14px;background:#eef5f8;border-radius:8px;' . $font . 'font-size:14px;line-height:1.5;color:' . $ink . ';">'
            . implode('<br>', array_map(fn ($shop) => ($many ? '<b>' . e($shop['name']) . ':</b> ' : '') . '<a href="' . e($shop['page']) . '" style="color:' . $sea . ';font-weight:bold;">' . e(preg_replace('#^https?://#', '', $shop['page'])) . '</a>', $shops))
            . '</td></tr></table>';

        $paragraphs = '';
        foreach (preg_split('/\n{2,}/', trim($draft['body'])) as $block) {
            $block = trim($block);
            if ($block === '{link}') {
                $paragraphs .= $button;
            } elseif ($block === '{page}') {
                $paragraphs .= $pageBox;
            } else {
                // Everything else is the admin's own words: escaped, then links made clickable.
                $text = e(strtr($block, $values));
                $text = preg_replace('#(https?://[^\s<]+)#', '<a href="$1" style="color:' . $sea . ';">$1</a>', $text);
                $paragraphs .= '<p style="margin:0 0 16px;' . $font . 'font-size:15px;line-height:1.55;color:#1b1b1b;">' . nl2br($text) . '</p>';
            }
        }

        $stop = implode(' &nbsp; ', array_map(fn ($shop) => '<a href="' . e($this->stopLink($shop)) . '" style="color:' . $muted . ';">' . e($many ? 'Stop emails about ' . $shop['name'] : 'No more emails like this') . '</a>', $shops));
        $footer = 'You are getting this because ' . e($values['{shop}']) . ($many ? ' are' : ' is') . ' listed on <a href="' . e(url('/')) . '" style="color:' . $muted . ';">Divers Hub</a>.<br>'
            . 'Divers Hub' . ($this->postalAddress() ? ', ' . e($this->postalAddress()) : '') . '<br>' . $stop;

        $images = implode('', array_map(fn ($src) => '<img src="' . e($src) . '" width="1" height="1" alt="" style="border:0;width:1px;height:1px;">', $pixels));
        $logo = e(asset('assets/img/logos/logo_circle.png'));

        return '<div style="margin:0;padding:24px 12px;background:#eef3f5;">'
            . '<table role="presentation" cellpadding="0" cellspacing="0" border="0" align="center" width="100%" style="max-width:580px;margin:0 auto;background:#ffffff;border-radius:12px;">'
            . '<tr><td style="padding:18px 26px;border-bottom:1px solid #e3e8eb;">'
            . '<table role="presentation" cellpadding="0" cellspacing="0" border="0"><tr>'
            . '<td style="padding-right:10px;"><img src="' . $logo . '" width="36" height="36" alt="" style="display:block;border:0;border-radius:18px;"></td>'
            . '<td style="' . $font . 'font-size:19px;font-weight:bold;color:' . $ink . ';">Divers Hub</td>'
            . '</tr></table></td></tr>'
            . '<tr><td style="padding:26px 26px 12px;">' . $paragraphs . '</td></tr>'
            . '<tr><td style="padding:16px 26px 20px;background:#f6f8f9;border-radius:0 0 12px 12px;' . $font . 'font-size:12px;line-height:1.6;color:' . $muted . ';">' . $footer . '</td></tr>'
            . '</table>' . $images . '</div>';
    }
}

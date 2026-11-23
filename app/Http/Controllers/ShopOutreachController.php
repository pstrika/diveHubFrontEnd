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

I'm Zach, one of the two divers who run Divers Hub. It's a free site South Florida divers use to find boats, trips and conditions. {shop} is already listed, with your trips pulled from your own calendar.

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
        ]);
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
        if (!GraphMailService::send($to, '[TEST] ' . $subject, $body)) {
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
                $ok = GraphMailService::send($email, $subject, $body);

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
        ];
    }

    private function fingerprint(array $draft): string
    {
        return md5($draft['subject'] . '|' . $draft['body'] . '|' . $draft['link_mode']);
    }

    private function tested(array $draft): bool
    {
        return session('shop_outreach.tested') === $this->fingerprint($draft);
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

        $out = [];
        foreach (Operator::orderBy('operatorName')->get(['id', 'operatorName', 'cityAddress', 'email']) as $op) {
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
                [$state, $note] = ['emailed', 'Emailed ' . $sent->created_at->format('M j') . ', link not used yet'];
            } elseif ($sent) {
                [$state, $note] = ['ready', 'Emailed ' . $sent->created_at->format('M j') . ', link expired or switched off'];
            } else {
                [$state, $note] = ['ready', 'Not emailed yet'];
            }

            $out[] = [
                'id' => (int) $op->id,
                'name' => (string) $op->operatorName,
                'city' => $op->cityAddress,
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
        return [['id' => 0, 'name' => 'Example Dive Shop', 'city' => null, 'email' => 'shop@example.com', 'state' => 'ready', 'note' => '', 'can_send' => true]];
    }

    /**
     * Subject and plain text body for one address.
     *
     * @param callable $linkFor fn (array $shop): string
     * @return array{0:string,1:string}
     */
    private function compose(array $draft, array $shops, callable $linkFor): array
    {
        $names = array_column($shops, 'name');
        $shopText = count($names) > 1
            ? implode(', ', array_slice($names, 0, -1)) . ' and ' . end($names)
            : $names[0];
        $linkText = count($shops) > 1
            ? implode("\n\n", array_map(fn ($shop) => $shop['name'] . ":\n" . $linkFor($shop), $shops))
            : $linkFor($shops[0]);

        $replace = [
            '{shop}' => $shopText,
            '{link}' => $linkText,
            '{expires}' => now()->addDays(self::LINK_DAYS)->format('F j'),
        ];
        $body = strtr($draft['body'], $replace);

        // The footer is not editable: who this is from, why they got it, how to stop it.
        $stop = implode("\n", array_map(
            fn ($shop) => (count($shops) > 1 ? $shop['name'] . ': ' : '') . ($shop['id'] ? URL::signedRoute('shop-outreach.stop', ['operator' => $shop['id']]) : url('/shop-email/stop/EXAMPLE')),
            $shops
        ));
        $body .= "\n\n--\n"
            . 'You are getting this because ' . $shopText . (count($shops) > 1 ? ' are' : ' is') . " listed on Divers Hub (" . url('/') . ").\n"
            . 'Divers Hub' . ($this->postalAddress() ? ', ' . $this->postalAddress() : '') . "\n"
            . "No more emails like this:\n" . $stop;

        return [strtr($draft['subject'], $replace), $body];
    }
}

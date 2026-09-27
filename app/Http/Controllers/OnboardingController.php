<?php

namespace App\Http\Controllers;

use App\Models\Operator;
use App\Models\User;
use App\Models\WeatherLocation;
use App\Support\Coast;
use App\Support\DiveLevel;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Storage;

/**
 * Welcome wizard: a short walk through the profile fields the redesign uses to
 * personalise the app (level, where you dive, favourite operators, a photo).
 *
 * Why: most members never filled these in on the old profile page, and the
 * dashboard picks, level cap and favourites calendar all depend on them. New
 * launch, new experience, so the first visit asks instead of pointing at
 * settings (Zach and Pablo, 2026-09-10).
 *
 * Every step is a plain form POST that saves only the fields it carries, then
 * moves on. It writes the same users columns the profile page writes; no new
 * tables or columns. "Skip for now" is a cookie for two weeks, so nobody is
 * nagged, and the wizard stops offering itself once the profile is complete.
 */
class OnboardingController extends Controller
{
    public const SKIP_COOKIE = 'dh_welcome_skip';
    private const SKIP_DAYS = 14;

    /** Steps in order; the view renders one at a time. */
    public const STEPS = ['welcome', 'level', 'places', 'operators', 'phone', 'comms', 'push', 'navbar', 'social', 'done'];

    /**
     * True when this member has never been through the wizard. Used to be a
     * "is certLevel/favLocations/favOperators empty" heuristic, which meant
     * an already-registered member (real data, or the old fake registration
     * defaults) could never be prompted at all - wizard_completed_at is an
     * explicit flag instead, left null for every existing user on purpose
     * (Pablo, 2026-09-16: "the wizard needs to run to all users that never
     * ran before...I want already registered users to run the wizard when
     * the first login into the new version").
     */
    public static function needs(?User $user): bool
    {
        if (!$user || !$user->isNotGuest()) {
            return false;
        }
        return $user->wizard_completed_at === null;
    }

    /** True when the dashboard should send this request to the wizard. */
    public static function shouldPrompt(Request $request, ?User $user): bool
    {
        return self::needs($user) && !$request->cookie(self::SKIP_COOKIE) && !$request->session()->get('welcome_seen');
    }

    public function show(Request $request)
    {
        $user = User::findOrFail(auth()->id());
        $step = $request->query('step', 'welcome');
        if (!in_array($step, self::STEPS, true)) {
            $step = 'welcome';
        }
        $request->session()->put('welcome_seen', true);

        // Reaching "done" is what actually marks the wizard as run - whether
        // by completing every step or by tapping "Skip this" on each one in
        // turn, both land here. Only the separate "Skip for now, ask me in
        // two weeks" button (skip()) leaves this unset, so that one keeps
        // re-prompting on its own schedule instead of marking the wizard
        // permanently done.
        if ($step === 'done' && $user->wizard_completed_at === null) {
            $user->wizard_completed_at = now();
            $user->save();
        }

        // Locations grouped by coast, US only (Argentina has its own pages).
        $locations = WeatherLocation::where('country', 'US')->get()->map(fn ($l) => [
            'id' => $l->id, 'name' => ucwords($l->location), 'coast' => Coast::label(Coast::forCode($l->short)),
        ])->groupBy('coast');
        $operators = Operator::select('id', 'operatorName', 'logoUrl', 'cityAddress', 'location', 'tec')
            ->orderBy('operatorName')->get();

        $favLocations = array_filter(array_map('intval', explode(',', (string) $user->favLocations)));
        $favOperators = array_filter(array_map('intval', explode(',', (string) $user->favOperators)));

        $SEO = ['title' => 'Welcome to Divers Hub', 'robots' => 'noindex, nofollow'];

        $navSlot1 = \App\Support\NavTabs::resolveSlot($user->nav_slot_1, \App\Support\NavTabs::DEFAULT_SLOT_1);
        $navSlot2 = \App\Support\NavTabs::resolveSlot($user->nav_slot_2, \App\Support\NavTabs::DEFAULT_SLOT_2);

        return view('pages.Welcome', [
            'user' => $user, 'step' => $step, 'steps' => self::STEPS,
            'levels' => DiveLevel::all(), 'locations' => $locations, 'operators' => $operators,
            'favLocations' => $favLocations, 'favOperators' => $favOperators, 'SEO' => $SEO,
            'navSlot1' => $navSlot1, 'navSlot2' => $navSlot2,
        ]);
    }

    /** Save one step's fields, then go to the next step. Only what the step sends is touched. */
    public function save(Request $request)
    {
        $user = User::findOrFail(auth()->id());
        $step = $request->input('step');
        if (!in_array($step, self::STEPS, true)) {
            return redirect()->route('welcome');
        }

        switch ($step) {
            case 'welcome':
                // Name isn't asked here - already captured at registration
                // (or pulled from the Google profile on SSO sign-up), per
                // Pablo, 2026-09-16: "Don't ask for name, they've already
                // put this at the registration (or we grabbed from Google
                // profile)".
                $data = $request->validate([
                    'picture' => 'nullable|image|max:8192',
                ]);
                if ($request->hasFile('picture')) {
                    // Same disk and folder the profile page uses, so the header finds it.
                    $file = $request->file('picture');
                    $filename = time() . '_' . preg_replace('/[^A-Za-z0-9._-]/', '_', $file->getClientOriginalName());
                    Storage::disk('siteAssets')->putFileAs('img/users', $file, $filename);
                    $user->picture = $filename;
                } elseif ($request->input('photoSource') === 'google' && $user->google_avatar_url && !$user->picture) {
                    // "Use my Google photo" - fetched and saved into our own
                    // storage rather than just linking Google's URL, since
                    // every other page already assumes users.picture is a
                    // filename under siteAssets/img/users (Pablo, 2026-09-16:
                    // "ask them if they want to use their Google Profile Pic
                    // or upload a new one").
                    try {
                        $response = Http::timeout(10)->get($user->google_avatar_url);
                        if ($response->successful()) {
                            $filename = time() . '_google_' . $user->id . '.jpg';
                            Storage::disk('siteAssets')->put('img/users/' . $filename, $response->body());
                            $user->picture = $filename;
                        }
                    } catch (\Throwable $e) {
                        // No Google photo is no worse than today - just move on.
                    }
                }
                break;

            case 'level':
                $data = $request->validate(['level' => 'required|integer|min:0|max:4']);
                $user->certLevel = (int) $data['level'];
                // Show everything up to and including the diver's level. The
                // profile page keeps a separate range slider for fine tuning.
                $user->showLevel = '0, ' . (int) $data['level'];
                break;

            case 'places':
                $data = $request->validate(['locations' => 'nullable|array', 'locations.*' => 'integer']);
                $ids = array_values(array_unique(array_map('intval', $data['locations'] ?? [])));
                $user->favLocations = $ids ? implode(', ', $ids) : null;
                break;

            case 'operators':
                $data = $request->validate([
                    'operators'   => 'nullable|array', 'operators.*' => 'integer',
                    'recommendBy' => 'nullable|in:locations,operators',
                ]);
                $ids = array_values(array_unique(array_map('intval', $data['operators'] ?? [])));
                $user->favOperators = $ids ? implode(', ', $ids) : null;
                // The dashboard's weekend picks follow either favourite places or favourite boats.
                $user->prefersLocation = ($data['recommendBy'] ?? 'locations') === 'locations';
                break;

            case 'phone':
                // Same rules as the profile page's phone field
                // (UserController::updateProfile): a US number goes through
                // real SMS verification before it's trusted; an
                // international number is saved straight away since there's
                // no SMS channel to verify it on (WhatsApp only, no
                // verification concept). Pablo, 2026-09-16: "ask and verify
                // the phone number...once verified, they can opt in for sms
                // and whatsApp comms" - comms opt-in is a separate step
                // further on, deliberately after this one.
                if ($request->boolean('resetPhone')) {
                    // "Use a different number" - abandon the pending code
                    // and re-show the entry form, rather than advancing.
                    \App\Services\PhoneVerificationService::clearPending($user);
                    $user->save();
                    return redirect()->route('welcome', ['step' => 'phone']);
                }
                $rawPhone = trim((string) $request->input('phone'));
                if ($rawPhone === '') {
                    // Nothing entered - move on, nothing to verify.
                    break;
                }
                $e164 = \App\Support\PhoneNumber::toE164($rawPhone);
                if ($e164 === null) {
                    session()->flash('phoneError', "That doesn't look like a valid phone number.");
                    return redirect()->route('welcome', ['step' => 'phone']);
                }
                if ($e164 === $user->phone && $user->phone_verified_at) {
                    // Genuinely already verified (e.g. came back to this
                    // step without changing anything) - nothing to do, move
                    // on. A number that's merely on file but never verified
                    // (legacy data from the old front end, or an
                    // international save) must fall through to real
                    // verification below even when it's the same digits
                    // (Pablo, 2026-09-17: re-entered his own already-on-file
                    // 954-292-2846 in the wizard and got no code at all -
                    // this exact check was silently treating "same number"
                    // as "already verified" and skipping the send).
                    break;
                }
                if (\App\Support\PhoneNumber::isUs($e164)) {
                    $started = \App\Services\PhoneVerificationService::start($user, $e164);
                    if (!$started) {
                        // start() returning false now covers two different
                        // things: the resend cooldown, or the SMS genuinely
                        // failing to send, in which case it also clears
                        // pending_phone back to null - so the entry form
                        // shows again here rather than a code box for a
                        // code that was never actually sent (Pablo,
                        // 2026-09-16: "it said send OTP, but I didn't
                        // receive anything").
                        session()->flash('phoneError', "We couldn't send a code just now - if you requested one recently, check your messages; otherwise please try again in a moment.");
                        return redirect()->route('welcome', ['step' => 'phone'])->withInput();
                    }
                    // Stay on this step so the code-entry form shows.
                    return redirect()->route('welcome', ['step' => 'phone']);
                }
                // International: save directly, nothing to verify.
                $user->phone = $e164;
                $user->phone_verified_at = null;
                \App\Services\PhoneVerificationService::clearPending($user);
                $user->sms_notifications = false;
                break;

            case 'comms':
                // The consent screen. Absent means unticked, same as the profile page.
                $before = \App\Support\NotificationConsent::state($user);
                foreach (\App\Support\NotificationConsent::switches() as $column) {
                    $user->{$column} = $request->boolean($column) ? 1 : 0;
                }
                // Same rule as the profile page: SMS/WhatsApp need a
                // verified phone, not just one submitted - if the diver
                // skipped verification, they stay off no matter what this
                // form sent (Pablo, 2026-09-17: "If the user skips, no sms
                // or whatsapp notifs").
                \App\Support\NotificationConsent::enforcePhoneVerification($user);
                \App\Support\NotificationConsent::stamp($user, $before);
                break;

            case 'navbar':
                // Same fallback-to-default rule as the profile page's picker
                // (App\Support\NavTabs) - an invalid/blank pick is just left
                // unset rather than blocking the wizard on it.
                $data = $request->validate(['nav_slot_1' => 'nullable|string', 'nav_slot_2' => 'nullable|string']);
                $user->nav_slot_1 = \App\Support\NavTabs::isValid($data['nav_slot_1'] ?? null) ? $data['nav_slot_1'] : null;
                $user->nav_slot_2 = \App\Support\NavTabs::isValid($data['nav_slot_2'] ?? null) ? $data['nav_slot_2'] : null;
                break;
        }
        $user->save();

        $next = self::STEPS[array_search($step, self::STEPS, true) + 1] ?? 'done';
        return redirect()->route('welcome', ['step' => $next]);
    }

    /** "Skip for now": two weeks of quiet, then offer once more if still incomplete. */
    public function skip(Request $request)
    {
        return redirect()->route('MyDashboard')
            ->withCookie(cookie(self::SKIP_COOKIE, '1', self::SKIP_DAYS * 24 * 60));
    }
}

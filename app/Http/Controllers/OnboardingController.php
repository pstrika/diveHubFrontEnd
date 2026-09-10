<?php

namespace App\Http\Controllers;

use App\Models\Operator;
use App\Models\User;
use App\Models\WeatherLocation;
use App\Support\Coast;
use App\Support\DiveLevel;
use Illuminate\Http\Request;
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
    public const STEPS = ['welcome', 'level', 'places', 'operators', 'done'];

    /** True when the member is missing what the personalised features need. */
    public static function needs(?User $user): bool
    {
        if (!$user || !$user->isNotGuest()) {
            return false;
        }
        $noLevel  = $user->certLevel === null || $user->certLevel === '';
        $noPlaces = trim((string) $user->favLocations) === '' && trim((string) $user->favOperators) === '';
        return $noLevel || $noPlaces;
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

        // Locations grouped by coast, US only (Argentina has its own pages).
        $locations = WeatherLocation::where('country', 'US')->get()->map(fn ($l) => [
            'id' => $l->id, 'name' => ucwords($l->location), 'coast' => Coast::label(Coast::forCode($l->short)),
        ])->groupBy('coast');
        $operators = Operator::select('id', 'operatorName', 'logoUrl', 'cityAddress', 'location', 'tec')
            ->orderBy('operatorName')->get();

        $favLocations = array_filter(array_map('intval', explode(',', (string) $user->favLocations)));
        $favOperators = array_filter(array_map('intval', explode(',', (string) $user->favOperators)));

        $SEO = ['title' => 'Welcome to Divers Hub', 'robots' => 'noindex, nofollow'];

        return view('pages.Welcome', [
            'user' => $user, 'step' => $step, 'steps' => self::STEPS,
            'levels' => DiveLevel::all(), 'locations' => $locations, 'operators' => $operators,
            'favLocations' => $favLocations, 'favOperators' => $favOperators, 'SEO' => $SEO,
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
                $data = $request->validate([
                    'name'    => 'nullable|string|max:100',
                    'picture' => 'nullable|image|max:8192',
                ]);
                if (!empty($data['name'])) {
                    $user->name = $data['name'];
                }
                if ($request->hasFile('picture')) {
                    // Same disk and folder the profile page uses, so the header finds it.
                    $file = $request->file('picture');
                    $filename = time() . '_' . preg_replace('/[^A-Za-z0-9._-]/', '_', $file->getClientOriginalName());
                    Storage::disk('siteAssets')->putFileAs('img/users', $file, $filename);
                    $user->picture = $filename;
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

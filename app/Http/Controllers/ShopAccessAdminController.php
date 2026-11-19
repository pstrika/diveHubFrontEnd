<?php

namespace App\Http\Controllers;

use App\Models\Operator;
use App\Models\OperatorAdmin;
use App\Models\OperatorClaim;
use App\Models\OperatorInvite;
use App\Models\User;
use App\Support\ShopAccess;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

/**
 * Platform admin screen for shop access (Zach, 2026-11-13): who manages
 * which operator, the invite links we hand out, and the "this is my shop"
 * requests waiting on us.
 *
 * Every route here sits behind auth + admin (role 1). Shop admins cannot
 * invite their own staff in this version; only we can.
 */
class ShopAccessAdminController extends Controller
{
    public const INVITE_DAYS = 14;

    public function index(Request $request)
    {
        $operators = Operator::orderBy('operatorName')->get(['id', 'operatorName', 'cityAddress']);
        $names = $operators->pluck('operatorName', 'id');

        $admins = OperatorAdmin::whereNull('revoked_at')->orderBy('operator_id')->get();
        $invites = OperatorInvite::whereNull('accepted_at')->whereNull('revoked_at')
            ->where('expires_at', '>', now())->orderByDesc('id')->get();
        $claims = OperatorClaim::where('status', 'pending')->orderBy('id')->get();

        [$adoption, $adoptionTotals] = $this->adoptionRows($operators, $admins, $invites, $claims);

        $userIds = $admins->pluck('user_id')->merge($claims->pluck('user_id'))->unique();
        $users = User::whereIn('id', $userIds)->get(['id', 'name', 'email'])->keyBy('id');

        return view('pages.shop.access-admin', [
            'operators' => $operators,
            'names' => $names,
            'admins' => $admins,
            'invites' => $invites,
            'claims' => $claims,
            'users' => $users,
            // Shown once, straight after the link is made. Never stored.
            'newInviteUrl' => session('new_invite_url'),
            'newInviteFor' => session('new_invite_for'),
            'selected' => (int) $request->query('operator', 0),
            'adoption' => $adoption,
            'adoptionTotals' => $adoptionTotals,
        ]);
    }

    /** @return array{0:\Illuminate\Support\Collection,1:array} rows and totals for the adoption table */
    private function adoptionRows($operators, $admins, $invites, $claims): array
    {
        // Adoption (Zach, 2026-11-19): every shop, whether anyone manages it
        // yet, and how far along its page is, so we can see who to chase.
        $adminCount = $admins->groupBy('operator_id')->map->count();
        $inviteOpen = $invites->groupBy('operator_id')->map->count();
        $claimOpen = $claims->groupBy('operator_id')->map->count();
        $locations = \App\Models\OperatorLocation::where('is_active', true)->get(['id', 'operator_id', 'type']);
        $storefronts = $locations->where('type', 'storefront')->groupBy('operator_id')->map->count();
        $locationOperator = $locations->pluck('operator_id', 'id');
        $offerings = \App\Models\OperatorOffering::where('is_active', true)->get(['operator_location_id'])
            ->groupBy(fn ($o) => $locationOperator[$o->operator_location_id] ?? 0)->map->count();
        $homeDivers = User::whereNotNull('home_operator_location_id')->get(['home_operator_location_id'])
            ->groupBy(fn ($u) => $locationOperator[$u->home_operator_location_id] ?? 0)->map->count();
        $lastEdit = \App\Models\OperatorProfile::get(['operator_id', 'updated_at'])->pluck('updated_at', 'operator_id');

        $adoption = $operators->map(function ($op) use ($adminCount, $inviteOpen, $claimOpen, $storefronts, $offerings, $homeDivers, $lastEdit) {
            $managed = (int) ($adminCount[$op->id] ?? 0);
            return [
                'id' => (int) $op->id,
                'name' => $op->operatorName,
                'city' => $op->cityAddress,
                'admins' => $managed,
                'invited' => (int) ($inviteOpen[$op->id] ?? 0),
                'requested' => (int) ($claimOpen[$op->id] ?? 0),
                'storefront' => ($storefronts[$op->id] ?? 0) > 0,
                'prices' => (int) ($offerings[$op->id] ?? 0),
                'homeDivers' => (int) ($homeDivers[$op->id] ?? 0),
                'lastEdit' => $lastEdit[$op->id] ?? null,
                // managed, then asked for, then invited, then nobody
                'state' => $managed ? 'managed' : (($claimOpen[$op->id] ?? 0) ? 'requested' : (($inviteOpen[$op->id] ?? 0) ? 'invited' : 'open')),
            ];
        });
        $adoptionTotals = [
            'all' => $adoption->count(),
            'managed' => $adoption->where('state', 'managed')->count(),
            'requested' => $adoption->where('state', 'requested')->count(),
            'invited' => $adoption->where('state', 'invited')->count(),
            'open' => $adoption->where('state', 'open')->count(),
            'storefront' => $adoption->where('storefront', true)->count(),
        ];
        // Unowned shops first within each state is the working order: who to chase next.
        $order = ['requested' => 0, 'invited' => 1, 'open' => 2, 'managed' => 3];
        $adoption = $adoption->sortBy(fn ($row) => [$order[$row['state']], $row['name']])->values();

        return [$adoption, $adoptionTotals];
    }

    /** Name or email search for the "make someone a shop admin" box. */
    public function searchUsers(Request $request)
    {
        $q = trim((string) $request->query('q', ''));
        if (mb_strlen($q) < 2) {
            return response()->json([]);
        }
        $like = '%' . str_replace(['%', '_'], ['\%', '\_'], $q) . '%';
        return response()->json(
            User::where('id', '<>', 5)->where('role_id', '<>', 4)
                ->where(fn ($w) => $w->where('name', 'like', $like)->orWhere('email', 'like', $like))
                ->orderBy('name')->limit(10)->get(['id', 'name', 'email'])
        );
    }

    public function grant(Request $request)
    {
        $data = $request->validate([
            'operator_id' => 'required|integer',
            'user_id' => 'required|integer',
        ]);
        $operator = Operator::findOrFail($data['operator_id']);
        $user = User::findOrFail($data['user_id']);
        if (!ShopAccess::isMember($user)) {
            return back()->withErrors(['user_id' => 'The guest account cannot be a shop admin.']);
        }

        ShopAccess::grant((int) $operator->id, (int) $user->id, (int) $request->user()->id, 'promotion');

        // Granted from the user's own edit page: go back there.
        if ($request->boolean('stay')) {
            return back()->with('shop_status', $user->name . ' can now manage ' . $operator->operatorName . '.');
        }

        return redirect()->route('shop-access.index', ['operator' => $operator->id])
            ->with('shop_status', $user->name . ' can now manage ' . $operator->operatorName . '.');
    }

    public function revoke(Request $request, int $admin)
    {
        $row = OperatorAdmin::findOrFail($admin);
        ShopAccess::revoke((int) $row->operator_id, (int) $row->user_id, (int) $request->user()->id);
        return back()->with('shop_status', 'Access removed.');
    }

    public function createInvite(Request $request)
    {
        $data = $request->validate([
            'operator_id' => 'required|integer',
            'email' => 'nullable|email|max:190',
        ]);
        $operator = Operator::findOrFail($data['operator_id']);

        // 48 characters from a CSPRNG, about 285 bits. Only its hash is kept.
        $token = Str::random(48);
        $invite = OperatorInvite::create([
            'operator_id' => $operator->id,
            'token_hash' => hash('sha256', $token),
            'email' => $data['email'] ?? null ?: null,
            'created_by' => $request->user()->id,
            'expires_at' => now()->addDays(self::INVITE_DAYS),
        ]);
        ShopAccess::audit((int) $operator->id, (int) $request->user()->id, 'invite.created', 'invite', (int) $invite->id,
            ['email' => $invite->email]);

        return redirect()->route('shop-access.index', ['operator' => $operator->id])
            ->with('new_invite_url', route('operator-invite.show', ['token' => $token]))
            ->with('new_invite_for', $operator->operatorName);
    }

    public function revokeInvite(Request $request, int $invite)
    {
        $row = OperatorInvite::findOrFail($invite);
        $row->update(['revoked_at' => now()]);
        ShopAccess::audit((int) $row->operator_id, (int) $request->user()->id, 'invite.revoked', 'invite', (int) $row->id);
        return back()->with('shop_status', 'Invite link switched off.');
    }

    public function decideClaim(Request $request, int $claim)
    {
        $data = $request->validate(['decision' => 'required|in:approve,decline']);
        $row = OperatorClaim::where('status', 'pending')->findOrFail($claim);
        $approved = $data['decision'] === 'approve';

        $row->update([
            'status' => $approved ? 'approved' : 'declined',
            'decided_by' => $request->user()->id,
            'decided_at' => now(),
        ]);
        if ($approved) {
            ShopAccess::grant((int) $row->operator_id, (int) $row->user_id, (int) $request->user()->id, 'claim');
        } else {
            ShopAccess::audit((int) $row->operator_id, (int) $request->user()->id, 'claim.declined', 'claim', (int) $row->id);
        }

        return back()->with('shop_status', $approved ? 'Request approved. They can manage the shop now.' : 'Request declined.');
    }
}

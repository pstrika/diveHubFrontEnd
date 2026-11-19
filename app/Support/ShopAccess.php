<?php

namespace App\Support;

use App\Http\Middleware\AuthenticateAsGuest;
use App\Models\Message;
use App\Models\OperatorAdmin;
use App\Models\OperatorAudit;
use App\Models\User;

/**
 * Who may manage which operator (shop portal, Zach, 2026-11-13).
 *
 * The one place the rule lives:
 *   a platform admin (role 1) may manage every operator;
 *   anyone else needs an operator_admins row that is not revoked;
 *   the shared guest account never may, whatever its role says.
 *
 * The guest is checked by id as well as by role. Anonymous visitors are
 * signed in as user 5, so auth()->check() is true for them, and the role
 * on that row is not the same in every environment.
 */
class ShopAccess
{
    /** A real, signed in person: not null and not the shared guest account. */
    public static function isMember(?object $user): bool
    {
        return $user
            && (int) $user->id !== AuthenticateAsGuest::GUEST_USER_ID
            && $user->isNotGuest();
    }

    public static function isPlatformAdmin(?object $user): bool
    {
        return self::isMember($user) && $user->isAdmin();
    }

    public static function canManage(?object $user, int $operatorId): bool
    {
        if (!self::isMember($user)) {
            return false;
        }
        if ($user->isAdmin()) {
            return true;
        }
        return in_array($operatorId, self::operatorIdsFor($user), true);
    }

    /** Operators this person holds a live grant for (not counting platform admin). */
    public static function operatorIdsFor(?object $user): array
    {
        if (!self::isMember($user)) {
            return [];
        }
        // Read fresh every time, on purpose: a grant or a revoke must take
        // effect on the very next check, and this is one indexed lookup.
        return OperatorAdmin::where('user_id', $user->id)->whereNull('revoked_at')
            ->pluck('operator_id')->map(fn ($id) => (int) $id)->all();
    }

    /** Whether the portal should be offered to this person at all (menu link). */
    public static function hasPortal(?object $user): bool
    {
        if (!self::isMember($user)) {
            return false;
        }
        try {
            return $user->isAdmin() || self::operatorIdsFor($user) !== [];
        } catch (\Throwable $e) {
            // The menu is on every page. If the table is not there yet
            // (code deployed before its migration), the link stays hidden
            // and the page still renders.
            return false;
        }
    }

    /**
     * Gives (or gives back) a grant. Safe to call twice: an existing row is
     * reused, and a revoked one is switched back on.
     */
    public static function grant(int $operatorId, int $userId, ?int $grantedBy, string $via, ?int $inviteId = null): OperatorAdmin
    {
        $row = OperatorAdmin::firstOrNew(['operator_id' => $operatorId, 'user_id' => $userId]);
        $row->fill([
            'granted_by' => $grantedBy,
            'granted_via' => $via,
            'invite_id' => $inviteId,
            'revoked_at' => null,
        ])->save();

        self::audit($operatorId, $grantedBy ?? $userId, 'admin.granted', 'user', $userId, ['via' => $via]);
        return $row;
    }

    public static function revoke(int $operatorId, int $userId, int $revokedBy): void
    {
        OperatorAdmin::where('operator_id', $operatorId)->where('user_id', $userId)
            ->whereNull('revoked_at')->update(['revoked_at' => now()]);
        self::audit($operatorId, $revokedBy, 'admin.revoked', 'user', $userId);
    }

    public static function audit(int $operatorId, int $userId, string $action, ?string $subjectType = null, ?int $subjectId = null, ?array $changes = null): void
    {
        OperatorAudit::create([
            'operator_id' => $operatorId,
            'user_id' => $userId,
            'action' => $action,
            'subject_type' => $subjectType,
            'subject_id' => $subjectId,
            'changes' => $changes,
            'created_at' => now(),
        ]);
    }

    /** A notice in every platform admin's Admin folder. */
    public static function notifyPlatformAdmins(string $subject, string $body, ?string $url = null): void
    {
        foreach (User::where('role_id', 1)->pluck('id') as $adminId) {
            Message::create([
                'userId' => $adminId,
                'kind' => Message::KIND_ADMIN,
                'subject' => $subject,
                'body' => $body,
                'url' => $url,
                'read' => 0,
                'deleted' => 0,
            ]);
        }
    }
}

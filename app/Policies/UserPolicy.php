<?php

namespace App\Policies;

use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;

class UserPolicy
{
    use HandlesAuthorization;

    /**
     * Create a new policy instance.
     *
     * @return void
     */
    public function __construct()
    {
        //
    }

      /**
     * Determine whether the user can see the users.
     */
    public function viewAny(User $user)
    {
        return $user->isAdmin();
    }

    /**
     * Determine whether the authenticate user can create users.
     */
    public function create(User $user)
    {
        return $user->isAdmin();
    }

    /**
     * Determine whether the authenticate user can update the user.
     */
    public function update(User $user, User $model)
    {
        
        return $user->isAdmin();
    }

    /**
     * Determine whether the authenticate user can delete the user.
     */
    public function delete(User $user, User $model)
    {
        
        return $user->isAdmin() && $user->id != $model->id;
    }

    /**
     * Determine whether the authenticate user can manage other users.
     */
    public function manageUsers(User $user)
    {
        return $user->isAdmin();
    }

    /**
     * Determine whether the authenticate user can manage items and other related entities(tags, categories).
     *
     * Admin-only (Pablo, 2026-09-18: Creators should have no access outside
     * Article management) - this used to also admit Creators, which is what
     * let them reach Dive Sites Admin, tags and categories.
     */
    public function manageItems(User $user)
    {
        return $user->isAdmin();
    }

    /**
     * rEvo CCR Build-up checklist (Pablo, 2026-10-17: "make ONLY available
     * to my user pstrika@gmail.com (nobody else - not even platform
     * admins)") - deliberately NOT isAdmin()-gated like every other
     * CcrChecklistController precedent in this app (Multi Dive Planner,
     * etc). This is Pablo's own personal equipment log, not an admin tool,
     * so admin status is irrelevant here on purpose.
     *
     * 2026-11-10 (Zach): the checklist is growing from Pablo's own build
     * sheet into a tool for other rebreather divers (docs/ccr-checklists-scope.md),
     * so the single hardcoded address became the 'ccr_checklists' early
     * access list in config/divehub.php. Same rule as before, admin status
     * still does not matter, only the list does. Opening it up later is a
     * config edit, not a code change.
     */
    public function ccrChecklistAccess(User $user)
    {
        // Registered members only (Zach, 2026-11-11, when the list became
        // '*'). Signed-out visitors are logged in as the shared guest
        // account, and a checklist saved there would be every visitor's.
        if (!$user->isNotGuest()) {
            return false;
        }
        return \App\Support\EarlyAccess::allows($user, 'ccr_checklists');
    }
}

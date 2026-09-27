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
}

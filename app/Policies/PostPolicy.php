<?php

namespace App\Policies;

use App\Models\Post;
use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;

/**
 * Creators are trusted to publish directly - no pre-approval queue (Pablo,
 * 2026-09-17: "I'm expecting creators to create reasonable content"). What
 * Admins get on top is removal power over EVERYONE's posts, not just their
 * own ("as Admins, we need to be able to remove articles") - the same
 * "creator owns theirs, admin owns everything" split Groups already uses
 * for the public-group-per-admin exemption.
 */
class PostPolicy
{
    use HandlesAuthorization;

    public function viewAny(User $user): bool
    {
        return $user->isCreator() || $user->isAdmin();
    }

    public function create(User $user): bool
    {
        return $user->isCreator() || $user->isAdmin();
    }

    public function update(User $user, Post $post): bool
    {
        return $user->isAdmin() || ($user->isCreator() && $post->author_id === $user->id);
    }

    public function delete(User $user, Post $post): bool
    {
        return $user->isAdmin() || ($user->isCreator() && $post->author_id === $user->id);
    }
}

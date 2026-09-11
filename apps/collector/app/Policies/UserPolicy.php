<?php

namespace App\Policies;

use App\Models\User;

class UserPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->isStaff();
    }

    public function view(User $user, User $target): bool
    {
        return $user->isStaff();
    }

    public function create(User $user): bool
    {
        return $user->can('manage moderators');
    }

    public function update(User $user, User $target): bool
    {
        return $user->can('manage moderators') && $target->isModerator();
    }

    public function delete(User $user, User $target): bool
    {
        return $user->can('manage moderators') && $target->isModerator() && $user->id !== $target->id;
    }
}

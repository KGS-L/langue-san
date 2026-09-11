<?php

namespace App\Policies;

use App\Models\User;
use App\Models\Validation;

class ValidationPolicy
{
    public function viewAny(User $user): bool { return $user->can('validate contributions'); }
    public function create(User $user): bool { return $user->can('validate contributions'); }
    public function update(User $user, Validation $validation): bool
    {
        return $user->can('validate contributions') && ($user->isAdmin() || $validation->validator_id === $user->id);
    }
}

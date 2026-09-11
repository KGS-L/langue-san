<?php

namespace App\Policies;

use App\Models\User;
use App\Models\Variety;

class VarietyPolicy
{
    public function viewAny(User $user): bool { return $user->can('manage reference data'); }
    public function create(User $user): bool { return $user->can('manage reference data'); }
    public function update(User $user, Variety $variety): bool { return $user->can('manage reference data'); }
    public function delete(User $user, Variety $variety): bool { return $user->can('manage reference data'); }
}

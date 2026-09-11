<?php

namespace App\Policies;

use App\Models\Locality;
use App\Models\User;

class LocalityPolicy
{
    public function viewAny(User $user): bool { return $user->can('manage reference data'); }
    public function create(User $user): bool { return $user->can('manage reference data'); }
    public function update(User $user, Locality $locality): bool { return $user->can('manage reference data'); }
    public function delete(User $user, Locality $locality): bool { return $user->can('manage reference data'); }
}

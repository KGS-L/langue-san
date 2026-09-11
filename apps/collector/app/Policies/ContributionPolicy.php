<?php

namespace App\Policies;

use App\Models\Contribution;
use App\Models\User;

class ContributionPolicy
{
    public function viewAny(User $user): bool { return $user->can('view contributions'); }
    public function view(User $user, Contribution $contribution): bool { return $user->can('view contributions'); }
    public function update(User $user, Contribution $contribution): bool { return $user->can('transcribe contributions'); }
    public function delete(User $user, Contribution $contribution): bool { return $user->isAdmin(); }
}

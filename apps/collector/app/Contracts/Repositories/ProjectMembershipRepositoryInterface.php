<?php

namespace App\Contracts\Repositories;

use App\Models\ProjectMembership;
use App\Models\User;

interface ProjectMembershipRepositoryInterface
{
    public function updateOrCreateForUser(User $user, array $data): ProjectMembership;
}

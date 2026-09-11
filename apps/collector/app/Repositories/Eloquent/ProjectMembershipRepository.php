<?php

namespace App\Repositories\Eloquent;

use App\Contracts\Repositories\ProjectMembershipRepositoryInterface;
use App\Models\ProjectMembership;
use App\Models\User;

class ProjectMembershipRepository implements ProjectMembershipRepositoryInterface
{
    public function __construct(private readonly ProjectMembership $model) {}

    public function updateOrCreateForUser(User $user, array $data): ProjectMembership
    {
        return $this->model->newQuery()->updateOrCreate(
            ['user_id' => $user->id],
            $data,
        );
    }
}

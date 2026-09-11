<?php

namespace App\Repositories\Eloquent;

use App\Contracts\Repositories\UserProfileRepositoryInterface;
use App\Models\User;
use App\Models\UserProfile;
use Illuminate\Support\Collection;

class UserProfileRepository implements UserProfileRepositoryInterface
{
    public function __construct(private readonly UserProfile $model) {}

    public function firstOrCreateForUser(User $user): UserProfile
    {
        return $this->model->newQuery()->firstOrCreate(['user_id' => $user->id]);
    }

    public function update(UserProfile $profile, array $data): UserProfile
    {
        $profile->update($data);

        return $profile->refresh();
    }

    public function publicCommunity(int $limit = 24): Collection
    {
        return $this->model->newQuery()
            ->with(['user.projectMembership'])
            ->where('public_profile_enabled', true)
            ->whereNotNull('onboarding_completed_at')
            ->latest('updated_at')
            ->limit($limit)
            ->get();
    }
}

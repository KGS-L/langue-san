<?php

namespace App\Contracts\Repositories;

use App\Models\User;
use App\Models\UserProfile;
use Illuminate\Support\Collection;

interface UserProfileRepositoryInterface
{
    public function firstOrCreateForUser(User $user): UserProfile;
    public function update(UserProfile $profile, array $data): UserProfile;
    public function publicCommunity(int $limit = 24): Collection;
}

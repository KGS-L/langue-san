<?php

namespace App\Services;

use App\Contracts\Repositories\UserProfileRepositoryInterface;
use App\Contracts\Repositories\UserRepositoryInterface;
use App\Models\User;
use App\Models\UserProfile;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\DB;

class UserProfileService
{
    public function __construct(
        private readonly UserProfileRepositoryInterface $profiles,
        private readonly UserRepositoryInterface $users,
    ) {}

    public function forUser(User $user): UserProfile
    {
        return $this->profiles->firstOrCreateForUser($user);
    }

    public function completeOnboarding(User $user, array $data): UserProfile
    {
        return DB::transaction(function () use ($user, $data): UserProfile {
            $this->users->update($user, ['name' => $data['name']]);
            $profile = $this->profiles->firstOrCreateForUser($user);

            return $this->profiles->update($profile, [
                ...Arr::only($data, [
                    'country',
                    'age_range',
                    'profession',
                    'profession_other',
                    'organization',
                ]),
                'profession_other' => ($data['profession'] ?? null) === 'other'
                    ? ($data['profession_other'] ?? null)
                    : null,
                'onboarding_completed_at' => now(),
            ]);
        });
    }

    public function updatePublicProfile(User $user, array $data): UserProfile
    {
        $profile = $this->profiles->firstOrCreateForUser($user);

        return $this->profiles->update($profile, Arr::only($data, [
            'public_profile_enabled',
            'public_display_name',
            'public_bio',
            'github_url',
            'linkedin_url',
        ]));
    }

    public function publicCommunity(int $limit = 24)
    {
        return $this->profiles->publicCommunity($limit);
    }
}

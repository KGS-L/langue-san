<?php

namespace App\Services;

use App\Contracts\Repositories\ContributorProfileRepositoryInterface;
use App\Data\ContributorIdentity;
use App\Models\ContributorProfile;
use App\Models\User;
use Illuminate\Support\Str;

class ContributorIdentityService
{
    public const COOKIE_NAME = 'san_contributor_token';

    public function __construct(
        private readonly ContributorProfileRepositoryInterface $profiles,
    ) {}

    public function resolve(?User $user, ?string $guestToken): ContributorIdentity
    {
        if ($user?->isContributor()) {
            $profile = $guestToken
                ? $this->profiles->claimGuestForUser($this->hashToken($guestToken), $user)
                : $this->profiles->findByUserId($user->id);

            if (! $profile) {
                $profile = $this->profiles->create([
                    'public_code' => $this->newPublicCode(),
                    'user_id' => $user->id,
                    'last_seen_at' => now(),
                ]);
            } else {
                $this->profiles->touchLastSeen($profile);
            }

            return new ContributorIdentity($profile);
        }

        if ($guestToken) {
            $profile = $this->profiles->findByGuestTokenHash($this->hashToken($guestToken));

            if ($profile) {
                $this->profiles->touchLastSeen($profile);
                return new ContributorIdentity($profile);
            }
        }

        $plainToken = Str::random(64);
        $profile = $this->profiles->create([
            'public_code' => $this->newPublicCode(),
            'guest_token_hash' => $this->hashToken($plainToken),
            'last_seen_at' => now(),
        ]);

        return new ContributorIdentity($profile, $plainToken, true);
    }

    public function claimGuestProfile(?string $guestToken, User $user): ?ContributorProfile
    {
        if (! $guestToken || ! $user->isContributor()) {
            return $this->profiles->findByUserId($user->id);
        }

        return $this->profiles->claimGuestForUser($this->hashToken($guestToken), $user);
    }

    private function hashToken(string $token): string
    {
        return hash('sha256', $token);
    }

    private function newPublicCode(): string
    {
        return 'SAN-'.strtoupper(substr((string) Str::ulid(), -10));
    }
}

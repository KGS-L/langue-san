<?php

namespace App\Contracts\Repositories;

use App\Models\ContributorProfile;
use App\Models\User;

interface ContributorProfileRepositoryInterface
{
    public function findByUserId(int $userId): ?ContributorProfile;
    public function findByGuestTokenHash(string $tokenHash): ?ContributorProfile;
    public function create(array $data): ContributorProfile;
    public function touchLastSeen(ContributorProfile $profile): void;
    public function claimGuestForUser(string $tokenHash, User $user): ?ContributorProfile;
}

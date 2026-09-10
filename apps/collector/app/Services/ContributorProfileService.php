<?php

namespace App\Services;

use App\Contracts\Repositories\ContributorProfileRepositoryInterface;
use App\Models\ContributorProfile;

class ContributorProfileService
{
    public function __construct(
        private readonly ContributorProfileRepositoryInterface $profiles,
    ) {}

    public function updateContext(ContributorProfile $profile, array $data): ContributorProfile
    {
        if (! empty($data['locality_id'])) {
            $data['locality_other'] = null;
        }

        return $this->profiles->update($profile, [
            'locality_id' => $data['locality_id'] ?? null,
            'locality_other' => $data['locality_other'] ?? null,
            'fluency_level' => $data['fluency_level'] ?? null,
            'can_write_san' => $data['can_write_san'] ?? null,
            'last_seen_at' => now(),
        ]);
    }
}

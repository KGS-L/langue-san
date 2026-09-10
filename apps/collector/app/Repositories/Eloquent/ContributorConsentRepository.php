<?php

namespace App\Repositories\Eloquent;

use App\Contracts\Repositories\ContributorConsentRepositoryInterface;
use App\Models\ConsentVersion;
use App\Models\ContributorConsent;
use App\Models\ContributorProfile;

class ContributorConsentRepository implements ContributorConsentRepositoryInterface
{
    public function activeVersion(): ?ConsentVersion
    {
        return ConsentVersion::query()
            ->where('is_active', true)
            ->latest('published_at')
            ->latest('id')
            ->first();
    }

    public function hasAccepted(ContributorProfile $profile, ConsentVersion $version): bool
    {
        return ContributorConsent::query()
            ->where('contributor_profile_id', $profile->id)
            ->where('consent_version_id', $version->id)
            ->exists();
    }

    public function accept(ContributorProfile $profile, ConsentVersion $version, ?string $ipHash): ContributorConsent
    {
        return ContributorConsent::query()->firstOrCreate(
            [
                'contributor_profile_id' => $profile->id,
                'consent_version_id' => $version->id,
            ],
            [
                'accepted_at' => now(),
                'ip_hash' => $ipHash,
            ],
        );
    }
}

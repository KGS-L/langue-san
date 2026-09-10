<?php

namespace App\Contracts\Repositories;

use App\Models\ConsentVersion;
use App\Models\ContributorConsent;
use App\Models\ContributorProfile;

interface ContributorConsentRepositoryInterface
{
    public function activeVersion(): ?ConsentVersion;
    public function hasAccepted(ContributorProfile $profile, ConsentVersion $version): bool;
    public function accept(ContributorProfile $profile, ConsentVersion $version, ?string $ipHash): ContributorConsent;
}

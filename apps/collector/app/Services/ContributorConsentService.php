<?php

namespace App\Services;

use App\Contracts\Repositories\ContributorConsentRepositoryInterface;
use App\Models\ConsentVersion;
use App\Models\ContributorConsent;
use App\Models\ContributorProfile;
use Illuminate\Validation\ValidationException;

class ContributorConsentService
{
    public function __construct(
        private readonly ContributorConsentRepositoryInterface $consents,
    ) {}

    public function currentVersion(): ?ConsentVersion
    {
        return $this->consents->activeVersion();
    }

    public function hasAcceptedCurrent(ContributorProfile $profile): bool
    {
        $version = $this->currentVersion();

        return $version ? $this->consents->hasAccepted($profile, $version) : false;
    }

    public function acceptCurrent(ContributorProfile $profile, ?string $ip): ContributorConsent
    {
        $version = $this->currentVersion();

        if (! $version) {
            throw ValidationException::withMessages([
                'consent' => 'Aucune version active du consentement n’est configurée. Veuillez réessayer plus tard.',
            ]);
        }

        $ipHash = $ip
            ? hash_hmac('sha256', $ip, (string) config('app.key'))
            : null;

        return $this->consents->accept($profile, $version, $ipHash);
    }
}

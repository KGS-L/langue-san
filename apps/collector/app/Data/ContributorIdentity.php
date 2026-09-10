<?php

namespace App\Data;

use App\Models\ContributorProfile;

final readonly class ContributorIdentity
{
    public function __construct(
        public ContributorProfile $profile,
        public ?string $guestToken = null,
        public bool $shouldSetCookie = false,
    ) {}
}

<?php

namespace App\Contracts\Repositories;

use App\Models\Contribution;
use App\Models\ContributionSegment;
use Illuminate\Support\Collection;

interface ContributionSegmentRepositoryInterface
{
    public function forContribution(Contribution $contribution): Collection;
    public function nextPosition(Contribution $contribution): int;
    public function create(array $data): ContributionSegment;
    public function update(ContributionSegment $segment, array $data): ContributionSegment;
    public function delete(ContributionSegment $segment): bool;
}

<?php

namespace App\Repositories\Eloquent;

use App\Contracts\Repositories\ContributionSegmentRepositoryInterface;
use App\Models\Contribution;
use App\Models\ContributionSegment;
use Illuminate\Support\Collection;

class ContributionSegmentRepository implements ContributionSegmentRepositoryInterface
{
    public function forContribution(Contribution $contribution): Collection
    {
        return ContributionSegment::query()
            ->where('contribution_id', $contribution->id)
            ->with(['variety', 'creator', 'updater'])
            ->orderBy('position')
            ->get();
    }

    public function nextPosition(Contribution $contribution): int
    {
        return ((int) ContributionSegment::query()
            ->where('contribution_id', $contribution->id)
            ->max('position')) + 1;
    }

    public function create(array $data): ContributionSegment
    {
        return ContributionSegment::query()->create($data);
    }

    public function update(ContributionSegment $segment, array $data): ContributionSegment
    {
        $segment->update($data);
        return $segment->refresh();
    }

    public function delete(ContributionSegment $segment): bool
    {
        return (bool) $segment->delete();
    }
}

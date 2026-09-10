<?php

namespace App\Repositories\Eloquent;

use App\Contracts\Repositories\ContributionRepositoryInterface;
use App\Models\Contribution;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

class ContributionRepository implements ContributionRepositoryInterface
{
    public function __construct(private readonly Contribution $model) {}

    public function paginate(int $perPage = 20): LengthAwarePaginator
    {
        return $this->model->newQuery()
            ->with(['contributorProfile.user', 'prompt.category', 'locality', 'recording'])
            ->withCount('validations')
            ->latest('submitted_at')
            ->paginate($perPage);
    }

    public function update(Contribution $contribution, array $data): Contribution
    {
        $contribution->update($data);
        return $contribution->refresh();
    }
}

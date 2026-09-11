<?php

namespace App\Repositories\Eloquent;

use App\Contracts\Repositories\DataRequestRepositoryInterface;
use App\Models\ContributorProfile;
use App\Models\DataRequest;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;

class DataRequestRepository implements DataRequestRepositoryInterface
{
    public function __construct(private readonly DataRequest $model) {}

    public function create(array $data): DataRequest
    {
        return $this->model->newQuery()->create($data);
    }

    public function update(DataRequest $request, array $data): DataRequest
    {
        $request->update($data);
        return $request->refresh();
    }

    public function forContributor(ContributorProfile $profile): Collection
    {
        return $this->model->newQuery()
            ->where('contributor_profile_id', $profile->id)
            ->with(['contribution.prompt', 'processor'])
            ->latest()
            ->get();
    }

    public function paginate(array $filters = [], int $perPage = 25): LengthAwarePaginator
    {
        return $this->model->newQuery()
            ->with(['contributorProfile.user', 'contribution.prompt', 'processor'])
            ->when($filters['status'] ?? null, fn (Builder $query, string $status) => $query->where('status', $status))
            ->when($filters['type'] ?? null, fn (Builder $query, string $type) => $query->where('type', $type))
            ->latest()
            ->paginate($perPage)
            ->withQueryString();
    }
}

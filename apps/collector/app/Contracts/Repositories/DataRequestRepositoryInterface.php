<?php

namespace App\Contracts\Repositories;

use App\Models\ContributorProfile;
use App\Models\DataRequest;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;

interface DataRequestRepositoryInterface
{
    public function create(array $data): DataRequest;
    public function update(DataRequest $request, array $data): DataRequest;
    public function forContributor(ContributorProfile $profile): Collection;
    public function paginate(array $filters = [], int $perPage = 25): LengthAwarePaginator;
}

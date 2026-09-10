<?php

namespace App\Contracts\Repositories;

use App\Models\Contribution;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

interface ContributionRepositoryInterface
{
    public function paginate(int $perPage = 20): LengthAwarePaginator;
    public function create(array $data): Contribution;
    public function update(Contribution $contribution, array $data): Contribution;
}

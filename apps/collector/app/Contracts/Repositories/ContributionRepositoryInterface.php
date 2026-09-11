<?php

namespace App\Contracts\Repositories;

use App\Models\Contribution;
use App\Models\User;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

interface ContributionRepositoryInterface
{
    public function paginate(int $perPage = 20): LengthAwarePaginator;
    public function paginateForTranscription(array $filters = [], int $perPage = 20): LengthAwarePaginator;
    public function paginateForValidation(User $validator, array $filters = [], int $perPage = 20): LengthAwarePaginator;
    public function create(array $data): Contribution;
    public function update(Contribution $contribution, array $data): Contribution;
}

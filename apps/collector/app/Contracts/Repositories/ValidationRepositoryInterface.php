<?php

namespace App\Contracts\Repositories;

use App\Models\Contribution;
use App\Models\Validation;
use Illuminate\Support\Collection;

interface ValidationRepositoryInterface
{
    public function create(array $data): Validation;
    public function forContribution(Contribution $contribution): Collection;
}

<?php

namespace App\Repositories\Eloquent;

use App\Contracts\Repositories\ValidationRepositoryInterface;
use App\Models\Contribution;
use App\Models\Validation;
use Illuminate\Support\Collection;

class ValidationRepository implements ValidationRepositoryInterface
{
    public function __construct(private readonly Validation $model) {}
    public function create(array $data): Validation { return $this->model->newQuery()->create($data); }
    public function forContribution(Contribution $contribution): Collection { return $this->model->newQuery()->whereBelongsTo($contribution)->with(['validator', 'variety'])->oldest()->get(); }
}

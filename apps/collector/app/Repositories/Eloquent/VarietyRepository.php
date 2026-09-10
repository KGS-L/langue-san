<?php

namespace App\Repositories\Eloquent;

use App\Contracts\Repositories\VarietyRepositoryInterface;
use App\Models\Variety;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;

class VarietyRepository implements VarietyRepositoryInterface
{
    public function __construct(private readonly Variety $model) {}
    public function paginate(int $perPage = 20): LengthAwarePaginator { return $this->model->newQuery()->withCount(['localities', 'validations'])->orderBy('name')->paginate($perPage); }
    public function active(): Collection { return $this->model->newQuery()->where('is_active', true)->orderBy('name')->get(); }
    public function create(array $data): Variety { return $this->model->newQuery()->create($data); }
    public function update(Variety $variety, array $data): Variety { $variety->update($data); return $variety->refresh(); }
    public function delete(Variety $variety): bool { return (bool) $variety->delete(); }
}

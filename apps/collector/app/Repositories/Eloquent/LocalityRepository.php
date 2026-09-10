<?php

namespace App\Repositories\Eloquent;

use App\Contracts\Repositories\LocalityRepositoryInterface;
use App\Models\Locality;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;

class LocalityRepository implements LocalityRepositoryInterface
{
    public function __construct(private readonly Locality $model) {}
    public function paginate(int $perPage = 20): LengthAwarePaginator { return $this->model->newQuery()->with('suggestedVariety')->orderBy('name')->paginate($perPage); }
    public function active(): Collection { return $this->model->newQuery()->where('is_active', true)->orderBy('name')->get(); }
    public function create(array $data): Locality { return $this->model->newQuery()->create($data); }
    public function update(Locality $locality, array $data): Locality { $locality->update($data); return $locality->refresh(); }
    public function delete(Locality $locality): bool { return (bool) $locality->delete(); }
}

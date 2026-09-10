<?php

namespace App\Repositories\Eloquent;

use App\Contracts\Repositories\CategoryRepositoryInterface;
use App\Models\Category;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;

class CategoryRepository implements CategoryRepositoryInterface
{
    public function __construct(private readonly Category $model) {}
    public function paginate(int $perPage = 20): LengthAwarePaginator { return $this->model->newQuery()->withCount('prompts')->orderBy('display_order')->paginate($perPage); }
    public function active(): Collection { return $this->model->newQuery()->where('is_active', true)->orderBy('display_order')->get(); }
    public function create(array $data): Category { return $this->model->newQuery()->create($data); }
    public function update(Category $category, array $data): Category { $category->update($data); return $category->refresh(); }
    public function delete(Category $category): bool { return (bool) $category->delete(); }
}

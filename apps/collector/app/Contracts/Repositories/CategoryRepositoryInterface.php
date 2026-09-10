<?php

namespace App\Contracts\Repositories;

use App\Models\Category;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;

interface CategoryRepositoryInterface
{
    public function paginate(int $perPage = 20): LengthAwarePaginator;
    public function active(): Collection;
    public function activeWithPromptCount(): Collection;
    public function create(array $data): Category;
    public function update(Category $category, array $data): Category;
    public function delete(Category $category): bool;
}

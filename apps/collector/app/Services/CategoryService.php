<?php

namespace App\Services;

use App\Contracts\Repositories\CategoryRepositoryInterface;
use App\Models\Category;
use Illuminate\Support\Str;

class CategoryService
{
    public function __construct(private readonly CategoryRepositoryInterface $categories) {}
    public function paginate(int $perPage = 20) { return $this->categories->paginate($perPage); }
    public function active() { return $this->categories->active(); }
    public function activeForCollection() { return $this->categories->activeWithPromptCount(); }
    public function create(array $data): Category { $data['slug'] = $data['slug'] ?? Str::slug($data['name']); return $this->categories->create($data); }
    public function update(Category $category, array $data): Category { $data['slug'] = $data['slug'] ?? Str::slug($data['name']); return $this->categories->update($category, $data); }
    public function delete(Category $category): bool { return $this->categories->delete($category); }
}

<?php

namespace App\Policies;

use App\Models\Category;
use App\Models\User;

class CategoryPolicy
{
    public function viewAny(User $user): bool { return $user->can('manage reference data'); }
    public function create(User $user): bool { return $user->can('manage reference data'); }
    public function update(User $user, Category $category): bool { return $user->can('manage reference data'); }
    public function delete(User $user, Category $category): bool { return $user->can('manage reference data'); }
}

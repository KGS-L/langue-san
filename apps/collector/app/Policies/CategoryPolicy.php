<?php
namespace App\Policies;
use App\Models\Category;
use App\Models\User;
class CategoryPolicy { public function viewAny(User $u): bool{return $u->isStaff();} public function create(User $u): bool{return $u->isStaff();} public function update(User $u, Category $m): bool{return $u->isStaff();} public function delete(User $u, Category $m): bool{return $u->isAdmin();} }

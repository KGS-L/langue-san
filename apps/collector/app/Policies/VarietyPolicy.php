<?php
namespace App\Policies;
use App\Models\Variety;
use App\Models\User;
class VarietyPolicy { public function viewAny(User $u): bool{return $u->isStaff();} public function create(User $u): bool{return $u->isStaff();} public function update(User $u, Variety $m): bool{return $u->isStaff();} public function delete(User $u, Variety $m): bool{return $u->isAdmin();} }

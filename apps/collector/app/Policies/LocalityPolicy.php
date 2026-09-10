<?php
namespace App\Policies;
use App\Models\Locality;
use App\Models\User;
class LocalityPolicy { public function viewAny(User $u): bool{return $u->isStaff();} public function create(User $u): bool{return $u->isStaff();} public function update(User $u, Locality $m): bool{return $u->isStaff();} public function delete(User $u, Locality $m): bool{return $u->isAdmin();} }

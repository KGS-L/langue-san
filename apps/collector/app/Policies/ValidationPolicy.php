<?php
namespace App\Policies;
use App\Models\User;
use App\Models\Validation;
class ValidationPolicy { public function viewAny(User $u): bool{return $u->isStaff();} public function create(User $u): bool{return $u->isStaff();} public function update(User $u, Validation $m): bool{return $u->isAdmin() || $m->validator_id===$u->id;} }

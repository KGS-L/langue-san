<?php
namespace App\Policies;
use App\Models\Contribution;
use App\Models\User;
class ContributionPolicy { public function viewAny(User $u): bool{return $u->isStaff();} public function view(User $u, Contribution $m): bool{return $u->isStaff();} public function update(User $u, Contribution $m): bool{return $u->isStaff();} public function delete(User $u, Contribution $m): bool{return $u->isAdmin();} }

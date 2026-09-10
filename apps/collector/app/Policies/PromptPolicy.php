<?php
namespace App\Policies;
use App\Models\Prompt;
use App\Models\User;
class PromptPolicy { public function viewAny(User $u): bool{return $u->isStaff();} public function create(User $u): bool{return $u->isStaff();} public function update(User $u, Prompt $m): bool{return $u->isStaff();} public function delete(User $u, Prompt $m): bool{return $u->isAdmin();} }

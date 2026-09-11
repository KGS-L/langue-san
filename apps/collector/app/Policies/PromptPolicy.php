<?php

namespace App\Policies;

use App\Models\Prompt;
use App\Models\User;

class PromptPolicy
{
    public function viewAny(User $user): bool { return $user->can('manage prompts'); }
    public function create(User $user): bool { return $user->can('manage prompts'); }
    public function update(User $user, Prompt $prompt): bool { return $user->can('manage prompts'); }
    public function delete(User $user, Prompt $prompt): bool { return $user->can('manage prompts'); }
}

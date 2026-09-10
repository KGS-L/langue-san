<?php

namespace App\Services;

use App\Contracts\Repositories\PromptRepositoryInterface;
use App\Models\Prompt;

class PromptService
{
    public function __construct(private readonly PromptRepositoryInterface $prompts) {}
    public function paginate(int $perPage = 20) { return $this->prompts->paginate($perPage); }
    public function create(array $data): Prompt { return $this->prompts->create($data); }
    public function update(Prompt $prompt, array $data): Prompt { return $this->prompts->update($prompt, $data); }
    public function delete(Prompt $prompt): bool { return $this->prompts->delete($prompt); }
}

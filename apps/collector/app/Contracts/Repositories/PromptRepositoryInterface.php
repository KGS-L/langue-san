<?php

namespace App\Contracts\Repositories;

use App\Enums\PromptType;
use App\Models\Prompt;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;

interface PromptRepositoryInterface
{
    public function paginate(int $perPage = 20): LengthAwarePaginator;
    public function create(array $data): Prompt;
    public function update(Prompt $prompt, array $data): Prompt;
    public function delete(Prompt $prompt): bool;
    public function leastCoveredForUserAndCategory(int $userId, int $categoryId, PromptType $type, int $limit): Collection;
}

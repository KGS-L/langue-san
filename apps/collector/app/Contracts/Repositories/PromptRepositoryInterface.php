<?php

namespace App\Contracts\Repositories;

use App\Enums\PromptType;
use App\Models\Prompt;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;

interface PromptRepositoryInterface
{
    public function paginate(array $filters = [], int $perPage = 20): LengthAwarePaginator;
    public function stats(): array;
    public function create(array $data): Prompt;
    public function update(Prompt $prompt, array $data): Prompt;
    public function upsertByCode(array $data): Prompt;
    public function delete(Prompt $prompt): bool;
    public function leastCoveredForContributorAndCategory(
        int $contributorProfileId,
        int $categoryId,
        PromptType $type,
        int $limit,
    ): Collection;
}

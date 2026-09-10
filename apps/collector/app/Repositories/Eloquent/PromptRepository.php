<?php

namespace App\Repositories\Eloquent;

use App\Contracts\Repositories\PromptRepositoryInterface;
use App\Enums\ContributionStatus;
use App\Enums\PromptType;
use App\Models\Prompt;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;

class PromptRepository implements PromptRepositoryInterface
{
    public function __construct(private readonly Prompt $model) {}

    public function paginate(int $perPage = 20): LengthAwarePaginator
    {
        return $this->model->newQuery()->with('category')->withCount('contributions')->latest()->paginate($perPage);
    }

    public function create(array $data): Prompt { return $this->model->newQuery()->create($data); }
    public function update(Prompt $prompt, array $data): Prompt { $prompt->update($data); return $prompt->refresh(); }
    public function delete(Prompt $prompt): bool { return (bool) $prompt->delete(); }

    public function leastCoveredForUserAndCategory(int $userId, int $categoryId, PromptType $type, int $limit): Collection
    {
        return $this->model->newQuery()
            ->where('category_id', $categoryId)
            ->where('type', $type->value)
            ->where('is_active', true)
            ->whereDoesntHave('contributions', fn ($query) => $query->where('user_id', $userId))
            ->withCount(['contributions' => fn ($query) => $query->where('status', '!=', ContributionStatus::REJECTED->value)])
            ->orderBy('contributions_count')
            ->inRandomOrder()
            ->limit($limit)
            ->get();
    }
}

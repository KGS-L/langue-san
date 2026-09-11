<?php

namespace App\Repositories\Eloquent;

use App\Contracts\Repositories\PromptRepositoryInterface;
use App\Enums\ContributionStatus;
use App\Enums\PromptType;
use App\Models\Prompt;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;

class PromptRepository implements PromptRepositoryInterface
{
    public function __construct(private readonly Prompt $model) {}

    public function paginate(array $filters = [], int $perPage = 20): LengthAwarePaginator
    {
        $query = $this->baseListQuery();

        $query
            ->when($filters['q'] ?? null, function (Builder $query, string $search) {
                $query->where(function (Builder $query) use ($search) {
                    $query->where('code', 'like', "%{$search}%")
                        ->orWhere('french_text', 'like', "%{$search}%")
                        ->orWhere('context', 'like', "%{$search}%");
                });
            })
            ->when($filters['category_id'] ?? null, fn (Builder $query, $categoryId) => $query->where('category_id', $categoryId))
            ->when($filters['type'] ?? null, fn (Builder $query, $type) => $query->where('type', $type))
            ->when(($filters['status'] ?? null) === 'active', fn (Builder $query) => $query->where('is_active', true))
            ->when(($filters['status'] ?? null) === 'inactive', fn (Builder $query) => $query->where('is_active', false))
            ->when(filter_var($filters['under_covered'] ?? false, FILTER_VALIDATE_BOOLEAN), fn (Builder $query) => $this->applyUnderCovered($query));

        return $query
            ->orderByDesc('priority')
            ->orderBy('code')
            ->paginate($perPage)
            ->withQueryString();
    }

    public function stats(): array
    {
        return [
            'active' => $this->model->newQuery()->where('is_active', true)->count(),
            'words' => $this->model->newQuery()->where('type', PromptType::WORD->value)->count(),
            'sentences' => $this->model->newQuery()->where('type', PromptType::SENTENCE->value)->count(),
            'narratives' => $this->model->newQuery()->where('type', PromptType::NARRATIVE->value)->count(),
            'under_covered' => $this->applyUnderCovered($this->model->newQuery())->count(),
        ];
    }

    public function create(array $data): Prompt
    {
        return $this->model->newQuery()->create($data);
    }

    public function update(Prompt $prompt, array $data): Prompt
    {
        $prompt->update($data);
        return $prompt->refresh();
    }

    public function upsertByCode(array $data): Prompt
    {
        return $this->model->newQuery()->updateOrCreate(
            ['code' => $data['code']],
            $data,
        );
    }

    public function delete(Prompt $prompt): bool
    {
        if ($prompt->contributions()->exists()) {
            $prompt->update(['is_active' => false]);
            return false;
        }

        return (bool) $prompt->delete();
    }

    public function leastCoveredForContributorAndCategory(
        int $contributorProfileId,
        int $categoryId,
        PromptType $type,
        int $limit,
    ): Collection {
        return $this->model->newQuery()
            ->where('category_id', $categoryId)
            ->where('type', $type->value)
            ->where('is_active', true)
            ->whereDoesntHave('contributions', fn (Builder $query) => $query->where('contributor_profile_id', $contributorProfileId))
            ->withCount([
                'contributions as usable_contributions_count' => fn (Builder $query) => $query->where('status', '!=', ContributionStatus::REJECTED->value),
            ])
            ->orderBy('usable_contributions_count')
            ->orderByDesc('priority')
            ->inRandomOrder()
            ->limit($limit)
            ->get();
    }

    public function naturalSpeechForContributor(int $contributorProfileId, int $limit = 12): Collection
    {
        return $this->model->newQuery()
            ->where('type', PromptType::NARRATIVE->value)
            ->where('is_active', true)
            ->whereDoesntHave('contributions', fn (Builder $query) => $query->where('contributor_profile_id', $contributorProfileId))
            ->with('category')
            ->withCount([
                'contributions as usable_contributions_count' => fn (Builder $query) => $query->where('status', '!=', ContributionStatus::REJECTED->value),
            ])
            ->orderBy('usable_contributions_count')
            ->orderByDesc('priority')
            ->inRandomOrder()
            ->limit($limit)
            ->get();
    }

    public function findActiveNarrative(int $promptId): ?Prompt
    {
        return $this->model->newQuery()
            ->whereKey($promptId)
            ->where('type', PromptType::NARRATIVE->value)
            ->where('is_active', true)
            ->with('category')
            ->first();
    }

    private function baseListQuery(): Builder
    {
        return $this->model->newQuery()
            ->with('category')
            ->withCount([
                'contributions as contributions_count' => fn (Builder $query) => $query->where('status', '!=', ContributionStatus::REJECTED->value),
                'contributions as approved_contributions_count' => fn (Builder $query) => $query->where('status', ContributionStatus::APPROVED->value),
            ]);
    }

    private function applyUnderCovered(Builder $query): Builder
    {
        return $query->whereRaw(
            '(SELECT COUNT(*) FROM contributions c WHERE c.prompt_id = prompts.id AND c.status != ?) < prompts.target_contributions',
            [ContributionStatus::REJECTED->value],
        );
    }
}

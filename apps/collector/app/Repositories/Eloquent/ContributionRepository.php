<?php

namespace App\Repositories\Eloquent;

use App\Contracts\Repositories\ContributionRepositoryInterface;
use App\Enums\ContributionStatus;
use App\Models\Contribution;
use App\Models\User;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Builder;

class ContributionRepository implements ContributionRepositoryInterface
{
    public function __construct(private readonly Contribution $model) {}

    public function paginate(int $perPage = 20): LengthAwarePaginator
    {
        return $this->baseQuery()
            ->latest('submitted_at')
            ->paginate($perPage)
            ->withQueryString();
    }

    public function paginateForTranscription(array $filters = [], int $perPage = 20): LengthAwarePaginator
    {
        return $this->applyFilters(
            $this->baseQuery()->where('status', ContributionStatus::PENDING->value),
            $filters,
        )
            ->oldest('submitted_at')
            ->paginate($perPage)
            ->withQueryString();
    }

    public function paginateForValidation(User $validator, array $filters = [], int $perPage = 20): LengthAwarePaginator
    {
        $statuses = [
            ContributionStatus::TRANSCRIBED->value,
            ContributionStatus::VALIDATED_ONCE->value,
            ContributionStatus::VALIDATED_TWICE->value,
        ];

        return $this->applyFilters(
            $this->baseQuery()
                ->whereIn('status', $statuses)
                ->whereDoesntHave('validations', fn (Builder $query) => $query->where('validator_id', $validator->id)),
            $filters,
        )
            ->orderByRaw("CASE status WHEN 'validated_twice' THEN 1 WHEN 'validated_once' THEN 2 ELSE 3 END")
            ->oldest('submitted_at')
            ->paginate($perPage)
            ->withQueryString();
    }

    public function create(array $data): Contribution
    {
        return $this->model->newQuery()->create($data);
    }

    public function update(Contribution $contribution, array $data): Contribution
    {
        $contribution->update($data);

        return $contribution->refresh();
    }

    private function baseQuery(): Builder
    {
        return $this->model->newQuery()
            ->with([
                'contributorProfile.user',
                'prompt.category',
                'locality',
                'recording',
                'validations.variety',
                'validations.validator',
            ])
            ->withCount('validations');
    }

    private function applyFilters(Builder $query, array $filters): Builder
    {
        return $query
            ->when($filters['category_id'] ?? null, fn (Builder $builder, $categoryId) =>
                $builder->whereHas('prompt', fn (Builder $prompt) => $prompt->where('category_id', $categoryId)))
            ->when($filters['locality_id'] ?? null, fn (Builder $builder, $localityId) =>
                $builder->where('locality_id', $localityId))
            ->when(array_key_exists('has_audio', $filters) && $filters['has_audio'] !== null, function (Builder $builder) use ($filters) {
                return $filters['has_audio']
                    ? $builder->whereHas('recording')
                    : $builder->whereDoesntHave('recording');
            })
            ->when($filters['from'] ?? null, fn (Builder $builder, $from) =>
                $builder->whereDate('submitted_at', '>=', $from))
            ->when($filters['to'] ?? null, fn (Builder $builder, $to) =>
                $builder->whereDate('submitted_at', '<=', $to))
            ->when($filters['q'] ?? null, function (Builder $builder, string $term) {
                $term = trim($term);
                if ($term === '') {
                    return $builder;
                }

                return $builder->where(function (Builder $nested) use ($term) {
                    $nested
                        ->where('san_text', 'like', '%'.$term.'%')
                        ->orWhereHas('prompt', fn (Builder $prompt) => $prompt->where('french_text', 'like', '%'.$term.'%'))
                        ->orWhereHas('contributorProfile.user', fn (Builder $user) => $user->where('name', 'like', '%'.$term.'%'));
                });
            });
    }
}

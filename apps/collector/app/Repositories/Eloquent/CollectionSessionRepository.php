<?php

namespace App\Repositories\Eloquent;

use App\Contracts\Repositories\CollectionSessionRepositoryInterface;
use App\Enums\CollectionSessionStatus;
use App\Enums\SessionPromptStatus;
use App\Models\CollectionSession;
use App\Models\SessionPrompt;
use Illuminate\Support\Collection;

class CollectionSessionRepository implements CollectionSessionRepositoryInterface
{
    public function __construct(private readonly CollectionSession $model) {}

    public function create(array $data): CollectionSession
    {
        return $this->model->newQuery()->create($data);
    }

    public function addPrompts(CollectionSession $session, Collection $prompts): void
    {
        $rows = $prompts->values()->map(fn ($prompt, $index) => [
            'prompt_id' => $prompt->id,
            'position' => $index + 1,
            'status' => SessionPromptStatus::PENDING->value,
        ])->all();

        $session->sessionPrompts()->createMany($rows);
    }

    public function abandonOpenForContributor(int $contributorProfileId): void
    {
        $this->model->newQuery()
            ->where('contributor_profile_id', $contributorProfileId)
            ->where('status', CollectionSessionStatus::STARTED->value)
            ->update([
                'status' => CollectionSessionStatus::ABANDONED->value,
                'updated_at' => now(),
            ]);
    }

    public function findOpenForContributor(int $contributorProfileId): ?CollectionSession
    {
        return $this->model->newQuery()
            ->where('contributor_profile_id', $contributorProfileId)
            ->where('status', CollectionSessionStatus::STARTED->value)
            ->with('category')
            ->latest('started_at')
            ->latest('id')
            ->first();
    }

    public function findForContributor(int $sessionId, int $contributorProfileId): ?CollectionSession
    {
        return $this->model->newQuery()
            ->whereKey($sessionId)
            ->where('contributor_profile_id', $contributorProfileId)
            ->with('category')
            ->first();
    }

    public function findSessionPrompt(int $sessionPromptId, int $sessionId): ?SessionPrompt
    {
        return SessionPrompt::query()
            ->whereKey($sessionPromptId)
            ->where('collection_session_id', $sessionId)
            ->with('prompt.category')
            ->first();
    }

    public function nextPending(CollectionSession $session): ?SessionPrompt
    {
        return $session->sessionPrompts()
            ->where('status', SessionPromptStatus::PENDING->value)
            ->with('prompt.category')
            ->orderBy('position')
            ->first();
    }

    public function progress(CollectionSession $session): array
    {
        $query = $session->sessionPrompts();
        $total = (clone $query)->count();
        $answered = (clone $query)->where('status', SessionPromptStatus::ANSWERED->value)->count();
        $skipped = (clone $query)->where('status', SessionPromptStatus::SKIPPED->value)->count();
        $completed = $answered + $skipped;

        return [
            'total' => $total,
            'answered' => $answered,
            'skipped' => $skipped,
            'completed' => $completed,
            'percent' => $total > 0 ? (int) round(($completed / $total) * 100) : 0,
        ];
    }

    public function markPrompt(SessionPrompt $sessionPrompt, SessionPromptStatus $status): SessionPrompt
    {
        $sessionPrompt->update(['status' => $status->value]);
        return $sessionPrompt->refresh();
    }

    public function complete(CollectionSession $session): CollectionSession
    {
        $session->update([
            'status' => CollectionSessionStatus::COMPLETED->value,
            'completed_at' => now(),
        ]);

        return $session->refresh();
    }
}

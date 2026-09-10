<?php

namespace App\Contracts\Repositories;

use App\Enums\SessionPromptStatus;
use App\Models\CollectionSession;
use App\Models\SessionPrompt;
use Illuminate\Support\Collection;

interface CollectionSessionRepositoryInterface
{
    public function create(array $data): CollectionSession;
    public function addPrompts(CollectionSession $session, Collection $prompts): void;
    public function abandonOpenForContributor(int $contributorProfileId): void;
    public function findForContributor(int $sessionId, int $contributorProfileId): ?CollectionSession;
    public function findSessionPrompt(int $sessionPromptId, int $sessionId): ?SessionPrompt;
    public function nextPending(CollectionSession $session): ?SessionPrompt;
    public function progress(CollectionSession $session): array;
    public function markPrompt(SessionPrompt $sessionPrompt, SessionPromptStatus $status): SessionPrompt;
    public function complete(CollectionSession $session): CollectionSession;
}

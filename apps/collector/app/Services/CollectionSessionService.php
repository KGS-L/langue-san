<?php

namespace App\Services;

use App\Contracts\Repositories\CollectionSessionRepositoryInterface;
use App\Enums\CollectionSessionStatus;
use App\Enums\SessionPromptStatus;
use App\Models\CollectionSession;
use App\Models\ContributorProfile;
use App\Models\SessionPrompt;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class CollectionSessionService
{
    public function __construct(
        private readonly CollectionSessionRepositoryInterface $sessions,
        private readonly PromptSelectionService $promptSelection,
    ) {}

    public function start(ContributorProfile $profile, int $categoryId): CollectionSession
    {
        $prompts = $this->promptSelection->forSession($profile->id, $categoryId);

        if ($prompts->isEmpty()) {
            throw ValidationException::withMessages([
                'category_id' => 'Aucune nouvelle question n’est disponible pour ce thème. Choisissez un autre thème.',
            ]);
        }

        return DB::transaction(function () use ($profile, $categoryId, $prompts) {
            $this->sessions->abandonOpenForContributor($profile->id);

            $session = $this->sessions->create([
                'contributor_profile_id' => $profile->id,
                'category_id' => $categoryId,
                'status' => CollectionSessionStatus::STARTED->value,
                'started_at' => now(),
            ]);

            $this->sessions->addPrompts($session, $prompts);

            return $session->load('category');
        });
    }

    public function activeForContributor(ContributorProfile $profile): ?CollectionSession
    {
        $session = $this->sessions->findOpenForContributor($profile->id);

        if (! $session) {
            return null;
        }

        if ($this->nextPrompt($session) === null) {
            $this->completeIfFinished($session);
            return null;
        }

        return $session;
    }

    public function forContributorOrFail(int $sessionId, ContributorProfile $profile): CollectionSession
    {
        $session = $this->sessions->findForContributor($sessionId, $profile->id);

        if (! $session) {
            throw (new ModelNotFoundException())->setModel(CollectionSession::class, [$sessionId]);
        }

        return $session;
    }

    public function promptOrFail(CollectionSession $session, int $sessionPromptId): SessionPrompt
    {
        $sessionPrompt = $this->sessions->findSessionPrompt($sessionPromptId, $session->id);

        if (! $sessionPrompt) {
            throw (new ModelNotFoundException())->setModel(SessionPrompt::class, [$sessionPromptId]);
        }

        return $sessionPrompt;
    }

    public function nextPrompt(CollectionSession $session): ?SessionPrompt
    {
        return $this->sessions->nextPending($session);
    }

    public function progress(CollectionSession $session): array
    {
        return $this->sessions->progress($session);
    }

    public function markAnswered(SessionPrompt $sessionPrompt): SessionPrompt
    {
        return $this->sessions->markPrompt($sessionPrompt, SessionPromptStatus::ANSWERED);
    }

    public function skip(SessionPrompt $sessionPrompt): SessionPrompt
    {
        if ($sessionPrompt->status !== SessionPromptStatus::PENDING) {
            throw ValidationException::withMessages([
                'prompt' => 'Cette question a déjà été traitée.',
            ]);
        }

        return $this->sessions->markPrompt($sessionPrompt, SessionPromptStatus::SKIPPED);
    }

    public function completeIfFinished(CollectionSession $session): CollectionSession
    {
        if ($this->nextPrompt($session) === null && $session->status === CollectionSessionStatus::STARTED) {
            return $this->sessions->complete($session);
        }

        return $session;
    }
}

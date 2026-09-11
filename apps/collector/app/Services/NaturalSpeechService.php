<?php

namespace App\Services;

use App\Contracts\Repositories\PromptRepositoryInterface;
use App\Models\CollectionSession;
use App\Models\ContributorProfile;
use Illuminate\Support\Collection;
use Illuminate\Validation\ValidationException;

class NaturalSpeechService
{
    public function __construct(
        private readonly PromptRepositoryInterface $prompts,
        private readonly CollectionSessionService $sessions,
    ) {}

    public function availableFor(ContributorProfile $profile, int $limit = 12): Collection
    {
        return $this->prompts->naturalSpeechForContributor($profile->id, $limit);
    }

    public function start(ContributorProfile $profile, int $promptId): CollectionSession
    {
        $prompt = $this->prompts->findActiveNarrative($promptId);

        if (! $prompt) {
            throw ValidationException::withMessages([
                'prompt_id' => 'Ce sujet de parole naturelle n’est plus disponible.',
            ]);
        }

        if ($prompt->contributions()->where('contributor_profile_id', $profile->id)->exists()) {
            throw ValidationException::withMessages([
                'prompt_id' => 'Vous avez déjà répondu à ce sujet. Choisissez-en un autre.',
            ]);
        }

        return $this->sessions->startWithPrompt($profile, $prompt);
    }
}

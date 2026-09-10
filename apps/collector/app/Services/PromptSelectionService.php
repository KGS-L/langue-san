<?php

namespace App\Services;

use App\Contracts\Repositories\PromptRepositoryInterface;
use App\Enums\PromptType;
use Illuminate\Support\Collection;

class PromptSelectionService
{
    public function __construct(private readonly PromptRepositoryInterface $prompts) {}

    public function forSession(
        int $contributorProfileId,
        int $categoryId,
        int $wordCount = 7,
        int $sentenceCount = 3,
    ): Collection {
        return $this->prompts
            ->leastCoveredForContributorAndCategory($contributorProfileId, $categoryId, PromptType::WORD, $wordCount)
            ->concat(
                $this->prompts->leastCoveredForContributorAndCategory(
                    $contributorProfileId,
                    $categoryId,
                    PromptType::SENTENCE,
                    $sentenceCount,
                ),
            )
            ->shuffle()
            ->values();
    }
}

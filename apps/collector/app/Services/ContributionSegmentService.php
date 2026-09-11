<?php

namespace App\Services;

use App\Contracts\Repositories\ContributionSegmentRepositoryInterface;
use App\Enums\ContributionStatus;
use App\Enums\PromptType;
use App\Models\Contribution;
use App\Models\ContributionSegment;
use App\Models\User;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class ContributionSegmentService
{
    public function __construct(private readonly ContributionSegmentRepositoryInterface $segments) {}

    public function forContribution(Contribution $contribution): Collection
    {
        return $this->segments->forContribution($contribution);
    }

    public function create(Contribution $contribution, User $user, array $data): ContributionSegment
    {
        $this->assertEditableNaturalSpeech($contribution);

        return DB::transaction(function () use ($contribution, $user, $data) {
            return $this->segments->create([
                ...$data,
                'contribution_id' => $contribution->id,
                'position' => $this->segments->nextPosition($contribution),
                'created_by' => $user->id,
                'updated_by' => $user->id,
            ]);
        });
    }

    public function update(
        Contribution $contribution,
        ContributionSegment $segment,
        User $user,
        array $data,
    ): ContributionSegment {
        $this->assertBelongsTo($contribution, $segment);
        $this->assertEditableNaturalSpeech($contribution);

        return $this->segments->update($segment, [
            ...$data,
            'updated_by' => $user->id,
        ]);
    }

    public function delete(Contribution $contribution, ContributionSegment $segment): void
    {
        $this->assertBelongsTo($contribution, $segment);
        $this->assertEditableNaturalSpeech($contribution);
        $this->segments->delete($segment);
    }

    private function assertEditableNaturalSpeech(Contribution $contribution): void
    {
        $contribution->loadMissing(['prompt', 'validations']);

        if ($contribution->prompt->type !== PromptType::NARRATIVE) {
            throw ValidationException::withMessages([
                'segment' => 'La segmentation est réservée aux contributions de parole naturelle.',
            ]);
        }

        if ($contribution->status !== ContributionStatus::TRANSCRIBED) {
            throw ValidationException::withMessages([
                'segment' => 'Transcrivez d’abord l’audio complet. Les segments sont verrouillés dès que la validation commence.',
            ]);
        }

        if ($contribution->validations->isNotEmpty()) {
            throw ValidationException::withMessages([
                'segment' => 'Les segments ne peuvent plus être modifiés après le début de la validation.',
            ]);
        }
    }

    private function assertBelongsTo(Contribution $contribution, ContributionSegment $segment): void
    {
        if ($segment->contribution_id !== $contribution->id) {
            abort(404);
        }
    }
}

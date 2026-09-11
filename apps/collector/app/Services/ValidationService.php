<?php

namespace App\Services;

use App\Contracts\Repositories\ContributionRepositoryInterface;
use App\Contracts\Repositories\ValidationRepositoryInterface;
use App\Enums\ContributionStatus;
use App\Enums\PromptType;
use App\Enums\ValidationDecision;
use App\Models\Contribution;
use App\Models\User;
use App\Models\Validation;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class ValidationService
{
    public function __construct(
        private readonly ValidationRepositoryInterface $validations,
        private readonly ContributionRepositoryInterface $contributions,
    ) {}

    public function validate(Contribution $contribution, User $validator, array $data): Validation
    {
        return DB::transaction(function () use ($contribution, $validator, $data) {
            $lockedContribution = Contribution::query()
                ->with(['prompt', 'segments'])
                ->lockForUpdate()
                ->findOrFail($contribution->id);

            if (! $lockedContribution->status->canBeValidated()) {
                throw ValidationException::withMessages([
                    'decision' => 'Cette contribution doit être transcrite avant validation, ou son workflow est déjà terminé.',
                ]);
            }

            if (blank($lockedContribution->san_text)) {
                throw ValidationException::withMessages([
                    'decision' => 'Aucune transcription San n’est disponible. Transcrivez d’abord la contribution.',
                ]);
            }

            if ($lockedContribution->prompt->type === PromptType::NARRATIVE) {
                if ($lockedContribution->segments->isEmpty()) {
                    throw ValidationException::withMessages([
                        'decision' => 'Ce récit naturel doit être segmenté en phrases et traduit en français avant la validation linguistique.',
                    ]);
                }

                $incompleteSegment = $lockedContribution->segments->first(
                    fn ($segment) => blank($segment->san_text) || blank($segment->french_translation),
                );

                if ($incompleteSegment) {
                    throw ValidationException::withMessages([
                        'decision' => 'Tous les segments du récit doivent contenir une transcription San et une traduction française avant validation.',
                    ]);
                }
            }

            if ($lockedContribution->validations()->where('validator_id', $validator->id)->exists()) {
                throw ValidationException::withMessages([
                    'decision' => 'Vous avez déjà validé cette contribution. La prochaine validation doit être faite par une autre personne.',
                ]);
            }

            $validation = $this->validations->create([
                ...$data,
                'contribution_id' => $lockedContribution->id,
                'validator_id' => $validator->id,
            ]);

            $all = $this->validations->forContribution($lockedContribution);
            $status = ContributionStatus::VALIDATED_ONCE;
            $canonicalText = null;

            if ($all->count() >= 2) {
                $latestTwo = $all->take(-2);
                $allRejected = $latestTwo->every(fn ($item) => $item->decision === ValidationDecision::REJECT);
                $allAccepted = $latestTwo->every(fn ($item) => in_array(
                    $item->decision,
                    [ValidationDecision::APPROVE, ValidationDecision::CORRECT],
                    true,
                ));
                $sameVariety = $latestTwo->pluck('variety_id')->filter()->unique()->count() === 1
                    && $latestTwo->every(fn ($item) => filled($item->variety_id));

                $effectiveTexts = $latestTwo->map(function ($item) use ($lockedContribution) {
                    $text = $item->decision === ValidationDecision::CORRECT
                        ? $item->san_text_corrected
                        : $lockedContribution->san_text;

                    return $this->normalizeText((string) $text);
                });

                $sameText = $effectiveTexts->filter()->count() === 2
                    && $effectiveTexts->unique()->count() === 1;

                if ($allRejected) {
                    $status = ContributionStatus::REJECTED;
                } elseif ($allAccepted && $sameVariety && $sameText) {
                    $status = ContributionStatus::APPROVED;
                    $canonicalText = $effectiveTexts->first();
                } else {
                    $status = ContributionStatus::VALIDATED_TWICE;
                }
            }

            $updates = ['status' => $status];
            if ($status === ContributionStatus::APPROVED && filled($canonicalText)) {
                $updates['san_text'] = $canonicalText;
            }

            $this->contributions->update($lockedContribution, $updates);

            return $validation;
        });
    }

    private function normalizeText(string $text): string
    {
        return trim((string) preg_replace('/\s+/u', ' ', $text));
    }
}

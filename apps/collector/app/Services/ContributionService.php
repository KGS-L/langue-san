<?php

namespace App\Services;

use App\Contracts\Repositories\ContributionRepositoryInterface;
use App\Enums\ContributionStatus;
use App\Models\Contribution;
use App\Models\User;
use Illuminate\Validation\ValidationException;

class ContributionService
{
    public function __construct(private readonly ContributionRepositoryInterface $contributions) {}

    public function paginate(int $perPage = 20)
    {
        return $this->contributions->paginate($perPage);
    }

    public function transcriptionQueue(array $filters = [], int $perPage = 20)
    {
        return $this->contributions->paginateForTranscription($filters, $perPage);
    }

    public function validationQueue(User $validator, array $filters = [], int $perPage = 20)
    {
        return $this->contributions->paginateForValidation($validator, $filters, $perPage);
    }

    public function transcribe(Contribution $contribution, string $sanText): Contribution
    {
        if (! $contribution->status->canBeTranscribed()) {
            throw ValidationException::withMessages([
                'san_text' => 'Cette contribution a déjà commencé son cycle de validation et sa transcription ne peut plus être modifiée ici.',
            ]);
        }

        if ($contribution->validations()->exists()) {
            throw ValidationException::withMessages([
                'san_text' => 'Cette contribution possède déjà une validation. La transcription est désormais verrouillée.',
            ]);
        }

        return $this->contributions->update($contribution, [
            'san_text' => trim($sanText),
            'status' => ContributionStatus::TRANSCRIBED,
        ]);
    }
}

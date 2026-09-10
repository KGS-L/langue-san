<?php

namespace App\Services;

use App\Contracts\Repositories\ContributionRepositoryInterface;
use App\Contracts\Repositories\ValidationRepositoryInterface;
use App\Enums\ContributionStatus;
use App\Enums\ValidationDecision;
use App\Models\Contribution;
use App\Models\User;
use App\Models\Validation;
use Illuminate\Support\Facades\DB;

class ValidationService
{
    public function __construct(
        private readonly ValidationRepositoryInterface $validations,
        private readonly ContributionRepositoryInterface $contributions,
    ) {}

    public function validate(Contribution $contribution, User $validator, array $data): Validation
    {
        return DB::transaction(function () use ($contribution, $validator, $data) {
            $validation = $this->validations->create([...$data, 'contribution_id' => $contribution->id, 'validator_id' => $validator->id]);
            $all = $this->validations->forContribution($contribution);

            if ($all->count() === 1) {
                $status = ContributionStatus::VALIDATED_ONCE;
            } else {
                $latestTwo = $all->take(-2);
                $allRejected = $latestTwo->every(fn ($item) => $item->decision === ValidationDecision::REJECT);
                $allAccepted = $latestTwo->every(fn ($item) => in_array($item->decision, [ValidationDecision::APPROVE, ValidationDecision::CORRECT], true));
                $sameVariety = $latestTwo->pluck('variety_id')->filter()->unique()->count() === 1 && $latestTwo->every(fn ($item) => filled($item->variety_id));

                $status = $allRejected
                    ? ContributionStatus::REJECTED
                    : (($allAccepted && $sameVariety) ? ContributionStatus::APPROVED : ContributionStatus::VALIDATED_TWICE);
            }

            $this->contributions->update($contribution, ['status' => $status]);
            return $validation;
        });
    }
}

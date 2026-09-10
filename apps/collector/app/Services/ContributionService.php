<?php

namespace App\Services;

use App\Contracts\Repositories\ContributionRepositoryInterface;
use App\Enums\ContributionStatus;
use App\Models\Contribution;

class ContributionService
{
    public function __construct(private readonly ContributionRepositoryInterface $contributions) {}
    public function paginate(int $perPage = 20) { return $this->contributions->paginate($perPage); }

    public function transcribe(Contribution $contribution, string $sanText): Contribution
    {
        return $this->contributions->update($contribution, ['san_text' => $sanText, 'status' => ContributionStatus::TRANSCRIBED]);
    }
}

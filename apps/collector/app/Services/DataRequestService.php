<?php

namespace App\Services;

use App\Contracts\Repositories\DataRequestRepositoryInterface;
use App\Enums\DataRequestStatus;
use App\Enums\DataRequestType;
use App\Models\Contribution;
use App\Models\ContributorProfile;
use App\Models\DataRequest;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\ValidationException;

class DataRequestService
{
    public function __construct(private readonly DataRequestRepositoryInterface $requests) {}

    public function forContributor(ContributorProfile $profile)
    {
        return $this->requests->forContributor($profile);
    }

    public function paginate(array $filters = [], int $perPage = 25)
    {
        return $this->requests->paginate($filters, $perPage);
    }

    public function submit(ContributorProfile $profile, array $data): DataRequest
    {
        $type = DataRequestType::from($data['type']);
        $contribution = null;

        if ($type->requiresContribution()) {
            $contribution = Contribution::query()
                ->whereKey($data['contribution_id'] ?? null)
                ->where('contributor_profile_id', $profile->id)
                ->with('recording')
                ->first();

            if (! $contribution) {
                throw ValidationException::withMessages([
                    'contribution_id' => 'Cette contribution ne vous appartient pas ou n’existe plus.',
                ]);
            }
        }

        if (in_array($type, [DataRequestType::CORRECTION, DataRequestType::OTHER], true)
            && blank($data['details'] ?? null)) {
            throw ValidationException::withMessages([
                'details' => 'Décrivez votre demande afin que nous puissions la traiter correctement.',
            ]);
        }

        if ($type === DataRequestType::WITHDRAW_CONTRIBUTION) {
            return $this->withdrawContribution($profile, $contribution, $data['details'] ?? null);
        }

        if ($type === DataRequestType::DELETE_AUDIO) {
            return $this->deleteAudio($profile, $contribution, $data['details'] ?? null);
        }

        return $this->requests->create([
            'contributor_profile_id' => $profile->id,
            'contribution_id' => $contribution?->id,
            'type' => $type->value,
            'status' => DataRequestStatus::PENDING->value,
            'details' => $data['details'] ?? null,
        ]);
    }

    public function review(DataRequest $request, User $processor, array $data): DataRequest
    {
        $status = DataRequestStatus::from($data['status']);

        return $this->requests->update($request, [
            'status' => $status->value,
            'resolution_notes' => $data['resolution_notes'] ?? null,
            'processed_by' => $processor->id,
            'processed_at' => in_array($status, [DataRequestStatus::COMPLETED, DataRequestStatus::REJECTED], true)
                ? now()
                : null,
        ]);
    }

    private function withdrawContribution(
        ContributorProfile $profile,
        Contribution $contribution,
        ?string $details,
    ): DataRequest {
        if ($contribution->withdrawn_at) {
            return $this->requests->create([
                'contributor_profile_id' => $profile->id,
                'contribution_id' => $contribution->id,
                'type' => DataRequestType::WITHDRAW_CONTRIBUTION->value,
                'status' => DataRequestStatus::COMPLETED->value,
                'details' => $details,
                'resolution_notes' => 'La contribution avait déjà été retirée.',
                'processed_at' => now(),
            ]);
        }

        return DB::transaction(function () use ($profile, $contribution, $details) {
            $locked = Contribution::query()
                ->with('recording')
                ->lockForUpdate()
                ->findOrFail($contribution->id);

            $this->deleteRecordingFile($locked);
            $locked->recording()->delete();
            $locked->segments()->delete();
            $locked->validations()->delete();
            $locked->update([
                'san_text' => null,
                'submitted_san_text' => null,
                'withdrawn_at' => now(),
            ]);

            return $this->requests->create([
                'contributor_profile_id' => $profile->id,
                'contribution_id' => $locked->id,
                'type' => DataRequestType::WITHDRAW_CONTRIBUTION->value,
                'status' => DataRequestStatus::COMPLETED->value,
                'details' => $details,
                'resolution_notes' => 'Retrait appliqué immédiatement : contenu linguistique et audio supprimés, contribution exclue des exports futurs.',
                'processed_at' => now(),
            ]);
        });
    }

    private function deleteAudio(
        ContributorProfile $profile,
        Contribution $contribution,
        ?string $details,
    ): DataRequest {
        return DB::transaction(function () use ($profile, $contribution, $details) {
            $locked = Contribution::query()
                ->with('recording')
                ->lockForUpdate()
                ->findOrFail($contribution->id);

            $hadRecording = (bool) $locked->recording;
            $this->deleteRecordingFile($locked);
            $locked->recording()->delete();

            return $this->requests->create([
                'contributor_profile_id' => $profile->id,
                'contribution_id' => $locked->id,
                'type' => DataRequestType::DELETE_AUDIO->value,
                'status' => DataRequestStatus::COMPLETED->value,
                'details' => $details,
                'resolution_notes' => $hadRecording
                    ? 'Enregistrement audio supprimé.'
                    : 'Aucun enregistrement audio n’était encore associé à cette contribution.',
                'processed_at' => now(),
            ]);
        });
    }

    private function deleteRecordingFile(Contribution $contribution): void
    {
        $recording = $contribution->recording;

        if ($recording && $recording->path) {
            Storage::disk($recording->disk ?: 'local')->delete($recording->path);
        }
    }
}

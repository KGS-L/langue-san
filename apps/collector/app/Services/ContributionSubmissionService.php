<?php

namespace App\Services;

use App\Contracts\Repositories\ContributionRepositoryInterface;
use App\Contracts\Repositories\RecordingRepositoryInterface;
use App\Enums\ContributionStatus;
use App\Enums\SessionPromptStatus;
use App\Models\CollectionSession;
use App\Models\Contribution;
use App\Models\ContributorProfile;
use App\Models\SessionPrompt;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\ValidationException;
use RuntimeException;
use Throwable;

class ContributionSubmissionService
{
    public function __construct(
        private readonly ContributionRepositoryInterface $contributions,
        private readonly RecordingRepositoryInterface $recordings,
        private readonly CollectionSessionService $sessions,
    ) {}

    public function submit(
        CollectionSession $session,
        SessionPrompt $sessionPrompt,
        ContributorProfile $profile,
        array $data,
        ?UploadedFile $audio,
    ): Contribution {
        if ($session->contributor_profile_id !== $profile->id || $sessionPrompt->collection_session_id !== $session->id) {
            abort(404);
        }

        if ($sessionPrompt->status !== SessionPromptStatus::PENDING) {
            throw ValidationException::withMessages([
                'prompt' => 'Cette question a déjà été traitée.',
            ]);
        }

        $storedPath = null;

        try {
            return DB::transaction(function () use ($session, $sessionPrompt, $profile, $data, $audio, &$storedPath) {
                $text = trim((string) ($data['san_text'] ?? ''));

                $contribution = $this->contributions->create([
                    'collection_session_id' => $session->id,
                    'prompt_id' => $sessionPrompt->prompt_id,
                    'contributor_profile_id' => $profile->id,
                    'locality_id' => $profile->locality_id,
                    'san_text' => $text !== '' ? $text : null,
                    'status' => ContributionStatus::PENDING->value,
                    'submitted_at' => now(),
                ]);

                if ($audio) {
                    $storedPath = $audio->store('recordings/'.$profile->public_code, 'local');

                    if (! $storedPath) {
                        throw new RuntimeException('Impossible de stocker l’enregistrement audio.');
                    }

                    $this->recordings->create([
                        'contribution_id' => $contribution->id,
                        'disk' => 'local',
                        'path' => $storedPath,
                        'mime_type' => $audio->getMimeType(),
                        'size_bytes' => $audio->getSize(),
                        'duration_ms' => null,
                        'quality_status' => 'pending',
                    ]);
                }

                $this->sessions->markAnswered($sessionPrompt);

                return $contribution->refresh();
            });
        } catch (Throwable $exception) {
            if ($storedPath) {
                Storage::disk('local')->delete($storedPath);
            }

            throw $exception;
        }
    }
}

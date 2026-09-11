<?php

namespace App\Services;

use App\Enums\ContributionStatus;
use App\Models\Contribution;
use Illuminate\Support\Facades\Storage;

class DataRetentionService
{
    public function purgeExpiredAudio(): array
    {
        $result = ['rejected' => 0, 'stale_pending' => 0];

        $rejectedBefore = now()->subDays((int) config('data_retention.rejected_audio_days', 90));
        Contribution::query()
            ->where('status', ContributionStatus::REJECTED->value)
            ->where('updated_at', '<=', $rejectedBefore)
            ->whereHas('recording')
            ->with('recording')
            ->chunkById(100, function ($contributions) use (&$result) {
                foreach ($contributions as $contribution) {
                    $this->deleteRecording($contribution);
                    $result['rejected']++;
                }
            });

        $pendingBefore = now()->subDays((int) config('data_retention.stale_pending_audio_days', 365));
        Contribution::query()
            ->where('status', ContributionStatus::PENDING->value)
            ->where('submitted_at', '<=', $pendingBefore)
            ->where('updated_at', '<=', $pendingBefore)
            ->whereHas('recording')
            ->with('recording')
            ->chunkById(100, function ($contributions) use (&$result) {
                foreach ($contributions as $contribution) {
                    $this->deleteRecording($contribution);
                    $result['stale_pending']++;
                }
            });

        return $result;
    }

    private function deleteRecording(Contribution $contribution): void
    {
        $recording = $contribution->recording;

        if (! $recording) {
            return;
        }

        if ($recording->path) {
            Storage::disk($recording->disk ?: 'local')->delete($recording->path);
        }

        $recording->delete();
    }
}

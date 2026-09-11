<?php

namespace App\Services;

use App\Enums\ContributionStatus;
use App\Enums\PromptType;
use App\Enums\ValidationDecision;
use App\Models\Contribution;
use App\Models\ContributionSegment;
use Symfony\Component\HttpFoundation\StreamedResponse;

class DatasetExportService
{
    public function summary(): array
    {
        $ids = $this->translationEligibleQuery()->pluck('id');
        $splits = ['train' => 0, 'validation' => 0, 'test' => 0];

        foreach ($ids as $id) {
            $sourceId = $this->sourceId((int) $id);
            $splits[$this->splitFor($sourceId)]++;
        }

        return [
            'version' => (string) config('dataset.version', '0.1.0'),
            'eligible' => $ids->count(),
            'translationEligible' => $ids->count(),
            'naturalSpeechSegmentsEligible' => $this->naturalSpeechEligibleQuery()->count(),
            'splits' => $splits,
            'sourceSaltConfigured' => config('dataset.source_salt') !== 'langue-san-local-dev-salt',
        ];
    }

    /** Export des paires élicitées Français → San (mots et phrases uniquement). */
    public function csv(): StreamedResponse
    {
        $version = (string) config('dataset.version', '0.1.0');
        $filename = 'langue-san-'.$version.'-fr-san-approved-'.now()->format('Ymd-His').'.csv';

        return response()->streamDownload(function () use ($version) {
            $out = fopen('php://output', 'w');
            fputcsv($out, [
                'dataset_version',
                'source_id',
                'split',
                'direction',
                'prompt_code',
                'variety',
                'variety_iso',
                'french',
                'context',
                'san',
                'type',
                'category',
                'locality',
                'validation_count',
                'submitted_at',
                'approved_at',
            ]);

            $this->translationEligibleQuery()
                ->with([
                    'prompt.category',
                    'locality',
                    'validations.variety',
                    'contributorProfile.consents.consentVersion',
                ])
                ->orderBy('id')
                ->chunkById(500, function ($items) use ($out, $version) {
                    foreach ($items as $item) {
                        $lastValidation = $item->validations->last();
                        $variety = $lastValidation?->variety;

                        if (! $variety) {
                            continue;
                        }

                        $san = $lastValidation?->decision === ValidationDecision::CORRECT
                            ? $lastValidation->san_text_corrected
                            : $item->san_text;

                        $sourceId = $this->sourceId($item->id);

                        fputcsv($out, [
                            $version,
                            $sourceId,
                            $this->splitFor($sourceId),
                            'fr-san',
                            $item->prompt->code,
                            $variety->name,
                            $variety->iso_code,
                            $item->prompt->french_text,
                            $item->prompt->context,
                            $san,
                            $item->prompt->type->value,
                            $item->prompt->category->name,
                            $item->locality?->name,
                            $item->validations->count(),
                            optional($item->submitted_at)->toIso8601String(),
                            optional($lastValidation?->created_at)->toIso8601String(),
                        ]);
                    }
                });

            fclose($out);
        }, $filename, ['Content-Type' => 'text/csv; charset=UTF-8']);
    }

    /**
     * Export des récits naturels après transcription, segmentation, traduction française
     * et validation du récit parent. Tous les segments d'un même récit gardent le même split.
     */
    public function naturalSpeechCsv(): StreamedResponse
    {
        $version = (string) config('dataset.version', '0.1.0');
        $filename = 'langue-san-'.$version.'-natural-san-fr-approved-'.now()->format('Ymd-His').'.csv';

        return response()->streamDownload(function () use ($version) {
            $out = fopen('php://output', 'w');
            fputcsv($out, [
                'dataset_version',
                'source_id',
                'segment_position',
                'split',
                'direction',
                'elicitation_prompt_code',
                'elicitation_prompt',
                'variety',
                'variety_iso',
                'san',
                'french',
                'category',
                'locality',
                'submitted_at',
                'approved_at',
            ]);

            $this->naturalSpeechEligibleQuery()
                ->with([
                    'variety',
                    'contribution.prompt.category',
                    'contribution.locality',
                    'contribution.validations.variety',
                    'contribution.contributorProfile.consents.consentVersion',
                ])
                ->orderBy('id')
                ->chunkById(500, function ($segments) use ($out, $version) {
                    foreach ($segments as $segment) {
                        $contribution = $segment->contribution;
                        $lastValidation = $contribution->validations->last();
                        $variety = $segment->variety ?? $lastValidation?->variety;

                        if (! $variety) {
                            continue;
                        }

                        $sourceId = $this->sourceId($contribution->id);

                        fputcsv($out, [
                            $version,
                            $sourceId,
                            $segment->position,
                            $this->splitFor($sourceId),
                            'san-fr',
                            $contribution->prompt->code,
                            $contribution->prompt->french_text,
                            $variety->name,
                            $variety->iso_code,
                            $segment->san_text,
                            $segment->french_translation,
                            $contribution->prompt->category->name,
                            $contribution->locality?->name,
                            optional($contribution->submitted_at)->toIso8601String(),
                            optional($lastValidation?->created_at)->toIso8601String(),
                        ]);
                    }
                });

            fclose($out);
        }, $filename, ['Content-Type' => 'text/csv; charset=UTF-8']);
    }

    private function translationEligibleQuery()
    {
        return Contribution::query()
            ->where('status', ContributionStatus::APPROVED->value)
            ->whereNotNull('san_text')
            ->whereHas('prompt', fn ($query) => $query->whereIn('type', [PromptType::WORD->value, PromptType::SENTENCE->value]))
            ->whereHas('validations', fn ($query) => $query->whereNotNull('variety_id'))
            ->whereHas(
                'contributorProfile.consents.consentVersion',
                fn ($query) => $query->where('allow_training', true),
            );
    }

    private function naturalSpeechEligibleQuery()
    {
        return ContributionSegment::query()
            ->whereNotNull('san_text')
            ->whereNotNull('french_translation')
            ->whereHas('contribution', function ($query) {
                $query
                    ->where('status', ContributionStatus::APPROVED->value)
                    ->whereHas('prompt', fn ($query) => $query->where('type', PromptType::NARRATIVE->value))
                    ->whereHas('validations', fn ($query) => $query->whereNotNull('variety_id'))
                    ->whereHas(
                        'contributorProfile.consents.consentVersion',
                        fn ($query) => $query->where('allow_training', true),
                    );
            });
    }

    private function sourceId(int $contributionId): string
    {
        return substr(hash_hmac(
            'sha256',
            'contribution:'.$contributionId,
            (string) config('dataset.source_salt', 'langue-san-local-dev-salt'),
        ), 0, 24);
    }

    private function splitFor(string $sourceId): string
    {
        $bucket = hexdec(substr(hash('sha256', $sourceId), 0, 8)) % 100;
        $splits = config('dataset.splits', ['train' => 80, 'validation' => 10, 'test' => 10]);
        $train = (int) ($splits['train'] ?? 80);
        $validation = (int) ($splits['validation'] ?? 10);

        if ($bucket < $train) {
            return 'train';
        }

        if ($bucket < $train + $validation) {
            return 'validation';
        }

        return 'test';
    }
}

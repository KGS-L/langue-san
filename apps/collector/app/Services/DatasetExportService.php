<?php

namespace App\Services;

use App\Enums\ContributionStatus;
use App\Enums\ValidationDecision;
use App\Models\Contribution;
use Symfony\Component\HttpFoundation\StreamedResponse;

class DatasetExportService
{
    public function csv(): StreamedResponse
    {
        $filename = 'langue-san-approved-'.now()->format('Ymd-His').'.csv';

        return response()->streamDownload(function () {
            $out = fopen('php://output', 'w');
            fputcsv($out, ['id', 'variety', 'french', 'san', 'type', 'category', 'locality', 'validation_level']);

            Contribution::query()
                ->where('status', ContributionStatus::APPROVED->value)
                ->whereNotNull('san_text')
                ->whereHas('user.consents.consentVersion', fn ($q) => $q->where('allow_training', true))
                ->with(['prompt.category', 'locality', 'validations.variety'])
                ->orderBy('id')
                ->chunkById(500, function ($items) use ($out) {
                    foreach ($items as $item) {
                        $lastValidation = $item->validations->last();
                        $variety = $lastValidation?->variety?->name;
                        if (!$variety) continue;

                        $san = $lastValidation?->decision === ValidationDecision::CORRECT
                            ? $lastValidation->san_text_corrected
                            : $item->san_text;

                        fputcsv($out, [
                            $item->id,
                            $variety,
                            $item->prompt->french_text,
                            $san,
                            $item->prompt->type->value,
                            $item->prompt->category->name,
                            $item->locality?->name,
                            $item->validations->count(),
                        ]);
                    }
                });

            fclose($out);
        }, $filename, ['Content-Type' => 'text/csv; charset=UTF-8']);
    }
}

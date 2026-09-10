<?php

namespace App\Services;

use App\Enums\ContributionStatus;
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
                ->whereHas('user', fn ($q) => $q->whereHas('consents', fn ($c) => $c->whereHas('consentVersion', fn ($v) => $v->where('allow_training', true))))
                ->with(['prompt.category', 'locality', 'validations.variety'])
                ->orderBy('id')
                ->chunk(500, function ($items) use ($out) {
                    foreach ($items as $item) {
                        $variety = $item->validations->last()?->variety?->name;
                        if (!$variety) continue;
                        fputcsv($out, [$item->id, $variety, $item->prompt->french_text, $item->san_text, $item->prompt->type->value, $item->prompt->category->name, $item->locality?->name, $item->validations->count()]);
                    }
                });

            fclose($out);
        }, $filename, ['Content-Type' => 'text/csv; charset=UTF-8']);
    }
}

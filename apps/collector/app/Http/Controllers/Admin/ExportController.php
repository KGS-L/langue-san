<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Services\DatasetExportService;

class ExportController extends Controller
{
    public function index(DatasetExportService $service)
    {
        abort_unless(auth()->user()?->can('export dataset'), 403);

        return view('admin.exports.index', [
            'summary' => $service->summary(),
        ]);
    }

    public function download(DatasetExportService $service)
    {
        abort_unless(auth()->user()?->can('export dataset'), 403);

        return $service->csv();
    }

    public function downloadNaturalSpeech(DatasetExportService $service)
    {
        abort_unless(auth()->user()?->can('export dataset'), 403);

        return $service->naturalSpeechCsv();
    }
}

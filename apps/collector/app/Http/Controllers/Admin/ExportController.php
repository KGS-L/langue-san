<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Services\DatasetExportService;

class ExportController extends Controller
{
    public function index()
    {
        abort_unless(auth()->user()?->can('export dataset'), 403);

        return view('admin.exports.index');
    }

    public function download(DatasetExportService $service)
    {
        abort_unless(auth()->user()?->can('export dataset'), 403);

        return $service->csv();
    }
}

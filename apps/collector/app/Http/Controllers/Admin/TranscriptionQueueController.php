<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\Contribution\ContributionQueueFilterRequest;
use App\Services\CategoryService;
use App\Services\ContributionService;
use App\Services\LocalityService;
use Illuminate\View\View;

class TranscriptionQueueController extends Controller
{
    public function __invoke(
        ContributionQueueFilterRequest $request,
        ContributionService $contributions,
        CategoryService $categories,
        LocalityService $localities,
    ): View {
        abort_unless($request->user()->can('transcribe contributions'), 403);

        return view('admin.transcriptions.index', [
            'contributions' => $contributions->transcriptionQueue($request->validated()),
            'categories' => $categories->active(),
            'localities' => $localities->active(),
            'filters' => $request->validated(),
        ]);
    }
}

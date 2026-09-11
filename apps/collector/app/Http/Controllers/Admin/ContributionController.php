<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\Contribution\TranscribeContributionRequest;
use App\Models\Contribution;
use App\Services\ContributionService;
use App\Services\VarietyService;

class ContributionController extends Controller
{
    public function __construct(
        private readonly ContributionService $service,
        private readonly VarietyService $varieties,
    ) {}

    public function index()
    {
        $this->authorize('viewAny', Contribution::class);

        return view('admin.contributions.index', [
            'contributions' => $this->service->paginate(),
        ]);
    }

    public function show(Contribution $contribution)
    {
        $this->authorize('view', $contribution);

        $contribution->load([
            'contributorProfile.user',
            'prompt.category',
            'locality.suggestedVariety',
            'recording',
            'segments.variety',
            'validations.validator',
            'validations.variety',
        ]);

        return view('admin.contributions.show', [
            'contribution' => $contribution,
            'varieties' => $this->varieties->active(),
        ]);
    }

    public function transcribe(
        TranscribeContributionRequest $request,
        Contribution $contribution,
    ) {
        $this->service->transcribe($contribution, $request->validated('san_text'));

        return back()->with('success', 'Transcription enregistrée.');
    }
}

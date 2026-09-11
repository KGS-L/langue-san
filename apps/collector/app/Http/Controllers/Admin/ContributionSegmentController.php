<?php

namespace App\Http\Controllers\Admin;

use App\Enums\ContributionStatus;
use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\Contribution\SaveContributionSegmentRequest;
use App\Models\Contribution;
use App\Models\ContributionSegment;
use App\Services\ContributionSegmentService;
use App\Services\VarietyService;
use Illuminate\Http\RedirectResponse;

class ContributionSegmentController extends Controller
{
    public function __construct(
        private readonly ContributionSegmentService $segments,
        private readonly VarietyService $varieties,
    ) {}

    public function index(Contribution $contribution)
    {
        $this->authorize('view', $contribution);
        $contribution->load(['prompt.category', 'locality.suggestedVariety', 'recording', 'validations']);

        return view('admin.contributions.segments', [
            'contribution' => $contribution,
            'segments' => $this->segments->forContribution($contribution),
            'varieties' => $this->varieties->active(),
            'editable' => auth()->user()->can('update', $contribution)
                && $contribution->status === ContributionStatus::TRANSCRIBED
                && $contribution->validations->isEmpty(),
        ]);
    }

    public function store(
        SaveContributionSegmentRequest $request,
        Contribution $contribution,
    ): RedirectResponse {
        $this->segments->create($contribution, $request->user(), $request->validated());
        return back()->with('success', 'Segment ajouté.');
    }

    public function update(
        SaveContributionSegmentRequest $request,
        Contribution $contribution,
        ContributionSegment $segment,
    ): RedirectResponse {
        $this->segments->update($contribution, $segment, $request->user(), $request->validated());
        return back()->with('success', 'Segment mis à jour.');
    }

    public function destroy(
        Contribution $contribution,
        ContributionSegment $segment,
    ): RedirectResponse {
        $this->authorize('update', $contribution);
        $this->segments->delete($contribution, $segment);
        return back()->with('success', 'Segment supprimé.');
    }
}

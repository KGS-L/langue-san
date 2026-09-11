<?php

namespace App\Http\Controllers\Admin;

use App\Enums\ProjectApplicationStatus;
use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\ProjectApplication\ReviewProjectApplicationRequest;
use App\Models\ProjectApplication;
use App\Services\ProjectApplicationService;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class ProjectApplicationController extends Controller
{
    public function __construct(private readonly ProjectApplicationService $applications) {}

    public function index(): View
    {
        abort_unless(auth()->user()?->can('review project applications'), 403);

        return view('admin.project-applications.index', [
            'applications' => $this->applications->paginate(),
        ]);
    }

    public function show(ProjectApplication $projectApplication): View
    {
        abort_unless(auth()->user()?->can('review project applications'), 403);

        $projectApplication->load(['user.userProfile', 'reviewer']);

        return view('admin.project-applications.show', [
            'application' => $projectApplication,
        ]);
    }

    public function review(
        ReviewProjectApplicationRequest $request,
        ProjectApplication $projectApplication,
    ): RedirectResponse {
        $this->applications->review(
            application: $projectApplication,
            reviewer: $request->user(),
            decision: ProjectApplicationStatus::from($request->validated('decision')),
            reason: $request->validated('decision_reason'),
        );

        return redirect()->route('admin.project-applications.show', $projectApplication)
            ->with('success', 'La décision a été enregistrée et la personne a été informée.');
    }
}

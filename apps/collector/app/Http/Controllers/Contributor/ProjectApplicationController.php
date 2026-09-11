<?php

namespace App\Http\Controllers\Contributor;

use App\Enums\ProjectContributionArea;
use App\Http\Controllers\Controller;
use App\Http\Requests\Contributor\StoreProjectApplicationRequest;
use App\Services\ProjectApplicationService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ProjectApplicationController extends Controller
{
    public function create(Request $request, ProjectApplicationService $applications): View
    {
        $user = $request->user();

        return view('public.join-project', [
            'areas' => ProjectContributionArea::cases(),
            'user' => $user,
            'application' => $user?->isContributor() ? $applications->latestForUser($user) : null,
            'profileComplete' => $user?->isContributor()
                ? ! $user->loadMissing('userProfile')->needsContributorOnboarding()
                : false,
        ]);
    }

    public function store(
        StoreProjectApplicationRequest $request,
        ProjectApplicationService $applications,
    ): RedirectResponse {
        $applications->submit($request->user(), $request->validated());

        return redirect()->route('contributor.dashboard')
            ->with('success', 'Votre candidature a bien été envoyée.');
    }
}

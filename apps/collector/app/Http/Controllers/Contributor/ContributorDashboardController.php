<?php

namespace App\Http\Controllers\Contributor;

use App\Http\Controllers\Controller;
use App\Services\ContributorDashboardService;
use App\Services\ProjectApplicationService;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ContributorDashboardController extends Controller
{
    public function __invoke(
        Request $request,
        ContributorDashboardService $dashboard,
        ProjectApplicationService $applications,
    ): View {
        return view('contributor.dashboard', [
            ...$dashboard->data($request->user()),
            'application' => $applications->latestForUser($request->user()),
            'user' => $request->user()->loadMissing(['userProfile', 'projectMembership']),
        ]);
    }
}

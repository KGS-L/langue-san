<?php

namespace App\Http\Controllers\Contributor;

use App\Enums\ContributionStatus;
use App\Http\Controllers\Controller;
use App\Services\ContributorDashboardService;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ContributorHistoryController extends Controller
{
    public function __invoke(Request $request, ContributorDashboardService $dashboard): View
    {
        $status = $request->string('status')->toString();

        return view('contributor.history', [
            'contributions' => $dashboard->history($request->user(), $status ?: null),
            'statuses' => ContributionStatus::cases(),
            'activeStatus' => $status,
        ]);
    }
}

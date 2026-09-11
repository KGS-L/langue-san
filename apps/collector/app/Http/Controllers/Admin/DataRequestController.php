<?php

namespace App\Http\Controllers\Admin;

use App\Enums\DataRequestStatus;
use App\Enums\DataRequestType;
use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\ReviewDataRequest;
use App\Models\DataRequest;
use App\Services\DataRequestService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class DataRequestController extends Controller
{
    public function index(Request $request, DataRequestService $service)
    {
        abort_unless($request->user()?->can('review data requests'), 403);

        return view('admin.data-requests.index', [
            'requests' => $service->paginate($request->only(['status', 'type'])),
            'statuses' => DataRequestStatus::cases(),
            'types' => DataRequestType::cases(),
        ]);
    }

    public function review(
        ReviewDataRequest $request,
        DataRequest $dataRequest,
        DataRequestService $service,
    ): RedirectResponse {
        $service->review($dataRequest, $request->user(), $request->validated());

        return back()->with('success', 'Demande mise à jour.');
    }
}

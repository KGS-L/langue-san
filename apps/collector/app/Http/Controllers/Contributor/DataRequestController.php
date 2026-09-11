<?php

namespace App\Http\Controllers\Contributor;

use App\Enums\DataRequestType;
use App\Http\Controllers\Controller;
use App\Http\Requests\Contributor\StoreDataRequestRequest;
use App\Services\DataRequestService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class DataRequestController extends Controller
{
    public function index(Request $request, DataRequestService $service): Response
    {
        $profile = $request->user()->contributorProfile()->firstOrFail();

        return response()->view('contributor.data-requests', [
            'profile' => $profile,
            'types' => DataRequestType::cases(),
            'contributions' => $profile->contributions()
                ->with(['prompt.category', 'recording'])
                ->latest('submitted_at')
                ->limit(100)
                ->get(),
            'requests' => $service->forContributor($profile),
        ]);
    }

    public function store(StoreDataRequestRequest $request, DataRequestService $service): RedirectResponse
    {
        $profile = $request->user()->contributorProfile()->firstOrFail();
        $dataRequest = $service->submit($profile, $request->validated());

        $message = $dataRequest->status->value === 'completed'
            ? 'Votre demande a été appliquée immédiatement.'
            : 'Votre demande a été enregistrée. Elle sera traitée par l’équipe.';

        return back()->with('success', $message);
    }
}

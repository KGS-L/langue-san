<?php

namespace App\Http\Controllers\Contributor;

use App\Enums\AgeRange;
use App\Enums\ProfessionType;
use App\Http\Controllers\Controller;
use App\Http\Requests\Contributor\CompleteProfileRequest;
use App\Http\Requests\Contributor\UpdatePublicProfileRequest;
use App\Services\UserProfileService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ContributorProfileController extends Controller
{
    public function __construct(private readonly UserProfileService $profiles) {}

    public function edit(Request $request): View
    {
        $profile = $this->profiles->forUser($request->user());

        return view('contributor.profile', [
            'profile' => $profile,
            'ageRanges' => AgeRange::cases(),
            'professions' => ProfessionType::cases(),
        ]);
    }

    public function update(CompleteProfileRequest $request): RedirectResponse
    {
        $this->profiles->completeOnboarding($request->user(), $request->validated());

        return redirect()->route('contributor.dashboard')
            ->with('success', 'Votre profil a été enregistré.');
    }

    public function updatePublic(UpdatePublicProfileRequest $request): RedirectResponse
    {
        $this->profiles->updatePublicProfile($request->user(), $request->validated());

        return back()->with('success', 'Vos préférences de visibilité ont été mises à jour.');
    }
}

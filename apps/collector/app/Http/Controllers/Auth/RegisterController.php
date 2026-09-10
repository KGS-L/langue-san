<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\RegisterContributorRequest;
use App\Services\ContributorIdentityService;
use App\Services\UserService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Auth;

class RegisterController extends Controller
{
    public function create()
    {
        return view('auth.register');
    }

    public function store(
        RegisterContributorRequest $request,
        UserService $users,
        ContributorIdentityService $identities,
    ): RedirectResponse {
        $user = $users->createContributor($request->validated());

        $identities->claimGuestProfile(
            $request->cookie(ContributorIdentityService::COOKIE_NAME),
            $user,
        );

        Auth::login($user);
        $request->session()->regenerate();

        return redirect()->route('contributor.home');
    }
}

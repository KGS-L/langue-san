<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\RegisterContributorRequest;
use App\Services\UserService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Auth;

class RegisterController extends Controller
{
    public function create() { return view('auth.register'); }

    public function store(RegisterContributorRequest $request, UserService $service): RedirectResponse
    {
        $user = $service->createContributor($request->validated());
        Auth::login($user);
        $request->session()->regenerate();
        return redirect()->route('contributor.home');
    }
}

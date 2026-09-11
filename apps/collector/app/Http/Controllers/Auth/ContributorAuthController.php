<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\SendContributorOtpRequest;
use App\Http\Requests\Auth\VerifyContributorOtpRequest;
use App\Services\ContributorIdentityService;
use App\Services\ContributorOtpService;
use App\Services\GoogleOAuthService;
use App\Services\UserService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class ContributorAuthController extends Controller
{
    public function show(Request $request, GoogleOAuthService $google): View|RedirectResponse
    {
        if ($request->user()?->isContributor()) {
            return redirect()->route('contributor.home');
        }

        if ($request->user()?->isStaff()) {
            return redirect()->route('admin.dashboard');
        }

        return view('auth.contributor', [
            'googleConfigured' => $google->isConfigured(),
            'email' => old('email', (string) $request->session()->get('contributor_otp_email', '')),
            'codeSent' => (bool) $request->session()->get('contributor_otp_sent', false),
        ]);
    }

    public function sendCode(
        SendContributorOtpRequest $request,
        ContributorOtpService $otp,
    ): RedirectResponse {
        $email = strtolower((string) $request->validated('email'));
        $otp->send($email, $request->ip());

        $request->session()->put([
            'contributor_otp_email' => $email,
            'contributor_otp_sent' => true,
        ]);

        return redirect()
            ->route('contributor.auth.show')
            ->with('success', 'Un code à 8 chiffres vient de vous être envoyé par email.');
    }

    public function verifyCode(
        VerifyContributorOtpRequest $request,
        ContributorOtpService $otp,
        UserService $users,
        ContributorIdentityService $identities,
    ): RedirectResponse {
        $data = $request->validated();
        $email = strtolower((string) $data['email']);

        $otp->verify($email, (string) $data['code']);
        $user = $users->findOrCreatePasswordlessContributor($email);

        $identities->claimGuestProfile(
            $request->cookie(ContributorIdentityService::COOKIE_NAME),
            $user,
        );

        Auth::login($user, true);
        $request->session()->regenerate();
        $request->session()->forget(['contributor_otp_email', 'contributor_otp_sent']);

        return redirect()->route('contributor.home');
    }

    public function googleRedirect(Request $request, GoogleOAuthService $google): RedirectResponse
    {
        return redirect()->away($google->authorizationUrl($request));
    }

    public function googleCallback(
        Request $request,
        GoogleOAuthService $google,
        UserService $users,
        ContributorIdentityService $identities,
    ): RedirectResponse {
        if ($request->filled('error')) {
            throw ValidationException::withMessages([
                'google' => 'La connexion Google a été annulée.',
            ]);
        }

        $profile = $google->userFromCallback($request);
        $user = $users->findOrCreatePasswordlessContributor(
            $profile['email'],
            $profile['name'] ?: null,
        );

        $identities->claimGuestProfile(
            $request->cookie(ContributorIdentityService::COOKIE_NAME),
            $user,
        );

        Auth::login($user, true);
        $request->session()->regenerate();

        return redirect()->route('contributor.home');
    }

    public function logout(Request $request): RedirectResponse
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('home');
    }
}

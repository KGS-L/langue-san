<?php

namespace App\Http\Controllers\Contributor;

use App\Http\Controllers\Controller;
use App\Http\Requests\Contributor\StartNaturalSpeechRequest;
use App\Services\ContributorConsentService;
use App\Services\ContributorIdentityService;
use App\Services\ContributorProfileService;
use App\Services\NaturalSpeechService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cookie;
use Symfony\Component\HttpFoundation\Response;

class ContributorNaturalSpeechController extends Controller
{
    public function index(
        Request $request,
        ContributorIdentityService $identities,
        ContributorProfileService $profiles,
        ContributorConsentService $consents,
        NaturalSpeechService $naturalSpeech,
    ): Response {
        if ($request->user()?->isStaff()) {
            return redirect()->route('admin.dashboard');
        }

        $identity = $identities->resolve(
            $request->user(),
            $request->cookie(ContributorIdentityService::COOKIE_NAME),
        );

        if (! $profiles->hasCompleteContext($identity->profile)) {
            $response = redirect()
                ->route('contributor.context.edit', ['next' => 'natural-speech'])
                ->with('warning', 'Complétez d’abord votre contexte linguistique.');

            return $this->withGuestCookie($response, $identity->guestToken, $identity->shouldSetCookie);
        }

        $response = response()->view('contributor.natural-speech', [
            'profile' => $identity->profile,
            'prompts' => $naturalSpeech->availableFor($identity->profile),
            'consentVersion' => $consents->currentVersion(),
            'hasAcceptedConsent' => $consents->hasAcceptedCurrent($identity->profile),
        ]);

        return $this->withGuestCookie($response, $identity->guestToken, $identity->shouldSetCookie);
    }

    public function store(
        StartNaturalSpeechRequest $request,
        ContributorIdentityService $identities,
        ContributorProfileService $profiles,
        ContributorConsentService $consents,
        NaturalSpeechService $naturalSpeech,
    ): RedirectResponse {
        if ($request->user()?->isStaff()) {
            return redirect()->route('admin.dashboard');
        }

        $identity = $identities->resolve(
            $request->user(),
            $request->cookie(ContributorIdentityService::COOKIE_NAME),
        );

        if (! $profiles->hasCompleteContext($identity->profile)) {
            return redirect()->route('contributor.context.edit', ['next' => 'natural-speech']);
        }

        $consents->acceptCurrent($identity->profile, $request->ip());
        $session = $naturalSpeech->start($identity->profile, (int) $request->validated('prompt_id'));

        return redirect()->route('contributor.sessions.show', $session);
    }

    private function withGuestCookie(Response $response, ?string $token, bool $shouldSet): Response
    {
        if ($shouldSet && $token) {
            $response->headers->setCookie(Cookie::make(
                ContributorIdentityService::COOKIE_NAME,
                $token,
                60 * 24 * 365,
                '/',
                null,
                app()->isProduction(),
                true,
                false,
                'lax',
            ));
        }

        return $response;
    }
}

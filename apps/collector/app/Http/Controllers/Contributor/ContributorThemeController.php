<?php

namespace App\Http\Controllers\Contributor;

use App\Http\Controllers\Controller;
use App\Http\Requests\Contributor\StartSessionRequest;
use App\Services\CategoryService;
use App\Services\CollectionSessionService;
use App\Services\ContributorConsentService;
use App\Services\ContributorIdentityService;
use App\Services\ContributorProfileService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cookie;
use Symfony\Component\HttpFoundation\Response;

class ContributorThemeController extends Controller
{
    public function index(
        Request $request,
        ContributorIdentityService $identities,
        ContributorProfileService $profiles,
        ContributorConsentService $consents,
        CategoryService $categories,
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
                ->route('contributor.context.edit')
                ->with('warning', 'Complétez d’abord votre contexte linguistique.');

            return $this->withGuestCookie($response, $identity->guestToken, $identity->shouldSetCookie);
        }

        $response = response()->view('contributor.themes', [
            'profile' => $identity->profile,
            'categories' => $categories->activeForCollection(),
            'consentVersion' => $consents->currentVersion(),
            'hasAcceptedConsent' => $consents->hasAcceptedCurrent($identity->profile),
        ]);

        return $this->withGuestCookie($response, $identity->guestToken, $identity->shouldSetCookie);
    }

    public function store(
        StartSessionRequest $request,
        ContributorIdentityService $identities,
        ContributorProfileService $profiles,
        ContributorConsentService $consents,
        CollectionSessionService $sessions,
    ): RedirectResponse {
        if ($request->user()?->isStaff()) {
            return redirect()->route('admin.dashboard');
        }

        $identity = $identities->resolve(
            $request->user(),
            $request->cookie(ContributorIdentityService::COOKIE_NAME),
        );

        if (! $profiles->hasCompleteContext($identity->profile)) {
            return redirect()->route('contributor.context.edit');
        }

        $data = $request->validated();
        $consents->acceptCurrent($identity->profile, $request->ip());
        $session = $sessions->start($identity->profile, (int) $data['category_id']);

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

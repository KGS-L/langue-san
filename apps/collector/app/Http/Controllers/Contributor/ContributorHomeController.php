<?php

namespace App\Http\Controllers\Contributor;

use App\Http\Controllers\Controller;
use App\Services\CollectionSessionService;
use App\Services\ContributorIdentityService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cookie;
use Symfony\Component\HttpFoundation\Response;

class ContributorHomeController extends Controller
{
    public function __invoke(
        Request $request,
        ContributorIdentityService $identities,
        CollectionSessionService $sessions,
    ): Response {
        if ($request->user()?->isStaff()) {
            return redirect()->route('admin.dashboard');
        }

        $identity = $identities->resolve(
            $request->user(),
            $request->cookie(ContributorIdentityService::COOKIE_NAME),
        );

        $activeSession = $sessions->activeForContributor($identity->profile);

        if ($activeSession) {
            $response = redirect()->route('contributor.sessions.show', $activeSession);
            return $this->withGuestCookie($response, $identity->guestToken, $identity->shouldSetCookie);
        }

        $response = response()->view('contributor.home', [
            'profile' => $identity->profile,
        ]);

        return $this->withGuestCookie($response, $identity->guestToken, $identity->shouldSetCookie);
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

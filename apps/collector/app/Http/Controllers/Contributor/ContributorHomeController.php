<?php

namespace App\Http\Controllers\Contributor;

use App\Http\Controllers\Controller;
use App\Services\ContributorIdentityService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cookie;
use Symfony\Component\HttpFoundation\Response;

class ContributorHomeController extends Controller
{
    public function __invoke(Request $request, ContributorIdentityService $identities): Response
    {
        if ($request->user()?->isStaff()) {
            return redirect()->route('admin.dashboard');
        }

        $identity = $identities->resolve(
            $request->user(),
            $request->cookie(ContributorIdentityService::COOKIE_NAME),
        );

        $response = response()->view('contributor.home', [
            'profile' => $identity->profile,
        ]);

        if ($identity->shouldSetCookie && $identity->guestToken) {
            $response->headers->setCookie(Cookie::make(
                ContributorIdentityService::COOKIE_NAME,
                $identity->guestToken,
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

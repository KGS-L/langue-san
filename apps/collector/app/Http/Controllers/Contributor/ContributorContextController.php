<?php

namespace App\Http\Controllers\Contributor;

use App\Http\Controllers\Controller;
use App\Http\Requests\Contributor\UpdateContextRequest;
use App\Services\ContributorIdentityService;
use App\Services\ContributorProfileService;
use App\Services\LocalityService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cookie;
use Symfony\Component\HttpFoundation\Response;

class ContributorContextController extends Controller
{
    public function edit(
        Request $request,
        ContributorIdentityService $identities,
        LocalityService $localities,
    ): Response {
        if ($request->user()?->isStaff()) {
            return redirect()->route('admin.dashboard');
        }

        $identity = $identities->resolve(
            $request->user(),
            $request->cookie(ContributorIdentityService::COOKIE_NAME),
        );

        $response = response()->view('contributor.context', [
            'profile' => $identity->profile,
            'localities' => $localities->active(),
            'nextMode' => in_array($request->query('next'), ['translation', 'natural-speech'], true)
                ? $request->query('next')
                : 'translation',
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

    public function update(
        UpdateContextRequest $request,
        ContributorIdentityService $identities,
        ContributorProfileService $profiles,
    ): RedirectResponse {
        if ($request->user()?->isStaff()) {
            return redirect()->route('admin.dashboard');
        }

        $identity = $identities->resolve(
            $request->user(),
            $request->cookie(ContributorIdentityService::COOKIE_NAME),
        );

        $data = $request->validated();
        $profiles->updateContext($identity->profile, $data);

        if (($data['next'] ?? null) === 'natural-speech') {
            return redirect()
                ->route('contributor.natural-speech.index')
                ->with('success', 'Votre contexte linguistique a été enregistré. Choisissez maintenant un sujet de parole naturelle.');
        }

        return redirect()
            ->route('contributor.themes.index')
            ->with('success', 'Votre contexte linguistique a été enregistré. Choisissez maintenant un thème.');
    }
}

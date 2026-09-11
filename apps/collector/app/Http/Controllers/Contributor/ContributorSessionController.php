<?php

namespace App\Http\Controllers\Contributor;

use App\Enums\PromptType;
use App\Http\Controllers\Controller;
use App\Http\Requests\Contributor\SkipPromptRequest;
use App\Http\Requests\Contributor\SubmitContributionRequest;
use App\Models\CollectionSession;
use App\Models\SessionPrompt;
use App\Services\CollectionSessionService;
use App\Services\ContributionSubmissionService;
use App\Services\ContributorIdentityService;
use App\Services\ContributorProfileService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cookie;
use Symfony\Component\HttpFoundation\Response;

class ContributorSessionController extends Controller
{
    public function show(
        Request $request,
        CollectionSession $session,
        ContributorIdentityService $identities,
        ContributorProfileService $profiles,
        CollectionSessionService $sessions,
    ): Response {
        if ($request->user()?->isStaff()) {
            return redirect()->route('admin.dashboard');
        }

        $identity = $identities->resolve(
            $request->user(),
            $request->cookie(ContributorIdentityService::COOKIE_NAME),
        );

        if (! $profiles->hasCompleteContext($identity->profile)) {
            $response = redirect()->route('contributor.context.edit');
            return $this->withGuestCookie($response, $identity->guestToken, $identity->shouldSetCookie);
        }

        $session = $sessions->forContributorOrFail($session->id, $identity->profile);
        $current = $sessions->nextPrompt($session);

        if (! $current) {
            $session = $sessions->completeIfFinished($session);
            $response = response()->view('contributor.completed', [
                'profile' => $identity->profile,
                'session' => $session,
                'progress' => $sessions->progress($session),
            ]);

            return $this->withGuestCookie($response, $identity->guestToken, $identity->shouldSetCookie);
        }

        $view = $current->prompt->type === PromptType::NARRATIVE
            ? 'contributor.natural-session'
            : 'contributor.session';

        $response = response()->view($view, [
            'profile' => $identity->profile,
            'session' => $session,
            'sessionPrompt' => $current,
            'progress' => $sessions->progress($session),
        ]);

        return $this->withGuestCookie($response, $identity->guestToken, $identity->shouldSetCookie);
    }

    public function submit(
        SubmitContributionRequest $request,
        CollectionSession $session,
        SessionPrompt $sessionPrompt,
        ContributorIdentityService $identities,
        CollectionSessionService $sessions,
        ContributionSubmissionService $submissions,
    ): RedirectResponse {
        $identity = $identities->resolve(
            $request->user(),
            $request->cookie(ContributorIdentityService::COOKIE_NAME),
        );

        $session = $sessions->forContributorOrFail($session->id, $identity->profile);
        $sessionPrompt = $sessions->promptOrFail($session, $sessionPrompt->id);

        $submissions->submit(
            $session,
            $sessionPrompt,
            $identity->profile,
            $request->validated(),
            $request->file('audio'),
        );

        return redirect()->route('contributor.sessions.show', $session);
    }

    public function skip(
        SkipPromptRequest $request,
        CollectionSession $session,
        SessionPrompt $sessionPrompt,
        ContributorIdentityService $identities,
        CollectionSessionService $sessions,
    ): RedirectResponse {
        $identity = $identities->resolve(
            $request->user(),
            $request->cookie(ContributorIdentityService::COOKIE_NAME),
        );

        $session = $sessions->forContributorOrFail($session->id, $identity->profile);
        $sessionPrompt = $sessions->promptOrFail($session, $sessionPrompt->id);
        $sessions->skip($sessionPrompt);

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

<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureContributorProfileComplete
{
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        if (! $user || ! $user->isContributor()) {
            abort(403);
        }

        $user->loadMissing('userProfile');

        if ($user->needsContributorOnboarding()) {
            return redirect()->route('contributor.profile.edit')
                ->with('info', 'Complétez votre profil avant de continuer.');
        }

        return $next($request);
    }
}

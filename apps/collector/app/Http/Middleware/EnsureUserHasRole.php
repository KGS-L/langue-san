<?php

namespace App\Http\Middleware;

use App\Enums\UserStatus;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class EnsureUserHasRole
{
    public function handle(Request $request, Closure $next, string ...$roles): Response
    {
        $user = $request->user();
        abort_unless($user, 401);

        if ($user->status !== UserStatus::ACTIVE) {
            $wasContributor = $user->isContributor();
            Auth::logout();
            $request->session()->invalidate();
            $request->session()->regenerateToken();

            $route = $wasContributor ? 'contributor.auth.show' : 'login';

            return redirect()->route($route)->withErrors([
                'email' => 'Ce compte est suspendu.',
            ]);
        }

        abort_unless($user->hasAnyRole($roles), 403);

        return $next($request);
    }
}

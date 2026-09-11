<?php

use App\Http\Controllers\Auth\ContributorAuthController;
use App\Http\Controllers\Contributor\ProjectApplicationController;
use App\Http\Controllers\Public\CommunityController;
use App\Http\Controllers\Public\HomeController;
use App\Services\ContributorIdentityService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::get('/', HomeController::class)->name('home');
Route::view('/confidentialite', 'public.privacy')->name('privacy');
Route::view('/politique-de-contribution', 'public.contribution-policy')->name('contribution-policy');
Route::view('/gouvernance-des-donnees', 'public.data-governance')->name('data-governance');
Route::get('/communaute', CommunityController::class)->name('community');
Route::get('/rejoindre-le-projet', [ProjectApplicationController::class, 'create'])->name('project.join');

Route::middleware('guest')->group(function () {
    Route::get('/compte', [ContributorAuthController::class, 'show'])->name('contributor.auth.show');
    Route::post('/compte/code', [ContributorAuthController::class, 'sendCode'])->name('contributor.auth.email.send');
    Route::post('/compte/verifier', [ContributorAuthController::class, 'verifyCode'])->name('contributor.auth.email.verify');
    Route::get('/compte/google', [ContributorAuthController::class, 'googleRedirect'])->name('contributor.auth.google.redirect');
    Route::get('/compte/google/callback', [ContributorAuthController::class, 'googleCallback'])->name('contributor.auth.google.callback');

    Route::redirect('/register', '/compte')->name('register');
    Route::redirect('/connexion', '/compte')->name('contributor.login');
});

Route::post('/compte/deconnexion', [ContributorAuthController::class, 'logout'])
    ->middleware('auth')
    ->name('contributor.logout');

Route::get('/auth/redirect', function (Request $request, ContributorIdentityService $identities) {
    $user = $request->user()->loadMissing('userProfile');

    if ($user->isContributor()) {
        $identities->claimGuestProfile(
            $request->cookie(ContributorIdentityService::COOKIE_NAME),
            $user,
        );

        return $user->needsContributorOnboarding()
            ? redirect()->route('contributor.profile.edit')
            : redirect()->route('contributor.dashboard');
    }

    return redirect()->route('admin.dashboard');
})->middleware('auth')->name('auth.redirect');

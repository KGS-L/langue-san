<?php

use App\Http\Controllers\Auth\RegisterController;
use App\Services\ContributorIdentityService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::view('/', 'public.home')->name('home');

Route::middleware('guest')->group(function () {
    Route::get('/register', [RegisterController::class, 'create'])->name('register');
    Route::post('/register', [RegisterController::class, 'store']);
});

Route::get('/auth/redirect', function (Request $request, ContributorIdentityService $identities) {
    $user = $request->user();

    if ($user->isContributor()) {
        $identities->claimGuestProfile(
            $request->cookie(ContributorIdentityService::COOKIE_NAME),
            $user,
        );
    }

    return $user->isStaff()
        ? redirect()->route('admin.dashboard')
        : redirect()->route('contributor.home');
})->middleware('auth')->name('auth.redirect');

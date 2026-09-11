<?php

use App\Http\Controllers\Contributor\ContributorContextController;
use App\Http\Controllers\Contributor\ContributorDashboardController;
use App\Http\Controllers\Contributor\ContributorHistoryController;
use App\Http\Controllers\Contributor\ContributorHomeController;
use App\Http\Controllers\Contributor\ContributorNaturalSpeechController;
use App\Http\Controllers\Contributor\ContributorProfileController;
use App\Http\Controllers\Contributor\ContributorSessionController;
use App\Http\Controllers\Contributor\ContributorThemeController;
use App\Http\Controllers\Contributor\DataRequestController;
use App\Http\Controllers\Contributor\ProjectApplicationController;
use Illuminate\Support\Facades\Route;

Route::get('/contribute', ContributorHomeController::class)->name('contributor.home');

Route::get('/contribute/context', [ContributorContextController::class, 'edit'])->name('contributor.context.edit');
Route::post('/contribute/context', [ContributorContextController::class, 'update'])->name('contributor.context.update');

Route::get('/contribute/themes', [ContributorThemeController::class, 'index'])->name('contributor.themes.index');
Route::post('/contribute/themes', [ContributorThemeController::class, 'store'])->name('contributor.themes.store');

Route::get('/contribute/parole-naturelle', [ContributorNaturalSpeechController::class, 'index'])->name('contributor.natural-speech.index');
Route::post('/contribute/parole-naturelle', [ContributorNaturalSpeechController::class, 'store'])->name('contributor.natural-speech.store');

Route::get('/contribute/sessions/{session}', [ContributorSessionController::class, 'show'])->name('contributor.sessions.show');
Route::post('/contribute/sessions/{session}/prompts/{sessionPrompt}', [ContributorSessionController::class, 'submit'])->name('contributor.sessions.submit');
Route::post('/contribute/sessions/{session}/prompts/{sessionPrompt}/skip', [ContributorSessionController::class, 'skip'])->name('contributor.sessions.skip');

Route::middleware(['auth', 'role:contributor'])->group(function () {
    Route::get('/mon-profil', [ContributorProfileController::class, 'edit'])->name('contributor.profile.edit');
    Route::post('/mon-profil', [ContributorProfileController::class, 'update'])->name('contributor.profile.update');
    Route::post('/mon-profil/public', [ContributorProfileController::class, 'updatePublic'])->name('contributor.profile.public.update');

    Route::middleware('profile.complete')->group(function () {
        Route::get('/mon-espace', ContributorDashboardController::class)->name('contributor.dashboard');
        Route::get('/mes-contributions', ContributorHistoryController::class)->name('contributor.history');
        Route::get('/mes-donnees', [DataRequestController::class, 'index'])->name('contributor.data-requests.index');
        Route::post('/mes-donnees', [DataRequestController::class, 'store'])->name('contributor.data-requests.store');
        Route::post('/rejoindre-le-projet', [ProjectApplicationController::class, 'store'])->name('project.join.store');
    });
});

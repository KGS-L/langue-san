<?php

use App\Http\Controllers\Contributor\ContributorContextController;
use App\Http\Controllers\Contributor\ContributorHomeController;
use App\Http\Controllers\Contributor\ContributorSessionController;
use App\Http\Controllers\Contributor\ContributorThemeController;
use Illuminate\Support\Facades\Route;

Route::get('/contribute', ContributorHomeController::class)->name('contributor.home');

Route::get('/contribute/context', [ContributorContextController::class, 'edit'])->name('contributor.context.edit');
Route::post('/contribute/context', [ContributorContextController::class, 'update'])->name('contributor.context.update');

Route::get('/contribute/themes', [ContributorThemeController::class, 'index'])->name('contributor.themes.index');
Route::post('/contribute/themes', [ContributorThemeController::class, 'store'])->name('contributor.themes.store');

Route::get('/contribute/sessions/{session}', [ContributorSessionController::class, 'show'])->name('contributor.sessions.show');
Route::post('/contribute/sessions/{session}/prompts/{sessionPrompt}', [ContributorSessionController::class, 'submit'])->name('contributor.sessions.submit');
Route::post('/contribute/sessions/{session}/prompts/{sessionPrompt}/skip', [ContributorSessionController::class, 'skip'])->name('contributor.sessions.skip');

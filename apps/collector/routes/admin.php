<?php

use App\Http\Controllers\Admin\CategoryController;
use App\Http\Controllers\Admin\ContributionController;
use App\Http\Controllers\Admin\ContributionSegmentController;
use App\Http\Controllers\Admin\DashboardController;
use App\Http\Controllers\Admin\ExportController;
use App\Http\Controllers\Admin\LocalityController;
use App\Http\Controllers\Admin\ProjectApplicationController;
use App\Http\Controllers\Admin\PromptController;
use App\Http\Controllers\Admin\RecordingController;
use App\Http\Controllers\Admin\TranscriptionQueueController;
use App\Http\Controllers\Admin\UserController;
use App\Http\Controllers\Admin\ValidationController;
use App\Http\Controllers\Admin\ValidationQueueController;
use App\Http\Controllers\Admin\VarietyController;
use Illuminate\Support\Facades\Route;

Route::prefix('admin')->name('admin.')->middleware(['auth', 'role:admin,moderator,transcriber,validator'])->group(function () {
    Route::get('/', DashboardController::class)->name('dashboard');
    Route::resource('users', UserController::class)->except('show');
    Route::resource('categories', CategoryController::class)->except('show');
    Route::resource('localities', LocalityController::class)->except('show');
    Route::resource('varieties', VarietyController::class)->except('show');
    Route::post('prompts/import', [PromptController::class, 'import'])->name('prompts.import');
    Route::resource('prompts', PromptController::class)->except('show');

    Route::get('transcriptions', TranscriptionQueueController::class)->name('transcriptions.index');
    Route::get('validations', ValidationQueueController::class)->name('validations.index');

    Route::resource('contributions', ContributionController::class)->only(['index', 'show']);
    Route::post('contributions/{contribution}/transcribe', [ContributionController::class, 'transcribe'])->name('contributions.transcribe');
    Route::post('contributions/{contribution}/validations', [ValidationController::class, 'store'])->name('contributions.validations.store');
    Route::get('contributions/{contribution}/segments', [ContributionSegmentController::class, 'index'])->name('contributions.segments.index');
    Route::post('contributions/{contribution}/segments', [ContributionSegmentController::class, 'store'])->name('contributions.segments.store');
    Route::put('contributions/{contribution}/segments/{segment}', [ContributionSegmentController::class, 'update'])->name('contributions.segments.update');
    Route::delete('contributions/{contribution}/segments/{segment}', [ContributionSegmentController::class, 'destroy'])->name('contributions.segments.destroy');
    Route::get('recordings/{recording}', [RecordingController::class, 'show'])->name('recordings.show');

    Route::get('project-applications', [ProjectApplicationController::class, 'index'])->name('project-applications.index');
    Route::get('project-applications/{projectApplication}', [ProjectApplicationController::class, 'show'])->name('project-applications.show');
    Route::post('project-applications/{projectApplication}/review', [ProjectApplicationController::class, 'review'])->name('project-applications.review');
    Route::post('project-applications/{projectApplication}/access', [ProjectApplicationController::class, 'updateAccess'])->name('project-applications.access.update');

    Route::get('exports', [ExportController::class, 'index'])->name('exports.index');
    Route::get('exports/download', [ExportController::class, 'download'])->name('exports.download');
});

<?php

use App\Http\Controllers\Contributor\ContributorContextController;
use App\Http\Controllers\Contributor\ContributorHomeController;
use Illuminate\Support\Facades\Route;

Route::get('/contribute', ContributorHomeController::class)->name('contributor.home');
Route::get('/contribute/context', [ContributorContextController::class, 'edit'])->name('contributor.context.edit');
Route::post('/contribute/context', [ContributorContextController::class, 'update'])->name('contributor.context.update');

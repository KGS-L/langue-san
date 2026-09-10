<?php

use App\Http\Controllers\Contributor\ContributorHomeController;
use Illuminate\Support\Facades\Route;

Route::get('/contribute', ContributorHomeController::class)->name('contributor.home');

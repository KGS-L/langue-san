<?php

use Illuminate\Support\Facades\Route;

Route::middleware(['auth', 'role:contributor'])->group(function () {
    Route::view('/contribute', 'contributor.home')->name('contributor.home');
});

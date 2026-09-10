<?php

use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});

Route::prefix('admin')->group(function () {
    Route::view('/', 'admin.dashboard.index')->name('admin.dashboard');
    Route::view('/categories', 'admin.categories.index')->name('admin.categories.index');
    Route::view('/prompts', 'admin.prompts.index')->name('admin.prompts.index');
    Route::view('/contributions', 'admin.contributions.index')->name('admin.contributions.index');
    Route::view('/transcriptions', 'admin.transcriptions.index')->name('admin.transcriptions.index');
    Route::view('/validations', 'admin.validations.index')->name('admin.validations.index');
    Route::view('/localities', 'admin.localities.index')->name('admin.localities.index');
    Route::view('/exports', 'admin.exports.index')->name('admin.exports.index');
});

<?php

use App\Http\Controllers\Auth\RegisterController;
use Illuminate\Support\Facades\Route;

Route::view('/', 'public.home')->name('home');

Route::middleware('guest')->group(function () {
    Route::get('/register', [RegisterController::class, 'create'])->name('register');
    Route::post('/register', [RegisterController::class, 'store']);
});

Route::get('/auth/redirect', function () {
    $user = auth()->user();

    return $user->isStaff()
        ? redirect()->route('admin.dashboard')
        : redirect()->route('contributor.home');
})->middleware('auth')->name('auth.redirect');

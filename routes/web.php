<?php

use App\Http\Controllers\GoogleAuthController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});

Route::get('/login', function () {
    return view('pages.login-tenant');
})->name('login');

Route::get('/auth/google/redirect', [GoogleAuthController::class, 'redirect'])->name('google.redirect');
Route::get('/auth/google/callback', [GoogleAuthController::class, 'callback'])->name('google.callback');

Route::livewire('/dashboard', 'pages::tenant.index')->middleware('auth')->name('user.dashboard');
Route::livewire('/pilih-lapak', 'pages::tenant.slot')->middleware('auth')->name('user.pilihTenant');

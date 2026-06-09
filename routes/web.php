<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\GoogleAuthController;
use App\Http\Middleware\IsTenant;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});


Route::get('/auth/google/redirect', [GoogleAuthController::class, 'redirect'])->name('google.redirect');
Route::get('/auth/google/callback', [GoogleAuthController::class, 'callback'])->name('google.callback');
Route::livewire('/login', 'pages::tenant.login')->middleware('guest')->name('login');
Route::post('/logout', [AuthController::class, 'logout'])->name('logout');

Route::livewire('/dashboard', 'pages::tenant.index')->middleware(['auth', IsTenant::class])->name('user.dashboard');
Route::livewire('/pilih-lapak', 'pages::tenant.slot')->name('user.pilihTenant');

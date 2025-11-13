<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\DashboardController;

// Halaman utama diarahkan ke login
Route::get('/', function () {
    return view('auth.custom-login');
});

// Login & Logout
Route::get('/login', [AuthController::class, 'showLoginForm'])->name('login');
Route::post('/login', [AuthController::class, 'login'])->name('login.post');
Route::post('/logout', [AuthController::class, 'logout'])->name('logout');

// Lupa Password (sementara nonaktif)
Route::get('/password/reset', function () {
    return 'Fitur lupa password akan tersedia melalui sistem LDAP Universitas.';
})->name('password.request');

// Dashboard (hanya bisa diakses setelah login)
Route::middleware(['auth'])->group(function () {
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');
    Route::post('/dashboard/update', [DashboardController::class, 'updateProfile'])->name('dashboard.updateProfile');
});

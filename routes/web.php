<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\ProposalController;


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
    Route::post('/proposal/store', [App\Http\Controllers\ProposalController::class, 'store'])->name('proposal.store');

});

// Monitoring & Data → Kalender Timeline
Route::get('/timeline', function () {
    return view('timeline');
})->name('timeline');

Route::get('/monitoring-data', function () {
    return view('monitoring.index');
})->name('monitoring.data');

Route::get('/monitoring/data', function () {
    return view('monitoring.data');
})->name('monitoring.data');

Route::get('/monitoring-data', function () {
    return view('monitoring-data');
})->name('monitoring.data');

//upload proposal
Route::get('/proposal/create', [ProposalController::class, 'create'])->name('proposal.create');
Route::post('/proposal/store', [ProposalController::class, 'store'])->name('proposal.store');
Route::get('/proposal', [ProposalController::class, 'index'])->name('proposal.index');
Route::get('/proposal/download/{id}', [ProposalController::class, 'download'])->name('proposal.download');

// PROFILE
Route::get('/profile', [App\Http\Controllers\ProfileController::class, 'show'])->name('profile.show');
Route::get('/profile/edit', [App\Http\Controllers\ProfileController::class, 'edit'])->name('profile.edit');
Route::post('/profile/update', [App\Http\Controllers\ProfileController::class, 'update'])->name('profile.update');

//PROPOSAL
Route::get('/daftar-proposal', function () {
    return view('proposal.daftar-proposal');
});



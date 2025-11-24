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

// Lupa Password
Route::get('/password/reset', function () {
    return 'Fitur lupa password akan tersedia melalui sistem LDAP Universitas.';
})->name('password.request');

// Dashboard
Route::middleware(['auth'])->group(function () {
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');
    Route::post('/dashboard/update', [DashboardController::class, 'updateProfile'])->name('dashboard.updateProfile');
});

// Monitoring & Data → Kalender Timeline
Route::get('/timeline', function () {
    return view('timeline');
})->name('timeline');

Route::get('/monitoring-data', function () {
    return view('monitoring.index');
})->name('monitoring.data');

// HINDARI DUplikasi route
Route::get('/monitoring/data', function () {
    return view('monitoring.data');
})->name('monitoring.data2');

// Kalender
Route::get('/monitoring-kalender', function () {
    return view('monitoring-data');
})->name('monitoring.kalender');

// UPLOAD PROPOSAL
Route::get('/proposal/create', [ProposalController::class, 'create'])->name('proposal.create');
Route::post('/proposal/store', [ProposalController::class, 'store'])->name('proposal.store');
Route::get('/proposal', [ProposalController::class, 'index'])->name('proposal.index');
Route::get('/proposal/download/{id}', [ProposalController::class, 'download'])->name('proposal.download');

// PROFILE
Route::get('/profile', [App\Http\Controllers\ProfileController::class, 'show'])->name('profile.show');
Route::get('/profile/edit', [App\Http\Controllers\ProfileController::class, 'edit'])->name('profile.edit');
Route::post('/profile/update', [App\Http\Controllers\ProfileController::class, 'update'])->name('profile.update');

// PROPOSAL (Monitoring Status)
Route::get('/daftar-proposal', [ProposalController::class, 'index'])
    ->name('monitoring.proposalDikirim');

// VIEW STATIS — PASTIKAN FILE ADA DI /resources/views/proposal/
Route::get('/proposal-disetujui', function () {
    return view('proposal.proposal-disetujui');
})->name('monitoring.proposalDisetujui');

Route::get('/proposal-ditolak', function () {
    return view('proposal.proposal-ditolak');
})->name('monitoring.proposalDitolak');

Route::get('/proposal-perlu-direview', function () {
    return view('proposal.proposal-perlu-direview');
})->name('monitoring.proposalPerluDireview');

Route::get('/proposal-sedang-direview', function () {
    return view('proposal.proposal-sedang-direview');
})->name('monitoring.proposalSedangDireview');

Route::get('/proposal-selesai', function () {
    return view('proposal.proposal-selesai');
})->name('monitoring.reviewSelesai');

Route::get('/hasil-review', function () {
    return view('proposal.hasil-review');
})->name('monitoring.hasilRevisi');

Route::get('/proposal-direvisi', function () {
    return view('proposal.proposal-direvisi');
})->name('monitoring.proposalDirevisi');

// REVIEWER
Route::get('/reviewer/isi-review', function () {
    return view('reviewer.isi-review');
})->name('reviewer.isi-review');

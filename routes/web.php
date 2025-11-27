<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\ProposalController;

/*
|--------------------------------------------------------------------------
| HALAMAN AWAL
|--------------------------------------------------------------------------
*/
Route::get('/', function () {
    return view('auth.custom-login');
});

/*
|--------------------------------------------------------------------------
| LOGIN & LOGOUT
|--------------------------------------------------------------------------
*/
Route::get('/login', [AuthController::class, 'showLoginForm'])->name('login');
Route::post('/login', [AuthController::class, 'login'])->name('login.post');
Route::post('/logout', [AuthController::class, 'logout'])->name('logout');

Route::get('/password/reset', function () {
    return 'Fitur lupa password akan tersedia melalui sistem LDAP Universitas.';
})->name('password.request');

/*
|--------------------------------------------------------------------------
| DASHBOARD (SEMUA ROLE)
|--------------------------------------------------------------------------
*/
Route::middleware(['auth'])->group(function () {
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');
    Route::post('/dashboard/update', [DashboardController::class, 'updateProfile'])->name('dashboard.updateProfile');

    // Kalender — semua role boleh
    Route::get('/monitoring-kalender', fn() => view('monitoring-data'))->name('monitoring.kalender');
});

/*
|--------------------------------------------------------------------------
| ADMIN ONLY
|--------------------------------------------------------------------------
*/
Route::middleware(['auth', 'role:admin'])->group(function () {
    Route::get('/timeline', fn() => view('timeline'))->name('timeline');
    Route::get('/monitoring-data', fn() => view('monitoring.index'))->name('monitoring.data');
    Route::get('/monitoring/data', fn() => view('monitoring.data'))->name('monitoring.data2');
});

/*
|--------------------------------------------------------------------------
| PENGAJU ONLY (Upload Proposal)
|--------------------------------------------------------------------------
*/
Route::middleware(['auth', 'role:pengaju'])->group(function () {
    Route::get('/proposal/create', [ProposalController::class, 'create'])->name('proposal.create');
    Route::post('/proposal/store', [ProposalController::class, 'store'])->name('proposal.store');
});

/*
|--------------------------------------------------------------------------
| SEMUA ROLE BOLEH LIHAT LIST PROPOSAL
|--------------------------------------------------------------------------
*/
Route::middleware(['auth'])->group(function () {
    Route::get('/proposal', [ProposalController::class, 'index'])->name('proposal.index');
    Route::get('/proposal/download/{id}', [ProposalController::class, 'download'])->name('proposal.download');
    Route::get('/daftar-proposal', [ProposalController::class, 'index'])->name('monitoring.proposalDikirim');
});

/*
|--------------------------------------------------------------------------
| ADMIN + REVIEWER ONLY
|--------------------------------------------------------------------------
| Ini yang diblok untuk PENGAJU
*/
Route::middleware(['auth', 'role:admin,reviewer'])->group(function () {

    Route::get('/proposal-perlu-direview', fn() => view('proposal.proposal-perlu-direview'))
        ->name('monitoring.proposalPerluDireview');

    Route::get('/proposal-sedang-direview', fn() => view('proposal.proposal-sedang-direview'))
        ->name('monitoring.proposalSedangDireview');
});

/*
|--------------------------------------------------------------------------
| SEMUA ROLE (ADMIN • REVIEWER • PENGAJU)
|--------------------------------------------------------------------------
*/
Route::middleware(['auth'])->group(function () {

    Route::get('/proposal-disetujui', fn() => view('proposal.proposal-disetujui'))
        ->name('monitoring.proposalDisetujui');

    Route::get('/proposal-ditolak', fn() => view('proposal.proposal-ditolak'))
        ->name('monitoring.proposalDitolak');

    Route::get('/proposal-selesai', fn() => view('proposal.proposal-selesai'))
        ->name('monitoring.reviewSelesai');

    Route::get('/hasil-review', fn() => view('proposal.hasil-review'))
        ->name('monitoring.hasilRevisi');

    Route::get('/proposal-direvisi', fn() => view('proposal.proposal-direvisi'))
        ->name('monitoring.proposalDirevisi');
});

/*
|--------------------------------------------------------------------------
| REVIEWER ONLY
|--------------------------------------------------------------------------
*/
Route::middleware(['auth', 'role:reviewer'])->group(function () {
    Route::get('/reviewer/isi-review', fn() => view('reviewer.isi-review'))->name('reviewer.isi-review');
});

/*
|--------------------------------------------------------------------------
| PROFILE (SEMUA ROLE)
|--------------------------------------------------------------------------
*/
Route::middleware(['auth'])->group(function () {
    Route::get('/profile', [App\Http\Controllers\ProfileController::class, 'show'])->name('profile.show');
    Route::get('/profile/edit', [App\Http\Controllers\ProfileController::class, 'edit'])->name('profile.edit');
    Route::post('/profile/update', [App\Http\Controllers\ProfileController::class, 'update'])->name('profile.update');
});

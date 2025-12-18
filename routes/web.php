<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Http\Request;
use App\Models\User;

use App\Http\Controllers\AuthController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\ProposalController;
use App\Http\Controllers\ReviewerController;
use App\Http\Controllers\AdminController;
use App\Http\Controllers\NotificationController;
use App\Http\Controllers\CalendarController;
use App\Http\Controllers\ProposalReviewerController;

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
| DASHBOARD & KALENDER (SEMUA ROLE)
|--------------------------------------------------------------------------
*/
Route::middleware(['auth'])->group(function () {
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');
    Route::post('/dashboard/update', [DashboardController::class, 'updateProfile'])
        ->name('dashboard.updateProfile');

    Route::get('/monitoring-kalender', [CalendarController::class, 'index'])
        ->name('monitoring.kalender');
});

/*
|--------------------------------------------------------------------------
| ADMIN ONLY
|--------------------------------------------------------------------------
*/
Route::middleware(['auth', 'role:admin'])->group(function () {

    Route::get('/timeline', fn () => view('timeline'))->name('timeline');

    Route::get('/monitoring-data', fn () => view('monitoring.index'))
        ->name('monitoring.data');

    Route::get('/monitoring/data', fn () => view('monitoring.data'))
        ->name('monitoring.data2');

    Route::get('/admin/hasil-review', [AdminController::class, 'hasilReview'])
        ->name('admin.hasil-review');

    Route::post('/monitoring-kalender/periode', [CalendarController::class, 'updatePeriod'])
        ->name('monitoring.kalender.periode');

    Route::get('/admin/proposals/{id}/edit', [AdminController::class, 'editProposal'])
        ->name('admin.proposals.edit');

    Route::put('/admin/proposals/{id}', [AdminController::class, 'updateProposal'])
        ->name('admin.proposals.update');

    Route::get('/admin/calendar', [AdminController::class, 'calendar'])
        ->name('admin.calendar');
});

/*
|--------------------------------------------------------------------------
| PENGAJU ONLY
|--------------------------------------------------------------------------
*/
Route::middleware(['auth', 'role:pengaju'])->group(function () {
    Route::get('/proposal/create', [ProposalController::class, 'create'])
        ->name('proposal.create');

    Route::post('/proposal/store', [ProposalController::class, 'store'])
        ->name('proposal.store');
});

/*
|--------------------------------------------------------------------------
| SEMUA ROLE - LIST PROPOSAL
|--------------------------------------------------------------------------
*/
Route::middleware(['auth'])->group(function () {

    Route::get('/proposal', [ProposalController::class, 'index'])
        ->name('proposal.index');

    Route::get('/daftar-proposal', [ProposalController::class, 'index'])
        ->name('monitoring.proposalDikirim');

    Route::get('/proposal/download/{id}', [ProposalController::class, 'download'])
        ->name('proposal.download');

    Route::get('/proposal/{id}/tinjau', [ProposalController::class, 'tinjau'])
        ->name('proposal.tinjau');

    Route::get('/proposal/{id}/edit', [ProposalController::class, 'edit'])
        ->name('proposal.edit');

    Route::put('/proposal/{id}', [ProposalController::class, 'update'])
        ->name('proposal.update');
});

/*
|--------------------------------------------------------------------------
| ADMIN + REVIEWER
|--------------------------------------------------------------------------
*/
Route::middleware(['auth', 'role:admin,reviewer'])->group(function () {

    Route::get('/proposal-perlu-direview',
        [ProposalController::class, 'proposalPerluDireview'])
        ->name('monitoring.proposalPerluDireview');

    Route::get('/proposal-sedang-direview',
        [ProposalController::class, 'proposalSedangDireview'])
        ->name('monitoring.proposalSedangDireview');

    // ✅ INI YANG KURANG (route name harus sama dengan yang dipanggil di blade)
    // pakai {proposal} biar cocok dengan method moveToPerluDireview(Proposal $proposal)
    Route::patch('/proposal/{proposal}/move-to-perlu-direview',
        [ProposalController::class, 'moveToPerluDireview'])
        ->name('proposal.moveToPerluDireview');

    Route::post('/admin/proposal/{id}/assign-reviewer',
    [AdminController::class, 'assignReviewer']
)->name('proposal.assignReviewer');
});

/*
|--------------------------------------------------------------------------
| REVIEWER ONLY
|--------------------------------------------------------------------------
*/
Route::middleware(['auth', 'role:reviewer'])->group(function () {

    Route::get('/reviewer/isi-review/{id}',
        [ReviewerController::class, 'isiReview'])
        ->name('reviewer.isi-review');

    Route::post('/reviewer/isi-review/{id}',
        [ReviewerController::class, 'submitReview'])
        ->name('reviewer.submitReview');

    Route::post('/review/{id}/simpan',
        [ReviewerController::class, 'submitReview'])
        ->name('review.simpan');
});

/*
|--------------------------------------------------------------------------
| STATUS PROPOSAL (SEMUA ROLE)
|--------------------------------------------------------------------------
*/
Route::middleware(['auth'])->group(function () {

    Route::get('/proposal-disetujui', fn () => view('proposal.proposal-disetujui'))
        ->name('monitoring.proposalDisetujui');

    Route::get('/proposal-ditolak', fn () => view('proposal.proposal-ditolak'))
        ->name('monitoring.proposalDitolak');

    Route::get('/proposal-selesai',
        [ProposalController::class, 'reviewSelesai'])
        ->name('monitoring.reviewSelesai');

    Route::get('/proposal-direvisi', fn () => view('proposal.proposal-direvisi'))
        ->name('monitoring.proposalDirevisi');

    Route::get('/hasil-review', fn () => view('proposal.hasil-review'))
        ->name('monitoring.hasilRevisi');
});

/*
|--------------------------------------------------------------------------
| PROFILE
|--------------------------------------------------------------------------
*/
Route::middleware(['auth'])->group(function () {

    Route::get('/profile', [App\Http\Controllers\ProfileController::class, 'show'])
        ->name('profile.show');

    Route::get('/profile/edit', [App\Http\Controllers\ProfileController::class, 'edit'])
        ->name('profile.edit');

    Route::post('/profile/update', [App\Http\Controllers\ProfileController::class, 'update'])
        ->name('profile.update');
});

/*
|--------------------------------------------------------------------------
| NOTIFIKASI
|--------------------------------------------------------------------------
*/
Route::middleware(['auth'])->group(function () {

    Route::get('/notifications/fetch',
        [NotificationController::class, 'fetch'])
        ->name('notifications.fetch');

    Route::post('/notifications/mark-all-read',
        [NotificationController::class, 'markAllAsRead'])
        ->name('notifications.markAllAsRead');

    Route::post('/notifications/send-all',
        [NotificationController::class, 'sendToAll'])
        ->name('notifications.sendAll');

    Route::post('/notifications/send-user',
        [NotificationController::class, 'sendToUser'])
        ->name('notifications.sendUser');
});

/*
|--------------------------------------------------------------------------
| REVIEWER SEARCH & ASSIGN (FIX DOUBLE)
|--------------------------------------------------------------------------
*/
Route::middleware(['auth', 'role:admin'])->group(function () {

    Route::get('/search-reviewer',
        [ProposalReviewerController::class, 'search'])
        ->name('reviewer.search');

    Route::post('/proposal/{proposal}/assign-reviewer',
        [ProposalReviewerController::class, 'assign'])
        ->name('proposal.assignReviewer');
});


Route::middleware(['auth', 'role:admin'])->group(function () {
    Route::get('/admin/reviewer', [ReviewerController::class, 'index'])
        ->name('admin.reviewer.index');

    Route::post('/admin/reviewer/{user}', [ReviewerController::class, 'setReviewer'])
        ->name('admin.reviewer.set');

    Route::get('/admin/search-reviewer', [ReviewerController::class, 'searchReviewer'])
        ->middleware('auth')
        ->name('admin.searchReviewer');

    Route::post('/proposal/{proposal}/assign-reviewer', [ProposalController::class, 'assignReviewer'])->name('proposal.assignReviewer');
});

Route::put('/proposal/{proposal}/approve',
    [ProposalController::class, 'approveProposal']
)->name('proposal.approve');

Route::get('/review/{review}/pdf', [App\Http\Controllers\ProposalController::class, 'downloadReviewPdf'])
    ->name('review.pdf');

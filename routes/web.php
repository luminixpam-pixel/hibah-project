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
use App\Http\Controllers\LaporanKemajuanController;
use App\Http\Controllers\ProfileController;

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
| ROUTE DENGAN MIDDLEWARE AUTH
|--------------------------------------------------------------------------
*/
Route::middleware(['auth'])->group(function () {

    /* --- DASHBOARD & KALENDER --- */
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');
    Route::post('/dashboard/update', [DashboardController::class, 'updateProfile'])->name('dashboard.updateProfile');
    Route::get('/monitoring-kalender', [CalendarController::class, 'index'])->name('monitoring.kalender');

    /* --- PROFILE --- */
    Route::get('/profile', [ProfileController::class, 'show'])->name('profile.show');
    Route::get('/profile/edit', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::post('/profile/update', [ProfileController::class, 'update'])->name('profile.update');

    /* --- LAPORAN KEMAJUAN (SESUAI FIGMA) --- */
    // Menampilkan halaman daftar proposal untuk upload laporan
    Route::get('/laporan-kemajuan', [LaporanKemajuanController::class, 'index'])->name('laporan.kemajuan.index');
    // Proses upload file laporan
    Route::post('/laporan-kemajuan', [LaporanKemajuanController::class, 'store'])->name('laporan.kemajuan.store');
    // Tambahan: Download file laporan yang sudah diunggah
    Route::get('/laporan-kemajuan/download/{id}', [LaporanKemajuanController::class, 'downloadLaporan'])->name('laporan.kemajuan.download');

    /* --- LIST & ACTION PROPOSAL --- */
    Route::get('/proposal', [ProposalController::class, 'index'])->name('proposal.index');
    Route::get('/daftar-proposal', [ProposalController::class, 'index'])->name('monitoring.proposalDikirim');
    Route::get('/proposal/download/{id}', [ProposalController::class, 'download'])->name('proposal.download');
    Route::get('/proposal/{id}/tinjau', [ProposalController::class, 'tinjau'])->name('proposal.tinjau');
    Route::get('/proposal/{id}/edit', [ProposalController::class, 'edit'])->name('proposal.edit');
    Route::put('/proposal/{id}', [ProposalController::class, 'update'])->name('proposal.update');

    /* --- STATUS MONITORING --- */
    Route::get('/proposal-disetujui', [ProposalController::class, 'proposalDisetujui'])->name('monitoring.proposalDisetujui');
    Route::get('/proposal-ditolak', [ProposalController::class, 'proposalDitolak'])->name('monitoring.proposalDitolak');
    Route::get('/proposal-selesai', [ProposalController::class, 'reviewSelesai'])->name('monitoring.reviewSelesai');
    Route::get('/proposal-direvisi', [ProposalController::class, 'proposalDirevisi'])->name('monitoring.proposalDirevisi');
    Route::get('/hasil-review', [ProposalController::class, 'hasilRevisi'])->name('monitoring.hasilRevisi');
    Route::get('/review/{review}/pdf', [ProposalController::class, 'downloadReviewPdf'])->name('review.pdf');

    /* --- NOTIFIKASI --- */
    Route::get('/notifications/fetch', [NotificationController::class, 'fetch'])->name('notifications.fetch');
    Route::post('/notifications/mark-all-read', [NotificationController::class, 'markAllAsRead'])->name('notifications.markAllAsRead');
    Route::post('/notifications/send-all', [NotificationController::class, 'sendToAll'])->name('notifications.sendAll');
    Route::post('/notifications/send-user', [NotificationController::class, 'sendToUser'])->name('notifications.sendUser');

    /*
    |--------------------------------------------------------------------------
    | ROLE: ADMIN ONLY
    |--------------------------------------------------------------------------
    */
    Route::middleware(['role:admin'])->group(function () {
        Route::get('/timeline', fn () => view('timeline'))->name('timeline');
        Route::get('/monitoring-data', fn () => view('monitoring.index'))->name('monitoring.data');
        Route::get('/admin/hasil-review', [AdminController::class, 'hasilReview'])->name('admin.hasil-review');
        Route::post('/monitoring-kalender/periode', [CalendarController::class, 'updatePeriod'])->name('monitoring.kalender.periode');

        Route::get('/admin/proposals/{id}/edit', [AdminController::class, 'editProposal'])->name('admin.proposals.edit');
        Route::put('/admin/proposals/{id}', [AdminController::class, 'updateProposal'])->name('admin.proposals.update');
        Route::get('/admin/calendar', [AdminController::class, 'calendar'])->name('admin.calendar');

        Route::put('/proposal/{proposal}/approve', [ProposalController::class, 'approveProposal'])->name('proposal.approve');
        Route::put('/proposal/{proposal}/reject', [ProposalController::class, 'rejectProposal'])->name('proposal.reject');

        // Reviewer Management
        Route::get('/admin/reviewer', [ReviewerController::class, 'index'])->name('admin.reviewer.index');
        Route::post('/admin/reviewer/{user}', [ReviewerController::class, 'setReviewer'])->name('admin.reviewer.set');
        Route::post('/admin/reviewer/{user}/remove', [ReviewerController::class, 'removeReviewer'])->name('reviewer.remove');
        Route::get('/admin/search-reviewer', [ReviewerController::class, 'searchReviewer'])->name('admin.searchReviewer');

        Route::post('/proposal/{proposal}/assign-reviewer', [ProposalReviewerController::class, 'assign'])->name('proposal.assignReviewer');
    });

    /*
    |--------------------------------------------------------------------------
    | ROLE: PENGAJU ONLY
    |--------------------------------------------------------------------------
    */
    Route::middleware(['role:pengaju'])->group(function () {
        Route::get('/proposal/create', [ProposalController::class, 'create'])->name('proposal.create');
        Route::post('/proposal/store', [ProposalController::class, 'store'])->name('proposal.store');
    });

    /*
    |--------------------------------------------------------------------------
    | ROLE: ADMIN + REVIEWER
    |--------------------------------------------------------------------------
    */
    Route::middleware(['role:admin,reviewer'])->group(function () {
        Route::get('/proposal-perlu-direview', [ProposalController::class, 'proposalPerluDireview'])->name('monitoring.proposalPerluDireview');
        Route::get('/proposal-sedang-direview', [ProposalController::class, 'proposalSedangDireview'])->name('monitoring.proposalSedangDireview');
        Route::patch('/proposal/{proposal}/move-to-perlu-direview', [ProposalController::class, 'moveToPerluDireview'])->name('proposal.moveToPerluDireview');
    });

    /*
    |--------------------------------------------------------------------------
    | ROLE: REVIEWER ONLY
    |--------------------------------------------------------------------------
    */
    Route::middleware(['role:reviewer'])->group(function () {
        Route::get('/reviewer/isi-review/{id}', [ReviewerController::class, 'isiReview'])->name('reviewer.isi-review');
        Route::post('/reviewer/isi-review/{id}', [ReviewerController::class, 'submitReview'])->name('reviewer.submitReview');
        Route::post('/review/{id}/simpan', [ReviewerController::class, 'submitReview'])->name('review.simpan');
    });
});

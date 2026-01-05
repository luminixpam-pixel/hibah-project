<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\{
    AuthController, DashboardController, ProposalController,
    ReviewerController, AdminController, NotificationController,
    CalendarController, ProposalReviewerController, LaporanKemajuanController,
    ProfileController, DokumenResmiController, AdminDocumentController
};

/*
|--------------------------------------------------------------------------
| HALAMAN AWAL & AUTH
|--------------------------------------------------------------------------
*/
Route::get('/', fn() => view('auth.custom-login'));

Route::controller(AuthController::class)->group(function () {
    Route::get('/login', 'showLoginForm')->name('login');
    Route::post('/login', 'login')->name('login.post');
    Route::post('/logout', 'logout')->name('logout');
});

Route::get('/password/reset', fn() => 'Fitur LDAP LDAP LDAP.')->name('password.request');

/*
|--------------------------------------------------------------------------
| ROUTE DENGAN MIDDLEWARE AUTH (SEMUA USER LOGIN)
|--------------------------------------------------------------------------
*/
Route::middleware(['auth'])->group(function () {

    /* --- DASHBOARD & PROFILE --- */
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');
    Route::post('/dashboard/update', [DashboardController::class, 'updateProfile'])->name('dashboard.updateProfile');
    Route::get('/profile', [ProfileController::class, 'show'])->name('profile.show');
    Route::get('/profile/edit', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::post('/profile/update', [ProfileController::class, 'update'])->name('profile.update');

    /* --- MONITORING & KALENDER --- */
    Route::get('/monitoring-kalender', [CalendarController::class, 'index'])->name('monitoring.kalender');
    Route::get('/pengumuman-pendanaan', [ProposalController::class, 'pengumumanPendanaan'])->name('proposal.pengumuman');

    /* --- PROPOSAL ACTIONS (GENERAL) --- */
    Route::controller(ProposalController::class)->group(function () {
        Route::get('/proposal', 'index')->name('proposal.index');
        Route::get('/daftar-proposal', 'index')->name('monitoring.proposalDikirim');
        Route::get('/proposal/download/{id}', 'download')->name('proposal.download');
        Route::get('/proposal/{id}/edit', 'edit')->name('proposal.edit');
        Route::put('/proposal/{id}', 'update')->name('proposal.update');
        Route::delete('/proposal/{id}', 'destroy')->name('proposal.destroy');
        Route::get('/proposal/{id}/tinjau', 'tinjau')->name('proposal.tinjau');
        Route::patch('/proposal/{id}/keputusan', 'keputusan')->name('proposal.keputusan');

        // Status Monitoring
        Route::get('/proposal-disetujui', 'proposalDisetujui')->name('monitoring.proposalDisetujui');
        Route::get('/proposal-ditolak', 'proposalDitolak')->name('monitoring.proposalDitolak');
        Route::get('/proposal-selesai', 'reviewSelesai')->name('monitoring.reviewSelesai');
        Route::get('/proposal-direvisi', 'proposalDirevisi')->name('monitoring.proposalDirevisi');
        Route::get('/hasil-review', 'hasilRevisi')->name('monitoring.hasilRevisi');
        Route::get('/review/{review}/pdf', 'downloadReviewPdf')->name('review.pdf');
    });

    /* --- LAPORAN KEMAJUAN --- */
    Route::controller(LaporanKemajuanController::class)->group(function () {
        Route::get('/laporan-kemajuan', 'index')->name('laporan.kemajuan.index');
        Route::post('/laporan-kemajuan', 'store')->name('laporan.kemajuan.store');
        Route::get('/laporan-kemajuan/download/{id}', 'downloadLaporan')->name('laporan.kemajuan.download');
    });

    /* --- DOKUMEN (USER VIEW) --- */
    Route::get('/dokumen', [AdminDocumentController::class, 'userView'])->name('dokumen.user');
    Route::get('/dokumen/download/{id}', [AdminDocumentController::class, 'download'])->name('dokumen.download');
    Route::get('/dokumen-resmi/{id}/download', [DokumenResmiController::class, 'download'])->name('dokumen.download_resmi');

    /* --- NOTIFIKASI --- */
    Route::controller(NotificationController::class)->group(function () {
        Route::get('/notifications/fetch', 'fetch')->name('notifications.fetch');
        Route::post('/notifications/mark-all-read', 'markAllAsRead')->name('notifications.markAllAsRead');
    });

    /*
    |--------------------------------------------------------------------------
    | ROLE: ADMIN ONLY
    |--------------------------------------------------------------------------
    */
    Route::middleware(['role:admin'])->group(function () {
        // Produktivitas & Riwayat Dosen
        Route::get('/riwayat-dosen', [DashboardController::class, 'riwayatDosen'])->name('admin.riwayatDosen');
        Route::get('/riwayat-dosen/{id}', [DashboardController::class, 'detailDosen'])->name('admin.dosen.detail');

        // Admin Management
        Route::get('/timeline', fn() => view('timeline'))->name('timeline');
        Route::get('/monitoring-data', fn() => view('monitoring.index'))->name('monitoring.data');
        Route::get('/admin/hasil-review', [AdminController::class, 'hasilReview'])->name('admin.hasil-review');
        Route::post('/monitoring-kalender/periode', [CalendarController::class, 'updatePeriod'])->name('monitoring.kalender.periode');

        // Proposal Admin Control
        Route::put('/proposal/{proposal}/approve', [ProposalController::class, 'approveProposal'])->name('proposal.approve');
        Route::put('/proposal/{proposal}/reject', [ProposalController::class, 'rejectProposal'])->name('proposal.reject');

        // Reviewer Management
        Route::controller(ReviewerController::class)->group(function () {
            Route::get('/admin/reviewer', 'index')->name('admin.reviewer.index');
            Route::post('/admin/reviewer/{user}', 'setReviewer')->name('admin.reviewer.set');
            Route::post('/admin/reviewer/{user}/remove', 'removeReviewer')->name('reviewer.remove');
            Route::get('/admin/search-reviewer', 'searchReviewer')->name('admin.searchReviewer');
        });

        Route::post('/proposal/{proposal}/assign-reviewer', [ProposalReviewerController::class, 'assign'])->name('proposal.assignReviewer');

        // Admin Documents
        Route::get('/admin/dokumen', [AdminDocumentController::class, 'index'])->name('admin.dokumen.index');
        Route::post('/admin/dokumen', [AdminDocumentController::class, 'store'])->name('admin.dokumen.store');
    });

    /*
    |--------------------------------------------------------------------------
    | ROLE: PENGAJU ONLY
    |--------------------------------------------------------------------------
    */
    Route::middleware(['role:pengaju,reviewer'])->group(function () {
    Route::get('/proposal/create', [ProposalController::class, 'create'])->name('proposal.create');
    Route::post('/proposal/store', [ProposalController::class, 'store'])->name('proposal.store');
});
    /*
    |--------------------------------------------------------------------------
    | ROLE: REVIEWER / ADMIN + REVIEWER
    |--------------------------------------------------------------------------
    */
    Route::middleware(['role:admin,reviewer'])->group(function () {
        Route::get('/proposal-perlu-direview', [ProposalController::class, 'proposalPerluDireview'])->name('monitoring.proposalPerluDireview');
        Route::get('/proposal-sedang-direview', [ProposalController::class, 'proposalSedangDireview'])->name('monitoring.proposalSedangDireview');
    });

    Route::middleware(['role:reviewer'])->group(function () {
    Route::get('/reviewer/isi-review/{id}', [ReviewerController::class, 'isiReview'])->name('reviewer.isi-review');

    // Gunakan nama 'review.simpan' agar cocok dengan error yang tadi muncul
    Route::post('/reviewer/isi-review/{id}', [ReviewerController::class, 'submitReview'])->name('review.simpan');
});
   Route::patch('/proposal/{id}/set-review', [ProposalController::class, 'setReview'])->name('proposal.set-review');

   Route::get('/review/{review}/pdf', [ProposalController::class, 'downloadReviewPdf'])->name('review.pdf');
});



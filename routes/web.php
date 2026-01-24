<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Auth\AuthController;
use App\Http\Controllers\{
    DashboardController, ProposalController, ReviewerController,
    AdminController, NotificationController, CalendarController,
    ProposalReviewerController, LaporanKemajuanController, ProfileController,
    AdminDocumentController, TemplateController, LaporanAkhirController
};

/* --- PUBLIC ROUTES --- */
Route::get('/', function () {
    return auth()->check() ? redirect()->route('dashboard') : redirect()->route('login');
});

Route::get('/login', [AuthController::class, 'showLoginForm'])->name('login');
Route::post('/login', [AuthController::class, 'login']);

/* --- AUTH ROUTES (ALL USERS) --- */
Route::middleware('auth')->group(function () {
    Route::post('/logout', [AuthController::class, 'logout'])->name('logout');

    // Dashboard & Profile
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');
    Route::post('/dashboard/update', [DashboardController::class, 'updateProfile'])->name('dashboard.updateProfile');
    Route::get('/profile', [ProfileController::class, 'show'])->name('profile.show');
    Route::get('/profile/edit', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::post('/profile/update', [ProfileController::class, 'update'])->name('profile.update');

    // Monitoring & Info Umum
    Route::get('/monitoring-kalender', [CalendarController::class, 'index'])->name('monitoring.kalender');
    Route::get('/pengumuman-pendanaan', [ProposalController::class, 'pengumumanPendanaan'])->name('proposal.pengumuman');
    Route::get('/cek-kuota-dosen', [ProposalController::class, 'cekKuota'])->name('dosen.cek-kuota');

    // Proposal Actions (General)
Route::controller(ProposalController::class)->group(function () {
    // 1. Menampilkan Daftar (GET)
    Route::get('/proposal', 'index')->name('proposal.index');
    Route::get('/daftar-proposal', 'index')->name('monitoring.proposalDikirim');

    // 2. Menampilkan Form Edit (HARUS GET)
    Route::get('/proposal/{id}/edit', 'edit')->name('proposal.edit');

    // 3. Proses Update Data (PUT)
    Route::put('/proposal/{id}', 'update')->name('proposal.update');

    // 4. Fitur Lainnya
    Route::get('/proposal/download/{id}', 'download')->name('proposal.download');
    Route::delete('/proposal/{id}', 'destroy')->name('proposal.destroy');
    Route::get('/proposal/{id}/tinjau', 'tinjau')->name('proposal.tinjau');
    Route::patch('/proposal/{id}/keputusan', 'keputusan')->name('proposal.keputusan');

    // Monitoring Statuses (GET)
    Route::get('/proposal-disetujui', 'proposalDisetujui')->name('monitoring.proposalDisetujui');
    Route::get('/proposal-ditolak', 'proposalDitolak')->name('monitoring.proposalDitolak');
    Route::get('/proposal-selesai', 'reviewSelesai')->name('monitoring.reviewSelesai');
    Route::get('/proposal-direvisi', 'proposalDirevisi')->name('monitoring.proposalDirevisi');
    Route::get('/hasil-review', 'hasilRevisi')->name('monitoring.hasilRevisi');
    Route::get('/review/{id}/pdf', 'downloadReviewPdf')->name('review.pdf');
});

    // Laporan (Kemajuan & Akhir)
    Route::get('/laporan-kemajuan', [LaporanKemajuanController::class, 'index'])->name('laporan.kemajuan.index');
    Route::post('/laporan-kemajuan', [LaporanKemajuanController::class, 'store'])->name('laporan.kemajuan.store');
    Route::get('/laporan-kemajuan/download/{id}', [LaporanKemajuanController::class, 'downloadLaporan'])->name('laporan.kemajuan.download');

    Route::get('/laporan-akhir', [LaporanAkhirController::class, 'index'])->name('laporan.akhir.index');
    Route::post('/laporan-akhir/store', [LaporanAkhirController::class, 'store'])->name('laporan.akhir.store');
    Route::get('/laporan-akhir/download/{id}', [LaporanAkhirController::class, 'download'])->name('laporan.akhir.download');

    // Dokumen (User View & Download)
    Route::get('/dokumen', [AdminDocumentController::class, 'userView'])->name('dokumen.user');
    Route::get('/dokumen/download/{id}', [AdminDocumentController::class, 'download'])->name('dokumen.download');

    // Notifikasi
    Route::controller(NotificationController::class)->group(function () {
        Route::get('/notifications/fetch', 'fetch')->name('notifications.fetch');
        Route::get('/notifications/count', 'count')->name('notifications.count');
        Route::post('/notifications/mark-all-read', 'markAllAsRead')->name('notifications.markAllAsRead');

        // ✅ TAMBAHAN: endpoint khusus AJAX (bypass CSRF) + name unik (biar gak ketabrak route dobel)
        Route::post('/notifications/mark-all-read-ajax', 'markAllAsRead')
            ->withoutMiddleware([\Illuminate\Foundation\Http\Middleware\VerifyCsrfToken::class])
            ->name('notifications.markAllAsReadAjax');
    });
});

/* --- ROLE: ADMIN ONLY --- */
Route::middleware(['auth', 'role:admin'])->group(function () {
    // User Management
    Route::get('/admin/users', [AdminController::class, 'indexUsers'])->name('admin.users.index');
    Route::put('/admin/users/{id}', [AdminController::class, 'updateUser'])->name('admin.users.update');
    Route::post('/admin/update-role/{id}', [DashboardController::class, 'updateRole'])->name('admin.updateRole');

    // Monitoring Admin
    Route::get('/riwayat-dosen', [DashboardController::class, 'riwayatDosen'])->name('admin.riwayatDosen');
    Route::get('/riwayat-dosen/{id}', [DashboardController::class, 'detailDosen'])->name('admin.dosen.detail');
    Route::get('/admin/hasil-review', [AdminController::class, 'hasilReview'])->name('admin.hasil-review');
    Route::post('/monitoring-kalender/periode', [CalendarController::class, 'updatePeriod'])->name('monitoring.kalender.periode');

    // Reviewer Management
    Route::controller(ReviewerController::class)->group(function () {
        Route::get('/admin/reviewer', 'index')->name('admin.reviewer.index');
        Route::post('/admin/reviewer/{user}', 'setReviewer')->name('admin.reviewer.set');
        Route::post('/admin/reviewer/{user}/remove', 'removeReviewer')->name('reviewer.remove');
    });

    // Dokumen Management
    // Cari bagian Dokumen Management di dalam middleware admin dan ganti dengan ini:
    Route::get('/admin/dokumen', [AdminDocumentController::class, 'index'])->name('admin.dokumen.index');
    Route::post('/admin/dokumen/store', [AdminDocumentController::class, 'store'])->name('admin.dokumen.store');
    Route::patch('/admin/dokumen/toggle/{id}', [AdminDocumentController::class, 'toggleVisibility'])->name('admin.dokumen.toggle');
    Route::delete('/admin/dokumen/{id}', [AdminDocumentController::class, 'destroy'])->name('admin.dokumen.destroy');
    // Template Management
    Route::post('/admin/template/upload', [TemplateController::class, 'store'])->name('admin.template.upload');
    Route::delete('/admin/template/{id}', [TemplateController::class, 'destroy'])->name('admin.template.destroy');

    // Proposal Assignments
    Route::post('/proposal/{id}/assign-reviewer', [ProposalController::class, 'assignReviewer'])->name('proposal.assignReviewer');
    Route::patch('/proposal/{id}/set-review', [ProposalController::class, 'setReview'])->name('proposal.set-review');
});

/* --- ROLE: PENGAJU --- */
Route::middleware(['auth', 'role:pengaju,reviewer'])->group(function () {
    Route::get('/proposal/create', [ProposalController::class, 'create'])->name('proposal.create');
    Route::post('/proposal/store', [ProposalController::class, 'store'])->name('proposal.store');
});

/* --- ROLE: REVIEWER --- */
Route::middleware(['auth', 'role:reviewer'])->group(function () {
    Route::get('/reviewer/isi-review/{id}', [ReviewerController::class, 'isiReview'])->name('reviewer.isi-review');
    Route::post('/reviewer/isi-review/{id}', [ReviewerController::class, 'submitReview'])->name('review.simpan');
});

/* --- ROLE: ADMIN & REVIEWER (SHARED MONITORING) --- */
Route::middleware(['auth', 'role:admin,reviewer'])->group(function () {
    Route::get('/proposal-perlu-direview', [ProposalController::class, 'proposalPerluDireview'])->name('monitoring.proposalPerluDireview');
    Route::get('/proposal-sedang-direview', [ProposalController::class, 'proposalSedangDireview'])->name('monitoring.proposalSedangDireview');
});

/* --- NOTIFIKASI --- */
Route::controller(NotificationController::class)->group(function () {
    Route::get('/notifications/fetch', 'fetch')->name('notifications.fetch');
    Route::get('/notifications/count', 'count')->name('notifications.count');

    // TAMBAHKAN BARIS INI (yang menyebabkan error)
    Route::get('/notifications/deadline-check', 'deadlineCheck')->name('notifications.deadlineCheck');

    Route::post('/notifications/mark-all-read', 'markAllAsRead')->name('notifications.markAllAsRead');
});

Route::middleware(['auth'])->group(function () {

    // --- FITUR ADMIN (Manajemen User menjadi Reviewer) ---
    Route::prefix('admin')->group(function () {
        Route::get('/reviewer', [ReviewerController::class, 'index'])->name('admin.reviewer.index');
        Route::get('/search-reviewer', [ReviewerController::class, 'searchReviewer'])->name('admin.reviewer.search');
        Route::post('/reviewer/{user}', [ReviewerController::class, 'setReviewer'])->name('admin.reviewer.set');
        Route::post('/reviewer/{user}/remove', [ReviewerController::class, 'removeReviewer'])->name('admin.reviewer.remove');
    });

    // --- FITUR REVIEWER (Proses Penilaian Proposal) ---
    Route::get('/proposal-perlu-direview', [ReviewerController::class, 'index'])->name('reviewer.index');
    Route::get('/review/{id}/isi', [ReviewerController::class, 'isiReview'])->name('reviewer.isiReview');
    Route::post('/review/{id}/submit', [ReviewerController::class, 'submitReview'])->name('reviewer.submitReview');
});


    /*
    |--------------------------------------------------------------------------
    | NOTIFICATION SYSTEM (Bell & Popups)
    |--------------------------------------------------------------------------
    */

Route::middleware(['auth'])->group(function () {

    // Grouping khusus notifikasi
    Route::prefix('notifications')->name('notifications.')->group(function () {

        // --- Fungsi Dasar (AJAX Fetch & Mark Read) ---
        Route::get('/fetch', [NotificationController::class, 'fetch'])->name('fetch');
        Route::get('/count', [NotificationController::class, 'count'])->name('count');
        Route::post('/mark-all-read', [NotificationController::class, 'markAllAsRead'])->name('markAllAsRead');

        // --- Fungsi Deadline (Khusus Reviewer) ---
        Route::get('/deadline-check', [NotificationController::class, 'deadlineCheck'])->name('deadlineCheck');

        // --- Fungsi Admin (Kirim Notifikasi Manual) ---
        Route::post('/send-to-all', [NotificationController::class, 'sendToAll'])->name('sendToAll');
        Route::post('/send-to-user', [NotificationController::class, 'sendToUser'])->name('sendToUser');
    });

});

Route::middleware(['auth'])->group(function () {

    /*
    |--------------------------------------------------------------------------
    | ADMIN: PROPOSAL REVIEWER ASSIGNMENT
    |--------------------------------------------------------------------------
    */
    Route::prefix('admin/proposal')->group(function () {

        // Rute untuk mencari reviewer via AJAX (Autocomplete)
        // URL: /admin/proposal/search-reviewer?q=nama
        Route::get('/search-reviewer', [ProposalReviewerController::class, 'search'])->name('admin.proposal.search-reviewer');

        // Rute untuk submit penugasan 2 reviewer dan tenggat waktu
        // URL: /admin/proposal/{id}/assign
        Route::post('/{id}/assign', [ProposalReviewerController::class, 'assign'])->name('admin.proposal.assign');

    });

});

Route::post('/mark-as-read', [NotificationController::class, 'markAllAsRead'])->name('notifications.markAllAsRead');

// Di dalam routes/web.php, pastikan ada grup monitoring seperti ini:
Route::middleware(['auth'])->group(function () {
    Route::prefix('monitoring')->name('monitoring.')->group(function () {
        // Route yang menyebabkan error
        Route::get('/perlu-direview', [ProposalController::class, 'perluDireview'])->name('proposalPerluDireview');

        // Daftarkan juga route monitoring lainnya agar tidak error selanjutnya
        Route::get('/dikirim', [ProposalController::class, 'dikirim'])->name('proposalDikirim');
        Route::get('/sedang-direview', [ProposalController::class, 'sedangDireview'])->name('proposalSedangDireview');
        Route::get('/disetujui', [ProposalController::class, 'disetujui'])->name('proposalDisetujui');
        Route::get('/ditolak', [ProposalController::class, 'ditolak'])->name('proposalDitolak');
    });
});

Route::middleware(['auth', 'admin'])->group(function () {
    // ... route admin lainnya ...

    // Tambahkan ini
    Route::post('/admin/reviewer/remove/{id}', [ReviewerController::class, 'remove'])->name('reviewer.remove');

    // Pastikan route 'set' juga ada karena dipanggil di baris 102 pada error log Anda
    Route::post('/admin/reviewer/set/{id}', [ReviewerController::class, 'set'])->name('admin.reviewer.set');
});

Route::middleware(['auth'])->group(function () {
    // Tambahkan ini jika belum ada
    Route::put('/user/profile/{user}', [AuthController::class, 'update'])->name('admin.user.update');
    Route::put('/user/profile/{user}', [App\Http\Controllers\Auth\AuthController::class, 'update'])->name('admin.user.update');
});

Route::get('/api/notifications', function() {
    // Gunakan id user untuk filter
    $userId = auth()->user()->id;

    return \App\Models\Notification::where('user_id', $userId)
        ->latest()
        ->limit(10)
        ->get()
        ->map(function($n) {
            return [
                'title'    => $n->title,
                'message'  => $n->message,
                'is_read'  => (bool)$n->is_read,
                'time_ago' => $n->created_at->diffForHumans()
            ];
        });
})->middleware('auth');

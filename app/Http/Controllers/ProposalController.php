<?php

namespace App\Http\Controllers;

use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use App\Models\Proposal;
use App\Models\User;
use App\Models\Review;
use App\Models\ActivityLog;
use App\Models\Fakultas;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\DB;
use App\Helpers\NotificationHelper;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;

class ProposalController extends Controller
{
    /**
     * Helper internal untuk mencatat log aktivitas pengguna
     */
    private function logActivity($activity)
    {
        ActivityLog::create([
          'user_id' => auth()->user()->id,
            'activity' => $activity,
            'ip_address' => request()->ip(),
            'user_agent' => request()->userAgent(),
        ]);
    }

    /**
     * Menampilkan daftar proposal & Dropdown Fakultas
     */
public function index(Request $request)
{
    $tahun = $request->get('tahun', date('Y'));
    $user = auth()->user();
    $role = $user->role;

    $list_fakultas = \App\Models\Fakultas::all();
    $all_dosen = \App\Models\User::whereIn('role', ['pengaju', 'reviewer'])->get();

    // --- LOGIKA MASA HIBAH ---
    $now = now();
    $isMasaHibah = \Illuminate\Support\Facades\DB::table('hibah_periods')
        ->whereDate('start_date', '<=', $now)
        ->whereDate('end_date', '>=', $now)
        ->exists();
    // -------------------------

    $query = \App\Models\Proposal::with(['fakultas', 'user'])
        ->whereYear('created_at', $tahun);

    if ($role === 'pengaju' || $role === 'reviewer') {
        $query->where('user_id', $user->id);
    }

    $proposals = $query->latest()->get();

    // Pastikan semua variabel dipanggil dengan benar di compact()
    return view('proposal.daftar_proposal', compact(
        'proposals',
        'tahun',
        'role',
        'list_fakultas',
        'all_dosen',
        'isMasaHibah'
    ));
}
/**
     * Menyimpan proposal baru dengan Validasi Kuota 3 Proposal
     */
    public function store(Request $request)
    {
        // 1. Proteksi Role
        if (!in_array(auth()->user()->role, ['pengaju', 'reviewer'])) {
            abort(403, 'Anda tidak memiliki izin untuk mengunggah proposal.');
        }

        $user = auth()->user();

        // 2. Kumpulkan semua nama yang terlibat (Ketua + Anggota)
        $anggotaInput = $request->input('anggota', []);
        $semuaPeserta = array_filter($anggotaInput, fn($v) => trim((string)$v) !== '');
        $semuaPeserta[] = $request->nama_ketua;
        $semuaPeserta = array_unique($semuaPeserta);

        // 3. Cek Batas Maksimal 3 Proposal untuk SETIAP orang yang terlibat
      $ketua = $request->nama_ketua;
        $anggotaInput = $request->anggota ?? [];
        $semuaDosenTerlibat = array_merge([$ketua], $anggotaInput);

        foreach ($semuaDosenTerlibat as $nama) {
            if (!$nama) continue;

            $countKetua = Proposal::where('nama_ketua', $nama)->count();
            $countAnggota = Proposal::whereJsonContains('anggota', $nama)->count();
            $totalPartisipasi = $countKetua + $countAnggota;

            if ($totalPartisipasi >= 3) {
                return redirect()->back()
                    ->withInput()
                    ->with('error', "Dosen {$nama} sudah terdaftar di 3 proposal. Batas maksimal adalah 3.");
            }
        }

        // 4. Validasi Form
        $request->validate([
            'judul'           => 'required|string|max:255',
            'nama_ketua'      => 'required|string|max:255',
            'fakultas_prodi'  => 'required|exists:fakultas,id',
            'biaya'           => 'required',
            'anggota'         => 'nullable|array',
            'file'            => 'required|file|mimes:pdf,doc,docx|max:10240', // Max 10MB
        ]);

        // 5. Olah Data
        $biayaClean = preg_replace('/[^0-9]/', '', $request->biaya);
        $anggotaFinal = is_array($anggotaInput) ? array_values(array_filter($anggotaInput)) : [];

        try {
            // Upload File
            $extension = $request->file('file')->getClientOriginalExtension();
            $cleanName = preg_replace('/[^A-Za-z0-9\-]/', '', $request->judul);
            $finalName = substr($cleanName, 0, 50) . '_' . time() . '.' . $extension;
            $filePath = $request->file('file')->storeAs('proposal_files', $finalName, 'public');

            // Simpan Database
            Proposal::create([
                'judul'          => $request->judul,
                'nama_ketua'     => $request->nama_ketua,
                'fakultas_prodi' => $request->fakultas_prodi,
                'file_path'      => $filePath,
                'anggota'        => $anggotaFinal,
                'biaya'          => $biayaClean,
                'status'         => 'Dikirim',
                'user_id'        => $user->id,
            ]);

            return redirect()->route('proposal.index')->with('success', 'Proposal berhasil diajukan!');
        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'Terjadi kesalahan: ' . $e->getMessage());
        }
    }

    /**
     * Halaman Edit Proposal
     */
public function edit($id)
{
    // 1. Ambil data proposal yang akan diedit
    $proposal = \App\Models\Proposal::findOrFail($id);

    // 2. Ambil semua data user (untuk list anggota di datalist)
    // Sesuaikan role jika hanya ingin menampilkan dosen/pengaju saja
    $users = \App\Models\User::all();

    // 3. Cek Masa Hibah (Keamanan)
    $now = now();
    $isMasaHibah = \Illuminate\Support\Facades\DB::table('hibah_periods')
        ->whereDate('start_date', '<=', $now)
        ->whereDate('end_date', '>=', $now)
        ->exists();

    if (!$isMasaHibah) {
        return redirect()->back()->with('error', 'Masa pengeditan proposal telah berakhir.');
    }

    // 4. Kirim semua variabel ke view
    return view('proposal.edit-proposal', compact('proposal', 'users'));
}
    /**
     * Update Data Proposal
     */
public function update(Request $request, $id)
{
    $proposal = \App\Models\Proposal::findOrFail($id);

    // Validasi (Samakan dengan input di Blade)
    $request->validate([
        'judul'          => 'required|string',
        'nama_ketua'     => 'required|string',
        'fakultas_prodi' => 'required',
        'biaya'          => 'required',
        'file'           => 'nullable|file|mimes:pdf|max:10240',
        'anggota'        => 'nullable|array',
    ]);

    // Bersihkan Biaya
    $biayaClean = preg_replace('/[^0-9]/', '', $request->biaya);

    // Update Data
    $proposal->update([
        'judul'          => $request->judul,
        'nama_ketua'     => $request->nama_ketua,
        'fakultas_prodi' => $request->fakultas_prodi,
        'biaya'          => $biayaClean,
        'anggota'        => array_values(array_filter($request->anggota ?? [])),
    ]);

    // Handle File
    if ($request->hasFile('file')) {
        if ($proposal->file_path) \Storage::disk('public')->delete($proposal->file_path);
        $proposal->file_path = $request->file('file')->store('proposal_files', 'public');
    }

    // Status logic
    if (in_array($proposal->status, ['Ditolak', 'Revisi'])) {
        $proposal->status = 'Hasil Revisi';
    }

    $proposal->save();

    return redirect()->route('monitoring.proposalDikirim')->with('success', 'Proposal diperbarui!');
}   /**
     * Download File Proposal
     */
  public function download($id)
{
    $proposal = \App\Models\Proposal::findOrFail($id);

    // Cek apakah file ada di disk public
    if (!\Storage::disk('public')->exists($proposal->file_path)) {
        return back()->with('error', 'File tidak ditemukan di server.');
    }

    $this->logActivity('Mendownload file proposal ID: ' . $id);

    // Menggunakan Storage download lebih aman daripada response()->download(storage_path(...))
    return \Storage::disk('public')->download($proposal->file_path);
}
    /**
     * Assign Reviewer oleh Admin
     */
 public function assignReviewer(Request $request, $id)
{
    $proposal = Proposal::findOrFail($id);

    // Gunakan after_or_equal:today agar tidak error gara-gara selisih jam/menit
    $request->validate([
        'reviewer_1' => 'required|exists:users,id',
        'reviewer_2' => 'required|exists:users,id',
        'review_deadline' => 'required|date|after_or_equal:today',
    ], [
        'reviewer_1.required' => 'Reviewer 1 harus dipilih dari list.',
        'reviewer_2.required' => 'Reviewer 2 harus dipilih dari list.',
        'review_deadline.after_or_equal' => 'Tenggat waktu tidak boleh tanggal yang sudah lewat.',
    ]);

    // Proteksi tambahan
    if ($request->reviewer_1 == $request->reviewer_2) {
        return back()->withInput()->with('error', 'Reviewer 1 dan 2 tidak boleh orang yang sama.');
    }

    if ($request->reviewer_1 == $proposal->user_id || $request->reviewer_2 == $proposal->user_id) {
        return back()->withInput()->with('error', 'Pemilik proposal tidak boleh menjadi reviewer sendiri.');
    }

    // Eksekusi Simpan
    try {
        // Simpan ke tabel pivot
        $proposal->reviewers()->sync([$request->reviewer_1, $request->reviewer_2]);

        // Update data proposal
        $proposal->update([
            'review_deadline' => \Carbon\Carbon::parse($request->review_deadline),
            'status' => 'Sedang Direview'
        ]);



        // ===============================
        // ✅ NOTIF KE REVIEWER (langsung muncul di bell)
        // ===============================
        $deadlineWib = Carbon::parse($proposal->review_deadline)->timezone('Asia/Jakarta');

        $title = 'Tugas Review Proposal';
        $message = 'Anda ditugaskan mereview proposal "' . ($proposal->judul ?? '-') . '". ' .
            'Tenggat penilaian: ' . $deadlineWib->translatedFormat('d M Y H:i') . ' WIB.';

        NotificationHelper::send((int)$request->reviewer_1, $title, $message, 'info', $proposal->id);
        NotificationHelper::send((int)$request->reviewer_2, $title, $message, 'info', $proposal->id);

        return back()->with('success', 'Berhasil! Reviewer telah ditugaskan.');
    } catch (\Exception $e) {
        return back()->withInput()->with('error', 'Database Error: ' . $e->getMessage());
    }
}

    /**
     * Hapus Proposal
     */

   public function destroy($id)
{
    $proposal = Proposal::findOrFail($id);
    $user = auth()->user();

    // 1. Jalur Khusus Admin (Admin bisa hapus siapa saja)
    if ($user->role === 'admin') {
        return $this->prosesHapus($proposal);
    }

    // 2. Jalur Pengaju/Reviewer (Hanya bisa hapus milik sendiri)
    // Gunakan != (bukan !==) untuk menghindari bug tipe data String vs Integer
    if ($proposal->user_id == $user->id) {
        return $this->prosesHapus($proposal);
    }

    // 3. Jika gagal semua, beri pesan error detail
    return back()->with('error', "Anda tidak diizinkan menghapus proposal ini (Milik ID: {$proposal->user_id}).");
}

/**
 * Helper internal agar kode tidak duplikat
 */
private function prosesHapus($proposal)
{
    // Hapus file jika ada
    if ($proposal->file_path && \Storage::disk('public')->exists($proposal->file_path)) {
        \Storage::disk('public')->delete($proposal->file_path);
    }

    $proposal->delete();
    return back()->with('success', 'Proposal berhasil dihapus.');
}

    /**
     * Cetak Review ke PDF
     */
   public function downloadReviewPdf($id)
{
    $review = Review::with(['reviewer', 'proposal'])->findOrFail($id);
    $data = ['reviews' => collect([$review]), 'is_pdf' => true];

    $pdf = Pdf::loadView('proposal.admin.hasil-review', $data);

    // Gunakan stream() agar muncul di tab baru browser
    return $pdf->setPaper('a4', 'portrait')->stream('Hasil-Review-'.$id.'.pdf');
}

    // --- View Monitoring Methods ---

// Sesuaikan nama function dengan Route (perluDireview)
public function perluDireview()
{
    $user = auth()->user();

    // Inisialisasi Query
    $query = Proposal::with(['reviewers', 'user']);

    if ($user->role === 'admin') {
        // Admin bisa melihat semua yang perlu diplot atau sedang direview
        $query->whereIn('status', ['Dikirim', 'Perlu Direview', 'Sedang Direview', 'Hasil Revisi']);
    }
    elseif ($user->role === 'reviewer') {
        // Reviewer HANYA melihat proposal yang ditugaskan kepadanya
        $query->where('status', 'Sedang Direview')
              ->whereHas('reviewers', function($q) use ($user) {
                  $q->where('reviewer_id', $user->id);
              });
    }

    $proposals = $query->latest()->get();
    $reviewers = User::where('role', 'reviewer')->orderBy('name')->get();

    return view('proposal.proposal-perlu-direview', compact('proposals', 'reviewers'));
}
     public function proposalSedangDireview()
    {
        $proposals = Proposal::where('status', 'Sedang Direview')
            ->with(['reviewers', 'reviews'])
            ->latest()
            ->get();

        return view('proposal.proposal-sedang-direview', compact('proposals'));
    }
    public function reviewSelesai()
    {
        $proposals = Proposal::where('status', 'Review Selesai')
            ->orWhereIn('status_pendanaan', ['Disetujui', 'Ditolak', 'Direvisi'])
            ->with(['user', 'reviews.reviewer'])
            ->latest('updated_at')
            ->get();

        return view('proposal.proposal-selesai', compact('proposals'));
    }
    public function proposalDitolak()
    {
        $proposals = Proposal::where('status', 'Ditolak')->latest()->get();
        return view('proposal.proposal-ditolak', compact('proposals'));
    }
public function proposalDirevisi()
    {
        $proposals = Proposal::whereIn('status', ['Ditolak', 'Direvisi'])->latest()->get();
        return view('proposal.proposal-direvisi', compact('proposals'));
    }

// Fungsi untuk Proposal Disetujui
public function proposalDisetujui()
{
    // Mengambil proposal yang statusnya 'Disetujui' DAN dimiliki oleh user yang login
    $proposals = Proposal::where('user_id', auth()->id())
        ->where('status', 'Disetujui')
        ->with('reviewers') // Eager loading untuk menampilkan nama reviewer
        ->latest()
        ->get();

    return view('proposal.proposal-disetujui', compact('proposals'));
}
// Tambahkan juga fungsi ini jika belum ada (antisipasi error berikutnya)
public function proposalSelesai()
{
    // Ambil semua proposal yang sudah melewati tahap review
    $proposals = Proposal::whereIn('status', [
            'Review Selesai',
            'Disetujui',
            'Ditolak',
            'Selesai'
        ])
        ->latest()
        ->get();

    return view('proposal.proposal-selesai', compact('proposals'));
}
public function hasilRevisi()
    {
        $proposals = Proposal::where('status', 'Hasil Revisi')->latest()->get();
        return view('proposal.hasil-review', compact('proposals'));
    }

public function keputusan(Request $request, $id)
{
    $proposal = Proposal::findOrFail($id);

    // 1. Tambahkan 'Disetujui' di sini
    $request->validate([
        'status_pendanaan' => 'required|string|in:Disetujui,Ditolak,Direvisi,Review Selesai'
    ]);

    // 2. Update status utama agar tidak hilang dari query 'whereIn' halaman Selesai
    $proposal->status = $request->status_pendanaan;

    // 3. Simpan juga ke kolom khusus pendanaan jika Anda menggunakannya
    $proposal->status_pendanaan = $request->status_pendanaan;

    $proposal->save();

    if (method_exists($this, 'logActivity')) {
        $this->logActivity("Admin mengubah status proposal ID {$id} menjadi: " . $request->status_pendanaan);
    }

    return redirect()->back()->with('success', 'Keputusan berhasil disimpan!');
}
public function setReview($id)
{
    $proposal = Proposal::findOrFail($id);

    // Ubah status agar masuk ke kategori "Perlu Direview"
    $proposal->status = 'Sedang Direview';
    $proposal->save();

    // Alihkan ke route halaman "Proposal Perlu Direview"
    return redirect()->route('monitoring.proposalPerluDireview')
                     ->with('success', 'Proposal telah dipindahkan ke daftar review.');
}
 public function tinjau($id)
    {
        $proposal = Proposal::with(['user', 'reviewers', 'reviews.reviewer'])->findOrFail($id);
        return view('proposal.tinjau-proposal', compact('proposal'));
    }
     public function daftarReview(Request $request)
{
    $user = Auth::user();
    $query = Proposal::with(['user', 'reviewers']);

    if ($user->role === 'reviewer') {
        // HANYA menampilkan proposal yang ditugaskan ke reviewer ini
        $query->whereHas('reviewers', function($q) use ($user) {
            $q->where('reviewer_id', $user->id);
        });

        // Opsional: Hanya tampilkan yang statusnya sedang dalam tahap review
        $query->whereIn('status', ['Perlu Direview', 'Sedang Direview', 'Review Selesai']);
    }

    $proposals = $query->latest()->get();

    return view('admin.daftar_review', compact('proposals'));
}

public function uploadLaporanKemajuan(Request $request, $id)
{
    $request->validate([
        'laporan_kemajuan' => 'required|mimes:pdf|max:2048',
    ]);

    $proposal = Proposal::findOrFail($id);

    $file = $request->file('laporan_kemajuan');
    $filename = 'laporan_kemajuan_' . time() . '.' . $file->getClientOriginalExtension();
    $file->storeAs('laporan', $filename, 'public');

    $proposal->update(['laporan_kemajuan' => $filename]);

    return back()->with('success', 'Laporan Kemajuan berhasil diunggah.');
}

public function uploadLaporanAkhir(Request $request, $id)
{
    $request->validate([
        'laporan_akhir' => 'required|mimes:pdf|max:2048',
    ]);

    $proposal = Proposal::findOrFail($id);

    $file = $request->file('laporan_akhir');
    $filename = 'laporan_akhir_' . time() . '.' . $file->getClientOriginalExtension();
    $file->storeAs('laporan', $filename, 'public');

    $proposal->update(['laporan_akhir' => $filename]);

    return back()->with('success', 'Laporan Akhir berhasil diunggah.');
}



    // ===============================
    // ✅ FIX ROUTE MONITORING
    // ===============================
    public function dikirim(Request $request)
    {
        $tahun = $request->get('tahun', date('Y'));
        $user = auth()->user();
        $role = $user->role;

        $list_fakultas = Fakultas::all();
        $all_dosen = User::whereIn('role', ['pengaju', 'reviewer'])->get();

        // --- LOGIKA MASA HIBAH ---
        $now = now();
        $isMasaHibah = DB::table('hibah_periods')
            ->whereDate('start_date', '<=', $now)
            ->whereDate('end_date', '>=', $now)
            ->exists();
        // -------------------------

        $query = Proposal::with(['fakultas', 'user'])
            ->whereYear('created_at', $tahun)
            ->where('status', 'Dikirim');

        if ($role === 'pengaju' || $role === 'reviewer') {
            $query->where('user_id', $user->id);
        }

        $proposals = $query->latest()->get();

        return view('proposal.daftar_proposal', compact(
            'proposals',
            'tahun',
            'role',
            'list_fakultas',
            'all_dosen',
            'isMasaHibah'
        ));
    }

    // Alias biar nama method di route aman (kalau route pakai sedangDireview/disetujui/ditolak)
    public function sedangDireview()
    {
        return $this->proposalSedangDireview();
    }

    public function disetujui()
    {
        return $this->proposalDisetujui();
    }

    public function ditolak()
    {
        return $this->proposalDitolak();
    }

}

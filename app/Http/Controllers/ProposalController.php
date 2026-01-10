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
            'user_id' => auth()->id(),
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
    // 1. Ambil data dasar
    $tahun = $request->get('tahun', date('Y'));
    $user = auth()->user();
    $role = $user->role; // Definisikan di awal agar bisa dipakai di bawah

    // 2. Ambil data pendukung untuk Modal
    $list_fakultas = Fakultas::all();
    $all_dosen = User::where('role', 'pengaju')->get();

    // 3. Bangun Query
    $query = Proposal::with(['fakultas', 'user'])
        ->whereYear('created_at', $tahun);

   // ✅ LOGIKA FILTER DIPERBARUI:
    // Admin melihat semua. Pengaju & Reviewer hanya melihat milik sendiri.
    if ($role === 'pengaju' || $role === 'reviewer') {
        $query->where('user_id', $user->id);
    }

    $proposals = $query->latest()->get();

    return view('proposal.daftar_proposal', compact(
        'proposals',
        'tahun',
        'role',
        'list_fakultas',
        'all_dosen'
    ));
    $semuaAnggotaInput = $request->anggota ?? [];
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
        $proposal = Proposal::findOrFail($id);
        $list_fakultas = Fakultas::all();
        $users = User::all();

        if (auth()->user()->role !== 'admin' && auth()->user()->id !== $proposal->user_id) {
            abort(403, 'Akses ditolak.');
        }

        return view('proposal.edit-proposal', compact('proposal', 'list_fakultas', 'users'));
    }

    /**
     * Update Data Proposal
     */
  public function update(Request $request, $id)
    {
        $proposal = Proposal::findOrFail($id);

        if (auth()->id() !== $proposal->user_id && auth()->user()->role !== 'admin') {
            abort(403, 'Akses ditolak.');
        }

        $request->validate([
            'judul'           => 'required|string|max:255',
            'nama_ketua'      => 'required|string|max:255',
            'fakultas_prodi'  => 'required|exists:fakultas,id',
            'biaya'           => 'required',
            'file'            => 'nullable|file|mimes:pdf|max:10240', // Max 10MB
            'anggota'         => 'nullable|array',
        ]);

        // 1. Bersihkan biaya (menghapus titik ribuan agar jadi angka murni)
        $biayaClean = preg_replace('/[^0-9]/', '', $request->biaya);

        $oldStatus = $proposal->status;

        // 2. Update field dasar
        $proposal->judul = $request->judul;
        $proposal->nama_ketua = $request->nama_ketua;
        $proposal->fakultas_prodi = $request->fakultas_prodi;
        $proposal->biaya = $biayaClean;
        $proposal->anggota = array_values(array_filter($request->anggota ?? []));

        // 3. Handle File
        if ($request->hasFile('file')) {
            if ($proposal->file_path && Storage::disk('public')->exists($proposal->file_path)) {
                Storage::disk('public')->delete($proposal->file_path);
            }
            $proposal->file_path = $request->file('file')->store('proposal_files', 'public');
        }

        // 4. Ubah status ke 'Hasil Revisi' agar pindah ke halaman monitoring terkait
        if (in_array($oldStatus, ['Ditolak', 'Revisi'])) {
            $proposal->status = 'Hasil Revisi';
        }

        $proposal->save();

        // Gunakan redirect ke route hasilRevisi
        return redirect()->route('monitoring.hasilRevisi')->with('success', 'Proposal berhasil diperbarui dan dikirim ke Hasil Revisi.');
    }
    /**
     * Download File Proposal
     */
    public function download($id)
    {
        $proposal = Proposal::findOrFail($id);
        $path = storage_path('app/public/' . $proposal->file_path);

        if (!file_exists($path)) return back()->with('error', 'File tidak ditemukan.');

        $this->logActivity('Mendownload file proposal ID: ' . $id);
        return response()->download($path);
    }

    /**
     * Assign Reviewer oleh Admin
     */
    public function assignReviewer(Request $request, $id)
    {
        $proposal = Proposal::findOrFail($id);

        $request->validate([
            'reviewer_1' => 'required|exists:users,id',
            'reviewer_2' => 'required|exists:users,id',
            'review_deadline' => 'required|date|after:now',
        ]);

        if ($request->reviewer_1 == $proposal->user_id || $request->reviewer_2 == $proposal->user_id) {
            return back()->with('error', 'Reviewer tidak boleh pemilik proposal!');
        }

        if ($request->reviewer_1 == $request->reviewer_2) {
            return back()->with('error', 'Reviewer 1 dan 2 tidak boleh orang yang sama.');
        }

        $proposal->reviewers()->sync([$request->reviewer_1, $request->reviewer_2]);

        $proposal->update([
            'review_deadline' => Carbon::parse($request->review_deadline),
            'status' => 'Sedang Direview'
        ]);

        return back()->with('success', 'Reviewer berhasil ditugaskan.');
    }

    /**
     * Hapus Proposal
     */
  public function destroy($id)
{
    $proposal = Proposal::findOrFail($id);

    // Pastikan hanya pemilik yang bisa hapus
    if (auth()->id() !== $proposal->user_id) {
        abort(403, 'Anda tidak memiliki akses untuk menghapus proposal ini.');
    }

    // Hapus file dari storage jika ada
    if ($proposal->file_path) {
        Storage::disk('public')->delete($proposal->file_path);
    }

    $proposal->delete();

    return redirect()->back()->with('success', 'Proposal berhasil dihapus.');
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

  public function proposalPerluDireview()
{
    $user = auth()->user();

    // ✅ PERBAIKAN: Tambahkan 'Sedang Direview' (dan 'Dikirim'/'Hasil Revisi' jika perlu)
    $query = Proposal::whereIn('status', [
            'Perlu Direview',
            'Sedang Direview',
            'Dikirim',
            'Hasil Revisi'
        ])
        ->with('reviewers');

    // Proteksi: Reviewer tidak boleh melihat proposal miliknya sendiri
    if ($user->role === 'reviewer') {
        $query->where('user_id', '!=', $user->id);
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
        $proposals = Proposal::where('status', 'Disetujui')->latest()->get();
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


}

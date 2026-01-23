<?php

namespace App\Http\Controllers;

use App\Models\AdminDocument;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;

class AdminDocumentController extends Controller
{
    /**
     * Tampilan untuk ADMIN (Kelola Dokumen)
     * Admin bisa melihat SEMUA dokumen termasuk yang disembunyikan.
     */
    public function index() {
    $docs = AdminDocument::latest()->get(); // Tanpa filter is_visible
    return view('admin.dokumen.index', compact('docs'));
}
    /**
     * Tampilan untuk USER (Daftar Dokumen Penting)
     * Hanya menampilkan dokumen yang statusnya is_visible = true.
     */
    public function userView()
    {
        // Filter is_visible = true agar user tidak melihat dokumen yang disembunyikan
        $docs = AdminDocument::where('is_visible', true)->latest()->get();

        // Mengarah ke view user
        return view('user.dokumen.index', compact('docs'));
    }

    /**
     * Proses Simpan Dokumen (ADMIN ONLY)
     */
    public function store(Request $request)
    {
        $request->validate([
            'judul' => 'required|string|max:255',
            'file' => 'required|mimes:pdf,doc,docx|max:5120' // Maksimal 5MB
        ]);

        if ($request->hasFile('file')) {
            // Simpan file ke folder storage/app/public/admin_docs
            $path = $request->file('file')->store('admin_docs', 'public');

            AdminDocument::create([
                'judul' => $request->judul,
                'file_path' => $path,
                'uploaded_by' => Auth::id(),
                'is_visible' => true, // Secara default langsung tampil
            ]);

            return back()->with('success', 'Dokumen berhasil dipublikasikan!');
        }

        return back()->with('error', 'Gagal mengunggah file.');
    }

    /**
     * Toggle Sembunyikan/Tampilkan (ADMIN ONLY)
     * Ini TIDAK MENGHAPUS data, hanya mengubah status 0 atau 1.
     */
   public function toggleVisibility($id)
{
    $doc = AdminDocument::findOrFail($id);
    $doc->is_visible = !$doc->is_visible;
    $doc->save();

    $status = $doc->is_visible ? 'ditampilkan' : 'disembunyikan';

    // Kembali ke halaman admin agar admin tetap bisa melihat data yang baru saja disembunyikan
    return redirect()->route('admin.dokumen.index')->with('success', "Dokumen berhasil $status.");
}

    /**
     * Proses Download untuk Admin & User
     */
    public function download($id)
    {
        $doc = AdminDocument::findOrFail($id);

        // Cek fisik file di storage
        if (!Storage::disk('public')->exists($doc->file_path)) {
            return back()->with('error', 'File fisik tidak ditemukan di server.');
        }

        return Storage::disk('public')->download($doc->file_path, $doc->judul . '.' . pathinfo($doc->file_path, PATHINFO_EXTENSION));
    }

    /**
     * Tambahan: Hapus Dokumen Permanen (ADMIN ONLY)
     * Jika admin benar-benar ingin menghapus data dan file fisiknya.
     */
    public function destroy($id)
    {
        $doc = AdminDocument::findOrFail($id);

        // Hapus file fisik dari storage agar tidak memenuhi server
        if (Storage::disk('public')->exists($doc->file_path)) {
            Storage::disk('public')->delete($doc->file_path);
        }

        $doc->delete();

        return back()->with('success', 'Dokumen berhasil dihapus secara permanen.');
    }


}

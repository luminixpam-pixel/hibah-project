<?php

namespace App\Http\Controllers;

use App\Models\Proposal;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Auth; // Tambahkan ini untuk Auth

class LaporanKemajuanController extends Controller
{
    public function index(Request $request)
    {
        $search = $request->input('search');

        // Mengambil data proposal dengan pagination dan pencarian
        // Ditambahkan pengurutan terbaru agar data yang baru diupdate muncul di atas
        $proposals = Proposal::with(['user', 'reviewer'])
            ->when($search, function ($query, $search) {
                return $query->where('judul', 'like', "%{$search}%")
                             ->orWhereHas('user', function($q) use ($search) {
                                 $q->where('name', 'like', "%{$search}%");
                             });
            })
            ->latest()
            ->paginate(10);

        return view('reviewer.laporan-kemajuan', compact('proposals'));
    }

    public function store(Request $request)
{
    $request->validate([
        'file' => 'required|mimes:pdf,doc,docx|max:2048',
        'keterangan' => 'nullable|string',
    ]);

    if ($request->hasFile('file')) {
        // Cari proposal milik user login
        $proposal = Proposal::where('user_id', Auth::id())->first();

        // JIKA PROPOSAL DITEMUKAN, UPDATE DATABASE
        if ($proposal) {
            if ($proposal->file_laporan) {
                Storage::disk('public')->delete($proposal->file_laporan);
            }
            $path = $request->file('file')->store('laporan', 'public');
            $proposal->update([
                'file_laporan' => $path,
                'keterangan' => $request->keterangan,
                'status' => 'Selesai'
            ]);
            return redirect()->back()->with('success', 'Laporan berhasil diunggah dan data diperbarui!');
        }

        // JIKA TIDAK ADA DATA PROPOSAL (UNTUK TEST SAJA)
        // Kita hanya simpan filenya saja tanpa update database agar tidak error merah
        $request->file('file')->store('laporan', 'public');
        return redirect()->back()->with('success', 'File berhasil terupload ke server (Data proposal database belum ada).');
    }

    return redirect()->back()->withErrors(['file' => 'Gagal mengunggah file.']);
}
}

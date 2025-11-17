<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Proposal;
use Illuminate\Support\Facades\Storage;

class ProposalController extends Controller
{
    /**
     * Halaman daftar proposal
     */
    public function index()
    {
        $proposals = Proposal::latest()->get();
        return view('proposal.index', compact('proposals'));
    }

    /**
     * Store proposal dari popup
     */
    public function store(Request $request)
    {
        $request->validate([
            'judul' => 'required|string|max:255',
            'file'  => 'required|file|mimes:pdf,doc,docx|max:102400',
        ]);

        // Upload file ke storage/app/public/proposal_files
        $filePath = $request->file('file')->store('proposal_files', 'public');

        // Simpan database
        Proposal::create([
            'judul' => $request->judul,
            'file_path' => $filePath,
        ]);

        // Setelah submit popup → pindah ke halaman proposal
        return redirect()->route('proposal.index')->with('success', 'Proposal berhasil diajukan!');
    }

    /**
     * Download file proposal
     */
    public function download($id)
    {
        $proposal = Proposal::findOrFail($id);

        if (!$proposal->file_path) {
            return back()->with('error', 'File belum diupload.');
        }

        $path = storage_path('app/public/' . $proposal->file_path);

        if (!file_exists($path)) {
            return back()->with('error', 'File tidak ditemukan di server.');
        }

        return response()->download($path);
        return redirect()->route('proposal.index')->with('success', 'Proposal berhasil dikirim!');

    }
}

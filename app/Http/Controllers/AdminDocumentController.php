<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\AdminDocument;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;

class AdminDocumentController extends Controller
{
    // ADMIN VIEW
    public function index()
    {
        $docs = AdminDocument::latest()->get();
        return view('admin.dokumen.index', compact('docs'));
    }

    // ADMIN UPLOAD
    public function store(Request $request)
    {
        $request->validate([
            'judul' => 'required',
            'file' => 'required|mimes:pdf,doc,docx|max:5120'
        ]);

        $path = $request->file('file')->store('admin_docs','public');

        AdminDocument::create([
            'judul' => $request->judul,
            'file_path' => $path,
            'uploaded_by' => Auth::id(),
            'is_visible' => true, // ✅ default tampil
        ]);

        return back()->with('success','Dokumen berhasil diunggah');
    }

    // ✅ TOGGLE TAMPILKAN / SEMBUNYIKAN (ADMIN)
    public function toggleVisibility($id)
    {
        $doc = AdminDocument::findOrFail($id);

        $doc->is_visible = !$doc->is_visible;
        $doc->save();

        return back()->with('success', 'Status dokumen berhasil diubah');
    }

    // USER VIEW (hanya yang tampil)
    public function userView()
    {
        $docs = AdminDocument::where('is_visible', true)->latest()->get();
        return view('user.dokumen.index', compact('docs'));
    }

    // DOWNLOAD (kalau user tetap butuh download, route ini tetap aman)
    public function download($id)
    {
        $doc = AdminDocument::findOrFail($id);
        return Storage::disk('public')->download($doc->file_path);
    }
}

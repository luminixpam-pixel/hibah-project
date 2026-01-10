<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Template;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Auth;

class TemplateController extends Controller
{
    /**
     * Menyimpan atau memperbarui template (Khusus Admin)
     */
    public function store(Request $request)
    {
        // 1. Validasi Input
        $request->validate([
            'file_template' => 'required|mimes:docx,doc,pdf,xlsx|max:5120', // Maks 5MB
            'jenis'         => 'required|string', // misal: 'laporan_kemajuan'
        ]);

        try {
            if ($request->hasFile('file_template')) {
                $file = $request->file('file_template');
                $jenis = $request->jenis;

                // 2. Cari data lama untuk dihapus filenya (agar tidak memenuhi storage)
                $oldTemplate = Template::where('jenis', $jenis)->first();
                if ($oldTemplate && Storage::disk('public')->exists($oldTemplate->file_path)) {
                    Storage::disk('public')->delete($oldTemplate->file_path);
                }

                // 3. Simpan file baru ke folder 'templates' di public storage
                $fileName = time() . '_' . $file->getClientOriginalName();
                $path = $file->storeAs('templates', $fileName, 'public');

                // 4. Update Database
                Template::updateOrCreate(
                    ['jenis' => $jenis],
                    [
                        'nama_template' => $file->getClientOriginalName(),
                        'file_path'     => $path
                    ]
                );

                return back()->with('success', 'Template ' . str_replace('_', ' ', $jenis) . ' berhasil diperbarui!');
            }
        } catch (\Exception $e) {
            return back()->with('error', 'Gagal mengunggah template: ' . $e->getMessage());
        }
    }

    /**
     * Menghapus template (Opsional untuk Admin)
     */
    public function destroy($id)
    {
        $template = Template::findOrFail($id);

        if (Storage::disk('public')->exists($template->file_path)) {
            Storage::disk('public')->delete($template->file_path);
        }

        $template->delete();
        return back()->with('success', 'Template berhasil dihapus.');
    }
}

<?php

namespace App\Http\Controllers;

use App\Models\AdminDocument;
use Illuminate\Support\Facades\Storage;

class DokumenResmiController extends Controller
{
    public function download($id)
    {
        $doc = AdminDocument::findOrFail($id);

        return Storage::disk('public')->download($doc->file);
    }
}

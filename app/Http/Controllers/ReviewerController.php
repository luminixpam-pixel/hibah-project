<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Proposal;
use App\Models\Review;
use Illuminate\Support\Facades\Auth;

class ReviewerController extends Controller
{
    /**
     * Halaman isi review.
     * Dipanggil dari route: reviewer.isi-review
     */
    public function isiReview($id)
    {
        $proposal = Proposal::findOrFail($id);

        // Jika user adalah reviewer, pastikan dia reviewer yang ditugaskan
        if (Auth::user()->role === 'reviewer') {
            if (!$proposal->reviewer || $proposal->reviewer !== Auth::user()->name) {
                abort(403, 'Anda bukan reviewer yang ditugaskan untuk proposal ini.');
            }
        }

        // Kalau masih "Perlu Direview", pindahkan ke "Sedang Direview"
        if ($proposal->status === 'Perlu Direview') {
            $proposal->status = 'Sedang Direview';
            $proposal->save();
        }

        return view('reviewer.isi-review', compact('proposal'));
    }

    /**
     * Simpan hasil review.
     * Dipanggil dari route: reviewer.submitReview / review.simpan
     */
    public function submitReview(Request $request, $id)
    {
        $proposal = Proposal::findOrFail($id);

        // Jika user adalah reviewer, pastikan dia reviewer yang ditugaskan
        if (Auth::user()->role === 'reviewer') {
            if (!$proposal->reviewer || $proposal->reviewer !== Auth::user()->name) {
                abort(403, 'Anda bukan reviewer yang ditugaskan untuk proposal ini.');
            }
        }

        // validasi sederhana
        $request->validate([
            'nilai_1' => 'nullable|integer',
            'nilai_2' => 'nullable|integer',
            'nilai_3' => 'nullable|integer',
            'nilai_4' => 'nullable|integer',
            'nilai_5' => 'nullable|integer',
            'nilai_6' => 'nullable|integer',
            'nilai_7' => 'nullable|integer',
            'status'   => 'nullable|string|max:50',
            'catatan'  => 'nullable|string',
        ]);

        // hitung total skor dari 7 nilai (yang terisi saja)
        $nilai = [
            $request->nilai_1,
            $request->nilai_2,
            $request->nilai_3,
            $request->nilai_4,
            $request->nilai_5,
            $request->nilai_6,
            $request->nilai_7,
        ];

        $totalScore = collect($nilai)
            ->filter(fn ($v) => $v !== null && $v !== '')
            ->sum();

        // simpan ke tabel reviews
        Review::create([
            'proposal_id' => $proposal->id,
            'reviewer_id' => Auth::id(),
            'nilai_1'     => $request->nilai_1,
            'nilai_2'     => $request->nilai_2,
            'nilai_3'     => $request->nilai_3,
            'nilai_4'     => $request->nilai_4,
            'nilai_5'     => $request->nilai_5,
            'nilai_6'     => $request->nilai_6,
            'nilai_7'     => $request->nilai_7,
            'status'      => $request->status,
            'catatan'     => $request->catatan,
            'total_score' => $totalScore,
        ]);

        // update status proposal setelah direview
        if ($request->status === 'disetujui') {
            $proposal->status = 'Disetujui';
        } elseif ($request->status === 'ditolak') {
            $proposal->status = 'Ditolak';
        } elseif ($request->status === 'direvisi') {
            $proposal->status = 'Direvisi';
        } else {
            // kalau tidak pilih apa-apa, anggap review selesai
            $proposal->status = 'Review Selesai';
        }

        $proposal->save();

        // ⬅️ langsung ke halaman Review Selesai
        return redirect()
            ->route('monitoring.reviewSelesai')
            ->with('success', 'Review berhasil disimpan.');
    }
}

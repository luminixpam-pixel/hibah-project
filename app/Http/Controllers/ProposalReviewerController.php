<?php

namespace App\Http\Controllers;

use App\Models\Proposal;
use App\Models\User;
use App\Models\Notification;
use App\Helpers\NotificationHelper; // ✅ pakai helper biar pasti masuk
use Illuminate\Http\Request;
use Carbon\Carbon;

class ProposalReviewerController extends Controller
{
    // 🔍 SEARCH REVIEWER (buat autocomplete)
    public function search(Request $request)
    {
        return User::where('role', 'reviewer')
            ->where('name', 'like', '%' . $request->q . '%')
            ->limit(10)
            ->get(['id', 'name', 'penempatan']);
    }

    // 💾 SIMPAN 2 REVIEWER KE PROPOSAL (+ SIMPAN TENGGAT)
    public function assign(Request $request, $proposalId)
    {
        $request->validate([
            'reviewer_1'      => 'required|exists:users,id',
            'reviewer_2'      => 'required|exists:users,id|different:reviewer_1',
            'review_deadline' => 'required|date',
        ]);

        $proposal = Proposal::findOrFail($proposalId);

        // ✅ FIX WIB: parse datetime-local sebagai Asia/Jakarta (biar H-3/H-2/H-1 tepat)
        // input: 2026-01-06T17:30
        $tz = 'Asia/Jakarta';
        $deadline = Carbon::createFromFormat('Y-m-d\TH:i', $request->review_deadline, $tz);

        $proposal->review_deadline = $deadline->format('Y-m-d H:i:s');
        $proposal->save();

        // maksimal 2 reviewer
        $proposal->reviewers()->sync([
            $request->reviewer_1,
            $request->reviewer_2,
        ]);

        /**
         * ✅ NOTIF MASUK KE BELL REVIEWER (TUGAS + TENGGAT)
         * - ini yang bikin reviewer langsung dapat notif, gak nunggu H-3
         * - kalau admin update deadline, notifnya ikut update (bukan nambah terus)
         */
        $titleTugas = 'Proposal Baru Ditugaskan';
        $msgTugas = 'Anda ditugaskan mereview proposal "' . ($proposal->judul ?? '-') . '".';

        $titleTenggatUpdate = 'Tenggat Review Diperbarui';
        $msgTenggatUpdate = 'Tenggat review proposal "' . ($proposal->judul ?? '-') . '" adalah '
            . $deadline->translatedFormat('d M Y H:i') . ' WIB.';

        foreach ([(int)$request->reviewer_1, (int)$request->reviewer_2] as $rid) {

            // 1) Notif tugas review (selalu ada)
            Notification::updateOrCreate(
                [
                    'user_id'     => $rid,
                    'proposal_id' => (int)$proposal->id,
                    'title'       => $titleTugas,
                ],
                [
                    'message' => $msgTugas,
                    'type'    => 'info',
                    'is_read' => false,
                ]
            );

            // 2) Notif update tenggat (selalu update kalau berubah)
            Notification::updateOrCreate(
                [
                    'user_id'     => $rid,
                    'proposal_id' => (int)$proposal->id,
                    'title'       => $titleTenggatUpdate,
                ],
                [
                    'message' => $msgTenggatUpdate,
                    'type'    => 'warning',
                    'is_read' => false,
                ]
            );
        }

        return back()->with('success', 'Reviewer & tenggat berhasil ditetapkan');
    }
}

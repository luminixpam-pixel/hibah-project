<?php

namespace App\Http\Controllers;

use App\Models\Notification;
use App\Models\User;
use App\Models\Proposal;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log; // ✅ tambah ini (buat safety)
use App\Helpers\NotificationHelper;
use Carbon\Carbon;

class NotificationController extends Controller
{
    /**
     * ✅ Helper: generate / update notif deadline (H-3/H-2/H-1) untuk reviewer tertentu
     * Dipanggil dari web request (count/fetch) => TANPA console/scheduler
     */
    private static function generateDeadlineNotifsForReviewer(int $reviewerId): void
    {
        $tz = 'Asia/Jakarta';

        // ✅ FIX: now langsung WIB (jangan now()->setTimezone, itu sering bikin shift kalau app timezone beda)
        $now = Carbon::now($tz);

        $proposals = Proposal::query()
            ->with('reviewers')
            ->whereNotNull('review_deadline')
            ->whereIn('status', ['Dikirim', 'Perlu Direview', 'Sedang Direview'])
            ->whereHas('reviewers', function ($q) use ($reviewerId) {
                // ✅ FIX: tetap pakai users.id biar gak ambigu
                $q->where('users.id', $reviewerId);
            })
            ->get();

        foreach ($proposals as $proposal) {
            if (!$proposal->review_deadline) continue;

            // ✅ FIX: parse deadline langsung WIB (bukan parse dulu baru setTimezone)
            $deadline = Carbon::parse($proposal->review_deadline, $tz);

            $existing = Notification::where('user_id', $reviewerId)
                ->where('proposal_id', $proposal->id)
                ->where('type', 'warning')
                ->where('title', 'like', 'Tenggat review%')
                ->orderByDesc('created_at')
                ->first();

            if ($deadline->lt($now)) {
                if ($existing) $existing->delete();
                continue;
            }

            // ✅ FIX: hitung H-3/H-2/H-1 stabil berdasarkan tanggal WIB
            $daysLeft = $now->copy()->startOfDay()->diffInDays($deadline->copy()->startOfDay(), false);

            if (!in_array($daysLeft, [3, 2, 1], true)) {
                if ($existing) $existing->delete();
                continue;
            }

            $title = "Tenggat review {$daysLeft} hari lagi";
            $message = 'Proposal "' . ($proposal->judul ?? '-') . '" harus dinilai sebelum '
                . $deadline->translatedFormat('d M Y H:i') . ' WIB.';

            if ($existing) {
                if ($existing->title !== $title || $existing->message !== $message) {
                    $existing->update([
                        'title'   => $title,
                        'message' => $message,
                        'type'    => 'warning',
                        'is_read' => false,
                    ]);
                }
                continue;
            }

            Notification::create([
                'user_id'     => $reviewerId,
                'proposal_id' => $proposal->id,
                'title'       => $title,
                'message'     => $message,
                'type'        => 'warning',
                'is_read'     => false,
            ]);
        }
    }

    public function count()
    {
        // ✅ jangan sampai deadline generator bikin bell kosong kalau error
        if (Auth::user()->role === 'reviewer') {
            try {
                self::generateDeadlineNotifsForReviewer(Auth::id());
            } catch (\Throwable $e) {
                Log::error('deadline notif error (count): ' . $e->getMessage());
            }
        }

        $count = Notification::where('user_id', Auth::id())
            ->where('is_read', false)
            ->count();

        return response()->json(['count' => $count]);
    }

    public function fetch()
    {
        if (Auth::user()->role === 'reviewer') {
            try {
                self::generateDeadlineNotifsForReviewer(Auth::id());
            } catch (\Throwable $e) {
                Log::error('deadline notif error (fetch): ' . $e->getMessage());
            }
        }

        $notifications = Notification::where('user_id', Auth::id())
            ->orderBy('created_at', 'desc')
            ->limit(50) // ✅ biar notif lama (waktu masih pengaju) gak “ketutup”
            ->get();

        $notifications->transform(function ($notif) {
            if (empty($notif->title)) {
                $notif->title = 'Proposal Baru Ditugaskan';
            }
            return $notif;
        });

        return response()->json($notifications);
    }

    public function markAllAsRead()
    {
        Notification::where('user_id', Auth::id())
            ->where('is_read', false)
            ->update(['is_read' => true]);

        return response()->json([
            'status'  => 'success',
            'message' => 'Semua notifikasi telah dibaca'
        ]);
    }

    public function deadlineCheck()
    {
        if (Auth::user()->role !== 'reviewer') {
            return response()->json(['popups' => []]);
        }

        try {
            self::generateDeadlineNotifsForReviewer(Auth::id());
        } catch (\Throwable $e) {
            Log::error('deadline notif error (deadlineCheck): ' . $e->getMessage());
            return response()->json(['popups' => []]);
        }

        $items = Notification::where('user_id', Auth::id())
            ->where('is_read', false)
            ->where('type', 'warning')
            ->where('title', 'like', 'Tenggat review%')
            ->orderBy('created_at', 'desc')
            ->limit(3)
            ->get(['title', 'message']);

        return response()->json([
            'popups' => $items->map(fn($n) => [
                'title' => $n->title,
                'message' => $n->message,
            ])->values(),
        ]);
    }

    public function sendToAll(Request $request)
    {
        if (Auth::user()->role !== 'admin') abort(403);

        $request->validate([
            'title'   => 'required|string|max:255',
            'message' => 'nullable|string',
            'type'    => 'nullable|in:info,success,warning'
        ]);

        NotificationHelper::sendToAll(
            $request->title,
            $request->message,
            $request->type ?? 'info'
        );

        return response()->json([
            'status'  => 'success',
            'message' => 'Notifikasi berhasil dikirim ke semua user'
        ]);
    }

    public function sendToUser(Request $request)
    {
        if (Auth::user()->role !== 'admin') abort(403);

        $request->validate([
            'user_id' => 'required|exists:users,id',
            'title'   => 'required|string|max:255',
            'message' => 'nullable|string',
            'type'    => 'nullable|in:info,success,warning'
        ]);

        NotificationHelper::send(
            $request->user_id,
            $request->title,
            $request->message,
            $request->type ?? 'info'
        );

        return response()->json([
            'status'  => 'success',
            'message' => 'Notifikasi berhasil dikirim ke user'
        ]);
    }

    // ================== KODE LAMA (AMAN) ==================
    public static function notifyPeriodUpdated()
    {
        $users = User::where('role', 'pengaju')->get();

        foreach ($users as $user) {
            NotificationHelper::send(
                $user->id,
                'Periode Upload Proposal Diperbarui',
                'Periode upload proposal baru telah diperbarui. Silakan cek dashboard.',
                'info'
            );
        }
    }

    public static function notifyProposalSubmitted($userId)
    {
        NotificationHelper::send(
            $userId,
            'Proposal Dikirim',
            'Proposal Anda berhasil dikirim dan menunggu proses review.',
            'success'
        );
    }

    public static function notifyProposalUnderReview($userId)
    {
        NotificationHelper::send(
            $userId,
            'Proposal Sedang Direview',
            'Proposal Anda sedang dalam proses review oleh reviewer.',
            'info'
        );
    }

    public static function notifyProposalApproved($userId)
    {
        NotificationHelper::send(
            $userId,
            'Proposal Disetujui',
            'Selamat! Proposal Anda telah disetujui.',
            'success'
        );
    }

    public static function notifyProposalRejected($userId)
    {
        NotificationHelper::send(
            $userId,
            'Proposal Ditolak',
            'Proposal Anda ditolak. Silakan cek catatan dari reviewer.',
            'warning'
        );
    }
}

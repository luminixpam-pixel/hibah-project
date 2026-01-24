<?php

namespace App\Http\Controllers;

use App\Models\Notification;
use App\Models\User;
use App\Models\Proposal;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use App\Helpers\NotificationHelper;
use Carbon\Carbon;

class NotificationController extends Controller
{
    /**
     * Format sisa waktu:
     * - >= 24 jam => "Tinggal X hari lagi" (pakai diffInDays, bukan ceil biar lebih natural)
     * - <  24 jam => "Tinggal X jam lagi"
     */
    private static function remainingText(Carbon $now, Carbon $deadline, string $tz = 'Asia/Jakarta'): string
    {
        $nowT = $now->copy()->timezone($tz);
        $dlT  = $deadline->copy()->timezone($tz);

        $secondsLeft = $nowT->diffInSeconds($dlT, false);

        if ($secondsLeft <= 0) return 'Tenggat sudah lewat';

        if ($secondsLeft >= 86400) {
            $days = (int) $nowT->diffInDays($dlT, false);
            if ($days < 1) $days = 1;
            return "Tinggal {$days} hari lagi";
        }

        $hours = (int) $nowT->diffInHours($dlT, false);
        if ($hours < 1) $hours = 1;
        return "Tinggal {$hours} jam lagi";
    }

    /**
     * Ambil deadline dari notif.message (biar sesuai yang tampil di bell).
     * Support format:
     *  - "Tenggat penilaian: 24 Jan 2026 05:59 WIB"
     *  - "harus dinilai sebelum 24 Jan 2026 05:59 WIB"
     */
    private static function extractDeadlineFromMessage(?string $message, string $tz = 'Asia/Jakarta'): ?Carbon
    {
        if (!$message) return null;

        $patterns = [
            '/Tenggat\s+penilaian\s*:\s*([0-9]{1,2}\s+[A-Za-z]{3}\s+[0-9]{4}\s+[0-9]{2}:[0-9]{2})\s*WIB/i',
            '/sebelum\s+([0-9]{1,2}\s+[A-Za-z]{3}\s+[0-9]{4}\s+[0-9]{2}:[0-9]{2})\s*WIB/i',
        ];

        foreach ($patterns as $pattern) {
            if (preg_match($pattern, $message, $m)) {
                $dt = trim($m[1]);
                try {
                    return Carbon::createFromFormat('d M Y H:i', $dt, $tz);
                } catch (\Throwable $e) {
                    return null;
                }
            }
        }

        return null;
    }

    /**
     * Generate notif reviewer:
     * 1) "Tugas Review Proposal" (info) selalu ada untuk tiap penugasan aktif
     * 2) "Tenggat review ..." (warning) H-3/H-2/H-1, dan kalau <24 jam jadi jam
     *
     * ✅ IMPORTANT FIX:
     * Saat dedupe, selalu prioritas simpan notif yang is_read = 0 (unread),
     * supaya badge tidak tiba-tiba jadi 0.
     */
    private static function generateDeadlineNotifsForReviewer(int $reviewerId): void
    {
        $tz  = 'Asia/Jakarta';
        $now = Carbon::now($tz);

        $proposals = Proposal::query()
            ->with('reviewers')
            ->whereNotNull('review_deadline')
            ->whereIn('status', ['Dikirim', 'Perlu Direview', 'Sedang Direview'])
            ->whereHas('reviewers', function ($q) use ($reviewerId) {
                $q->where('users.id', $reviewerId);
            })
            ->get();

        foreach ($proposals as $proposal) {
            if (!$proposal->review_deadline) continue;

            $deadline = ($proposal->review_deadline instanceof Carbon)
                ? $proposal->review_deadline->copy()->timezone($tz)
                : Carbon::parse($proposal->review_deadline, $tz);

            $secondsLeft = $now->diffInSeconds($deadline, false);

            // ==============================
            // 1) NOTIF "TUGAS REVIEW" (INFO)
            // ==============================
            if ($deadline->gte($now)) {
                $remainText  = self::remainingText($now, $deadline, $tz);
                $taskTitle   = 'Tugas Review Proposal';
                $taskMessage = 'Anda ditugaskan mereview proposal "' . ($proposal->judul ?? '-') . '". ' .
                    'Tenggat penilaian: ' . $deadline->translatedFormat('d M Y H:i') . ' WIB. ' .
                    $remainText . '.';

                $taskBaseQuery = Notification::where('user_id', $reviewerId)
                    ->where('proposal_id', $proposal->id)
                    ->where('type', 'info')
                    ->where('title', $taskTitle);

                // ✅ pilih yang UNREAD dulu, baru fallback yang mana aja
                $existingTask = (clone $taskBaseQuery)
                    ->where('is_read', false)
                    ->orderBy('id', 'asc')
                    ->first();

                if (!$existingTask) {
                    $existingTask = (clone $taskBaseQuery)
                        ->orderBy('id', 'asc')
                        ->first();
                }

                if ($existingTask) {
                    // ✅ hapus duplikat tapi jangan sampai unread hilang
                    (clone $taskBaseQuery)->where('id', '!=', $existingTask->id)->delete();

                    // update message kalau berubah (tanpa maksa jadi unread)
                    if (($existingTask->message ?? '') !== $taskMessage) {
                        $existingTask->update(['message' => $taskMessage]);
                    }
                } else {
                    Notification::create([
                        'user_id'     => $reviewerId,
                        'proposal_id' => $proposal->id,
                        'title'       => $taskTitle,
                        'message'     => $taskMessage,
                        'type'        => 'info',
                        'is_read'     => false,
                    ]);
                }
            }

            // ==========================================
            // 2) NOTIF "TENGGAT REVIEW" (WARNING H-3/2/1)
            // ==========================================
            $warnBaseQuery = Notification::where('user_id', $reviewerId)
                ->where('proposal_id', $proposal->id)
                ->where('type', 'warning')
                ->where('title', 'like', 'Tenggat review%');

            // ✅ pilih yang UNREAD dulu, baru fallback
            $existingWarning = (clone $warnBaseQuery)
                ->where('is_read', false)
                ->orderBy('id', 'asc')
                ->first();

            if (!$existingWarning) {
                $existingWarning = (clone $warnBaseQuery)
                    ->orderBy('id', 'asc')
                    ->first();
            }

            if ($existingWarning) {
                (clone $warnBaseQuery)->where('id', '!=', $existingWarning->id)->delete();
            }

            // lewat deadline => warning dihapus
            if ($deadline->lt($now)) {
                if ($existingWarning) $existingWarning->delete();
                continue;
            }

            // trigger warning berdasarkan H-3/H-2/H-1 (stabil pakai startOfDay WIB)
            $daysLeft = $now->copy()->startOfDay()->diffInDays($deadline->copy()->startOfDay(), false);

            if (!in_array($daysLeft, [3, 2, 1], true)) {
                if ($existingWarning) $existingWarning->delete();
                continue;
            }

            $remainText = self::remainingText($now, $deadline, $tz);

            // title warning jam kalau <24 jam
            if ($secondsLeft > 0 && $secondsLeft < 86400) {
                $hoursLeft = (int) $now->diffInHours($deadline, false);
                if ($hoursLeft < 1) $hoursLeft = 1;
                $warnTitle = "Tenggat review tinggal {$hoursLeft} jam lagi";
            } else {
                $warnTitle = "Tenggat review {$daysLeft} hari lagi";
            }

            $warnMessage = 'Proposal "' . ($proposal->judul ?? '-') . '" harus dinilai sebelum '
                . $deadline->translatedFormat('d M Y H:i') . ' WIB. '
                . $remainText . '.';

            if ($existingWarning) {
                if ($existingWarning->title !== $warnTitle || $existingWarning->message !== $warnMessage) {
                    $existingWarning->update([
                        'title'   => $warnTitle,
                        'message' => $warnMessage,
                        'type'    => 'warning',
                        // ✅ jangan ubah jadi read; kalau user belum baca, tetep unread
                        // tapi kalau notifnya memang warning dan berubah hari->jam, biar muncul badge lagi:
                        'is_read' => false,
                    ]);
                }
            } else {
                Notification::create([
                    'user_id'     => $reviewerId,
                    'proposal_id' => $proposal->id,
                    'title'       => $warnTitle,
                    'message'     => $warnMessage,
                    'type'        => 'warning',
                    'is_read'     => false,
                ]);
            }
        }
    }

    public function count()
    {
        if (!Auth::check()) {
            return response()->json([
                'count'  => 0,
                'unread' => 0,
                'total'  => 0,
            ]);
        }

        // ✅ FIX: jangan pakai Auth::id() (bisa username), pakai users.id
        $uid = (int) (Auth::user()->id ?? 0);

        if (Auth::user()->role === 'reviewer') {
            try {
                self::generateDeadlineNotifsForReviewer($uid);
            } catch (\Throwable $e) {
                Log::error('deadline notif error (count): ' . $e->getMessage());
            }
        }

        $unread = Notification::where('user_id', $uid)
            ->where('is_read', false)
            ->count();

        $total = Notification::where('user_id', $uid)->count();

        return response()->json([
            'count'  => $unread,
            'unread' => $unread,
            'total'  => $total,
        ]);
    }

    public function fetch()
    {
        if (!Auth::check()) {
            return response()->json([]);
        }

        $user = Auth::user();
        $uid  = (int) ($user->id ?? 0);

        if ($user->role === 'reviewer') {
            try {
                self::generateDeadlineNotifsForReviewer($uid);
            } catch (\Throwable $e) {
                Log::error('deadline notif error (fetch): ' . $e->getMessage());
            }
        }

        $notifications = Notification::where('user_id', $uid)
            ->orderBy('created_at', 'desc')
            ->limit(50)
            ->get();

        // fallback map deadline dari proposal (kalau message gak ada deadline)
        $proposalIds = $notifications->pluck('proposal_id')->filter()->unique()->values();
        $proposalMap = [];

        if ($proposalIds->count() > 0) {
            $proposalMap = Proposal::whereIn('id', $proposalIds->all())
                ->get(['id', 'review_deadline'])
                ->keyBy('id')
                ->all();
        }

        $tz  = 'Asia/Jakarta';
        $now = Carbon::now($tz);

        $notifications->transform(function ($notif) use ($proposalMap, $tz, $now) {
            if (empty($notif->title)) {
                $notif->title = 'Pemberitahuan Sistem';
            }

            // ✅ utama: ambil deadline dari message biar sesuai yang tampil
            $deadline = self::extractDeadlineFromMessage($notif->message, $tz);

            // ✅ fallback: ambil dari proposal.review_deadline
            if (!$deadline && !empty($notif->proposal_id) && isset($proposalMap[$notif->proposal_id])) {
                $p = $proposalMap[$notif->proposal_id];
                if (!empty($p->review_deadline)) {
                    $deadline = ($p->review_deadline instanceof Carbon)
                        ? $p->review_deadline->copy()->timezone($tz)
                        : Carbon::parse($p->review_deadline, $tz);
                }
            }

            if ($deadline) {
                $notif->remaining_text = self::remainingText($now, $deadline, $tz);
                $notif->deadline_iso   = $deadline->toIso8601String();
            }

            return $notif;
        });

        return response()->json($notifications);
    }

    public function markAllAsRead()
    {
        if (!Auth::check()) {
            return response()->json(['status' => 'error', 'message' => 'Unauthorized'], 401);
        }

        // ✅ FIX: jangan pakai Auth::id() (bisa username), pakai users.id
        $uid = (int) (Auth::user()->id ?? 0);

        Notification::where('user_id', $uid)
            ->where('is_read', false)
            ->update(['is_read' => true]);

        return response()->json([
            'status'  => 'success',
            'message' => 'Semua notifikasi telah dibaca'
        ]);
    }

    public function deadlineCheck()
    {
        if (!Auth::check()) {
            return response()->json(['popups' => []]);
        }

        if (Auth::user()->role !== 'reviewer') {
            return response()->json(['popups' => []]);
        }

        // ✅ FIX: jangan pakai Auth::id() (bisa username), pakai users.id
        $uid = (int) (Auth::user()->id ?? 0);

        try {
            self::generateDeadlineNotifsForReviewer($uid);
        } catch (\Throwable $e) {
            Log::error('deadline notif error (deadlineCheck): ' . $e->getMessage());
            return response()->json(['popups' => []]);
        }

        $items = Notification::where('user_id', $uid)
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

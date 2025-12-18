<?php

namespace App\Http\Controllers;

use App\Models\Notification;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Helpers\NotificationHelper;

class NotificationController extends Controller
{
    /**
     * Ambil notifikasi user login (10 terbaru)
     * AJAX
     */
    public function fetch()
    {
        $notifications = Notification::where('user_id', Auth::id())
            ->orderBy('created_at', 'desc')
            ->limit(10)
            ->get();

        return response()->json($notifications);
    }

    /**
     * Tandai semua notifikasi sebagai dibaca
     */
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

    /**
     * Kirim notifikasi ke SEMUA user
     * (ADMIN ONLY)
     */
    public function sendToAll(Request $request)
    {
        if (Auth::user()->role !== 'admin') {
            abort(403);
        }

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

    /**
     * Kirim notifikasi ke USER tertentu
     * (ADMIN ONLY)
     */
    public function sendToUser(Request $request)
    {
        if (Auth::user()->role !== 'admin') {
            abort(403);
        }

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

    /* ================== SYSTEM TRIGGERS ================== */

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

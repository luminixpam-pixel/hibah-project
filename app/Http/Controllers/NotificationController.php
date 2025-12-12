<?php

namespace App\Http\Controllers;

use App\Models\Notification;
use App\Models\User;
use App\Models\Proposal;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Helpers\NotificationHelper;

class NotificationController extends Controller
{
    /**
     * Ambil semua notifikasi user login (terbaru 10)
     */
    public function fetch()
    {
        $user = Auth::user();
        $notifications = $user->notifications()->latest()->take(10)->get();

        return response()->json($notifications);
    }

    /**
     * Tandai semua notifikasi user login sebagai sudah dibaca
     */
    public function markAllAsRead()
    {
        $user = Auth::user();
        NotificationHelper::markAllAsRead($user->id);

        return response()->json([
            'status' => 'success',
            'message' => 'Semua notifikasi telah dibaca'
        ]);
    }

    /**
     * Kirim notifikasi ke semua user (admin action)
     */
    public function sendToAll(Request $request)
    {
        $request->validate([
            'title' => 'required|string|max:255',
            'message' => 'nullable|string',
            'type' => 'nullable|string|in:info,success,warning'
        ]);

        $type = $request->type ?? 'info';
        NotificationHelper::sendToAll($request->title, $request->message, $type);

        return response()->json([
            'status' => 'success',
            'message' => 'Notifikasi dikirim ke semua user'
        ]);
    }

    /**
     * Kirim notifikasi ke user tertentu (admin action)
     */
    public function sendToUser(Request $request)
    {
        $request->validate([
            'user_id' => 'required|exists:users,id',
            'title' => 'required|string|max:255',
            'message' => 'nullable|string',
            'type' => 'nullable|string|in:info,success,warning'
        ]);

        $type = $request->type ?? 'info';
        NotificationHelper::send($request->user_id, $request->title, $request->message, $type);

        return response()->json([
            'status' => 'success',
            'message' => 'Notifikasi dikirim ke user'
        ]);
    }

    /**
     * Trigger notifikasi: Periode upload proposal diperbarui
     * Panggil ini di CalendarController@updatePeriod
     */
    public static function notifyPeriodUpdated()
    {
        $users = User::where('role', 'pengaju')->get();
        foreach ($users as $user) {
            NotificationHelper::send(
                $user->id,
                'Periode Upload Proposal Diperbarui',
                'Periode upload proposal baru sudah diperbarui, silakan cek dashboard.',
                'info'
            );
        }
    }

    /**
     * Trigger notifikasi: Proposal dikirim
     * Panggil ini di ProposalController@store
     */
    public static function notifyProposalSubmitted($userId)
    {
        NotificationHelper::send(
            $userId,
            'Proposal Dikirim',
            'Proposal Anda telah berhasil dikirim dan menunggu review.',
            'success'
        );
    }

    /**
     * Trigger notifikasi: Proposal sedang direview
     * Panggil ini di ProposalController@moveToPerluDireview atau assignReviewer
     */
    public static function notifyProposalUnderReview($userId)
    {
        NotificationHelper::send(
            $userId,
            'Proposal Sedang Direview',
            'Proposal Anda sedang dalam proses review.',
            'info'
        );
    }

    /**
     * Trigger notifikasi: Proposal disetujui
     * Panggil ini di ProposalController setelah approve
     */
    public static function notifyProposalApproved($userId)
    {
        NotificationHelper::send(
            $userId,
            'Proposal Disetujui',
            'Selamat! Proposal Anda telah disetujui.',
            'success'
        );
    }

    /**
     * Trigger notifikasi: Proposal ditolak
     * Panggil ini di ProposalController setelah reject
     */
    public static function notifyProposalRejected($userId)
    {
        NotificationHelper::send(
            $userId,
            'Proposal Ditolak',
            'Maaf, proposal Anda ditolak. Silakan cek catatan review.',
            'warning'
        );
    }
}

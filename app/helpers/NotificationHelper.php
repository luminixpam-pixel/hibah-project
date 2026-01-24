<?php

namespace App\Helpers;

use App\Models\User;
use App\Models\Notification;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Schema;

class NotificationHelper
{
    // Kirim ke semua user (by role)
    public static function sendToAll($title, $message = null, $type = 'info', $role = null, $proposalId = null)
    {
        if (!Schema::hasTable('notifications')) return;

        $message = $message ?: 'Anda memiliki notifikasi baru.';

        $query = User::query();
        if ($role) {
            $query->where('role', $role);
        }

        foreach ($query->get() as $user) {
            Notification::create([
                'user_id'     => $user->id,
                'proposal_id' => $proposalId,
                'title'       => $title,
                'message'     => $message,
                'type'        => $type,
                'is_read'     => false,
            ]);
        }
    }

    // Kirim ke satu user
    public static function send($userId, $title, $message = null, $type = 'info', $proposalId = null)
    {
        if (!Schema::hasTable('notifications')) return;

        try {
            Notification::create([
                'user_id'     => $userId,
                'proposal_id' => $proposalId,
                'title'       => $title,
                'message'     => $message,
                'type'        => $type,
                'is_read'     => false,
            ]);
        } catch (\Exception $e) {
            Log::error('Notif error: '.$e->getMessage());
        }
    }

    // Tandai semua notifikasi dibaca
    public static function markAllAsRead($userId)
    {
        if (!Schema::hasTable('notifications')) return;

        Notification::where('user_id', $userId)
            ->update(['is_read' => true]);
    }
}

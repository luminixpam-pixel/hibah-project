<?php

namespace App\Helpers;

use App\Models\User;
use App\Models\Notification;

class NotificationHelper
{
    // Kirim notifikasi ke semua user
    public static function sendToAll($title, $message = null, $type = 'info', $role = null)
    {
        $query = User::query();
        if ($role) $query->where('role', $role);
        $users = $query->get();

        foreach ($users as $user) {
            Notification::create([
                'user_id' => $user->id,
                'title' => $title,
                'message' => $message,
                'type' => $type,
                'is_read' => false,
            ]);
        }
    }

    // Kirim notifikasi ke user tertentu
    public static function send($userId, $title, $message = null, $type = 'info')
    {
        Notification::create([
            'user_id' => $userId,
            'title' => $title,
            'message' => $message,
            'type' => $type,
            'is_read' => false,
        ]);
    }

    // Tandai semua notifikasi user sebagai sudah dibaca
    public static function markAllAsRead($userId)
    {
        Notification::where('user_id', $userId)->update(['is_read' => true]);
    }
}

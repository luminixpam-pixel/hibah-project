<?php

namespace App\Helpers;

use App\Models\Notification;

class NotificationHelper
{
    /**
     * Buat notifikasi baru untuk user
     *
     * @param int $userId
     * @param string $title
     * @param string|null $message
     * @param string $type
     * @return Notification
     */
    public static function send(int $userId, string $title, ?string $message = null, string $type = 'info')
    {
        return Notification::create([
            'user_id' => $userId,
            'title' => $title,
            'message' => $message,
            'type' => $type,
        ]);
    }
}

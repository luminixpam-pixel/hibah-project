<?php

use App\Models\Notification;

/**
 * Kirim notifikasi ke user tertentu
 *
 * @param int $userId
 * @param string $title
 * @param string|null $message
 * @param string $type
 * @return \App\Models\Notification
 */
function sendNotification($userId, $title, $message = null, $type = 'info')
{
    return Notification::create([
        'user_id' => $userId,
        'title' => $title,
        'message' => $message,
        'type' => $type,
    ]);

    if (!function_exists('formatRupiah')) {
    function formatRupiah($nominal)
    {
        return 'Rp ' . number_format($nominal, 0, ',', '.');
    }
}
}

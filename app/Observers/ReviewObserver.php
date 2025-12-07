<?php

namespace App\Observers;

use App\Models\Review;
use App\Models\Notification;

class ReviewObserver
{
    /**
     * Dipanggil saat review pertama kali dibuat.
     */
    public function created(Review $review): void
    {
        // pastikan relasi proposal sudah dimuat
        $review->loadMissing('proposal');

        // kalau proposal tidak ada, hentikan supaya tidak error
        if (!$review->proposal) {
            return;
        }

        // Notifikasi ke pengaju saat review dibuat
        Notification::create([
            'user_id' => $review->proposal->user_id,
            'title'   => "Proposal Anda sedang direview",
            'type'    => 'info',
            'is_read' => false,
        ]);
    }

    /**
     * Dipanggil saat review di-update (misalnya status diubah).
     */
    public function updated(Review $review): void
    {
        // pastikan relasi proposal sudah dimuat
        $review->loadMissing('proposal');

        if (!$review->proposal) {
            return;
        }

        if ($review->status === 'ditolak') {
            Notification::create([
                'user_id' => $review->proposal->user_id,
                'title'   => "Proposal Anda ditolak ❌",
                'type'    => 'warning',
                'is_read' => false,
            ]);
        } elseif ($review->status === 'disetujui') {
            Notification::create([
                'user_id' => $review->proposal->user_id,
                'title'   => "Proposal Anda disetujui 🎉",
                'type'    => 'success',
                'is_read' => false,
            ]);
        }
    }
}

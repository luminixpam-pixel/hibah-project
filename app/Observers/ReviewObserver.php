<?php

// app/Observers/ReviewObserver.php
namespace App\Observers;

use App\Models\Review;
use App\Models\Notification;

class ReviewObserver
{
    public function created(Review $review)
    {
        // Notifikasi ke pengaju saat review dibuat
        Notification::create([
            'user_id' => $review->proposal->user_id,
            'title' => "Proposal Anda sedang direview",
            'type' => 'info',
        ]);
    }

    public function updated(Review $review)
    {
        if ($review->status === 'ditolak') {
            Notification::create([
                'user_id' => $review->proposal->user_id,
                'title' => "Proposal Anda ditolak ❌",
                'type' => 'warning',
            ]);
        } elseif ($review->status === 'disetujui') {
            Notification::create([
                'user_id' => $review->proposal->user_id,
                'title' => "Proposal Anda disetujui 🎉",
                'type' => 'success',
            ]);
        }
    }
}

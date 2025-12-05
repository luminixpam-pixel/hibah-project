<?php

namespace App\Http\Controllers;
use App\Models\Notification;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class NotificationController extends Controller
{
    public function fetch()
    {
        $user = Auth::user();

        // Contoh ambil notifikasi user, sesuaikan dengan tabel/struktur kamu
        $notifications = $user->notifications()->latest()->get();

        return response()->json($notifications);
    }
}

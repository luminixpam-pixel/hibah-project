<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\HibahPeriod;
use App\Helpers\NotificationHelper;
use App\Http\Controllers\NotificationController; // import class NotificationController

class CalendarController extends Controller
{
    /**
     * Menampilkan halaman kalender + periode hibah dari DB.
     */
    public function index()
    {
        $hibahPeriod = HibahPeriod::first();
        return view('monitoring-data', compact('hibahPeriod'));
    }

    /**
     * Hanya ADMIN yang boleh menyimpan / update periode hibah.
     * Juga kirim notifikasi ke semua pengaju.
     */
    public function updatePeriod(Request $request)
    {
        $validated = $request->validate([
            'start_date' => ['required', 'date'],
            'end_date'   => ['required', 'date', 'after_or_equal:start_date'],
        ]);

        $existing = HibahPeriod::first();

        if ($existing) {
            $existing->update($validated);
        } else {
            HibahPeriod::create($validated);
        }

        // 🔔 Kirim notifikasi ke semua pengaju
        NotificationController::notifyPeriodUpdated();

        return redirect()
            ->route('monitoring.kalender')
            ->with('success', 'Periode pengajuan hibah berhasil disimpan dan notifikasi telah dikirim.');
    }
}

<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\HibahPeriod;

class CalendarController extends Controller
{
    /**
     * Menampilkan halaman kalender + periode hibah dari DB.
     */
    public function index()
    {
        // ambil 1 periode aktif (kita pakai row pertama saja)
        $hibahPeriod = HibahPeriod::first();

        return view('monitoring-data', compact('hibahPeriod'));
    }

    /**
     * Hanya ADMIN yang boleh menyimpan / update periode hibah.
     */
    public function updatePeriod(Request $request)
    {
        $validated = $request->validate([
            'start_date' => ['required', 'date'],
            'end_date'   => ['required', 'date', 'after_or_equal:start_date'],
        ]);

        // kalau sudah ada row, update; kalau belum, create
        $existing = HibahPeriod::first();

        if ($existing) {
            $existing->update($validated);
        } else {
            HibahPeriod::create($validated);
        }

        return redirect()
            ->route('monitoring.kalender')
            ->with('success', 'Periode pengajuan hibah berhasil disimpan.');
    }
}

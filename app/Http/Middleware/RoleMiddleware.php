<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Support\Facades\Auth;

class RoleMiddleware
{
    public function handle($request, Closure $next, ...$roles)
    {
        if (!Auth::check()) {
            return redirect('/login')->with('error', 'Silakan login dahulu');
        }

        // Ambil role user dari database
        $userRole = Auth::user()->role;

        // Cek apakah role user ada di daftar middleware (admin, reviewer)
        if (!in_array($userRole, $roles)) {
            abort(403, 'Akses ditolak.');
        }

        return $next($request);
    }
}

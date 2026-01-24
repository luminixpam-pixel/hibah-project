<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
// TAMBAHKAN 3 BARIS DI BAWAH INI:
use Illuminate\Support\Facades\View;
use App\Models\Fakultas;
use App\Models\User;
use App\Models\Template;
use App\Models\Notification;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Schema;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        // Gunakan View Composer
        view()->composer('*', function ($view) {

            // 1. Cek tabel fakultas
            if (Schema::hasTable('fakultas')) {
                $view->with('list_fakultas', Fakultas::all());
            }

            // 2. Cek tabel users
            if (Schema::hasTable('users')) {
                $view->with('all_dosen', User::whereIn('role', ['reviewer', 'pengaju'])->get());
            }

            // 3. Cek tabel templates
            if (Schema::hasTable('templates')) {
                $view->with('template_kemajuan', Template::where('jenis', 'laporan_kemajuan')->first());
                $view->with('template_akhir', Template::where('jenis', 'laporan_akhir')->first());
            }

            // 4. Notifikasi (badge bell)
            $notifUnread = 0;
            $notifTotal = 0;

            if (Schema::hasTable('notifications') && Auth::check()) {
                // ✅ PENTING: pakai users.id (angka), BUKAN Auth::id() (username)
                $uid = Auth::user()->id;

                $notifUnread = Notification::where('user_id', $uid)
                    ->where('is_read', false)
                    ->count();

                $notifTotal = Notification::where('user_id', $uid)->count();
            }

            $view->with('notif_unread_count', $notifUnread);
            $view->with('notif_total_count', $notifTotal);
        });
    }
}

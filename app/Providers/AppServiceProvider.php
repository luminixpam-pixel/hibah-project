<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
// TAMBAHKAN 3 BARIS DI BAWAH INI:
use Illuminate\Support\Facades\View;
use App\Models\Fakultas;
use App\Models\User;
use App\Models\Template;

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
        // Menggunakan View Composer agar data tersedia di semua halaman (tanda *)
        View::composer('*', function ($view) {
            $view->with('list_fakultas', Fakultas::all());
            $view->with('all_dosen', User::whereIn('role', ['reviewer', 'pengaju'])->get());
        });

        View::composer('*', function ($view) {
        $view->with('list_fakultas', \App\Models\Fakultas::all());
        $view->with('all_dosen', \App\Models\User::whereIn('role', ['reviewer', 'pengaju'])->get());

        // Tambahkan ini agar Template selalu terbawa di semua halaman
        $view->with('template_kemajuan', Template::where('jenis', 'laporan_kemajuan')->first());
        $view->with('template_akhir', Template::where('jenis', 'laporan_akhir')->first());
    });
    }
}

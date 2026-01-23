<?php

namespace App\Providers;

use App\Actions\Fortify\CreateNewUser;
use App\Actions\Fortify\ResetUserPassword;
use App\Actions\Fortify\UpdateUserPassword;
use App\Actions\Fortify\UpdateUserProfileInformation;
use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth; // Tambahkan ini
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Str;
use Laravel\Fortify\Actions\RedirectIfTwoFactorAuthenticatable;
use Laravel\Fortify\Fortify;
use Laravel\Fortify\Http\Requests\LoginRequest; // Tambahkan ini
use App\Models\User;
use LdapRecord\Container;
use Illuminate\Support\Facades\Hash;


class FortifyServiceProvider extends ServiceProvider
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
        // 1. Beritahu Fortify secara global bahwa kita menggunakan 'username'
        Fortify::username('username');

        Fortify::createUsersUsing(CreateNewUser::class);
        Fortify::updateUserProfileInformationUsing(UpdateUserProfileInformation::class);
        Fortify::updateUserPasswordsUsing(UpdateUserPassword::class);
        Fortify::resetUserPasswordsUsing(ResetUserPassword::class);
        Fortify::redirectUserForTwoFactorAuthenticationUsing(RedirectIfTwoFactorAuthenticatable::class);

        // 2. FIX UTAMA: Paksa validasi Fortify untuk menggunakan 'username', bukan 'email'
        $this->app->bind(LoginRequest::class, function ($app) {
            return new class extends LoginRequest {
                public function rules(): array {
                    return [
                        'username' => 'required|string',
                        'password' => 'required|string',
                    ];
                }
            };
        });

        // 3. LOGIKA LOGIN LDAP YARSI
 Fortify::authenticateUsing(function ($request) {
    // 1. JALUR ADMIN LOKAL
    if ($request->username === 'admin_utama') {
        $user = \App\Models\User::where('username', 'admin_utama')->first();
        if ($user && \Illuminate\Support\Facades\Hash::check($request->password, $user->password)) {
            return $user;
        }
    }

    // 2. JALUR LDAP YARSI
    try {
        $connection = \LdapRecord\Container::getConnection('default');

        // Cari user
        $ldapEntry = $connection->query()
                        ->in('dc=yarsi,dc=ac,dc=id')
                        ->where('uid', '=', $request->username)
                        ->first();

        if ($ldapEntry) {
            // PERBAIKAN ERROR: Cek apakah $ldapEntry objek atau array
            $userDn = is_array($ldapEntry) ? $ldapEntry['dn'][0] : $ldapEntry->getDn();

            // Verifikasi Password
            if ($connection->auth()->attempt($userDn, $request->password)) {

                // Ambil atribut (handle jika objek atau array)
                $displayName = is_array($ldapEntry) ? ($ldapEntry['displayname'][0] ?? $request->username) : $ldapEntry->getFirstAttribute('displayname');
                $description = is_array($ldapEntry) ? ($ldapEntry['description'][0] ?? null) : $ldapEntry->getFirstAttribute('description');
                $title = is_array($ldapEntry) ? ($ldapEntry['title'][0] ?? null) : $ldapEntry->getFirstAttribute('title');

                return \App\Models\User::updateOrCreate(
                    ['username' => $request->username],
                    [
                        'name' => $displayName,
                        'password' => \Illuminate\Support\Facades\Hash::make($request->password),
                        'role' => 'pengaju',
                        'npm_nik' => $description,
                        'tipe_user' => $title,
                    ]
                );
            }
        }
    } catch (\Exception $e) {
        // Log error jika diperlukan: \Log::error($e->getMessage());
    }

    return null;
});

        RateLimiter::for('login', function (Request $request) {
            // Gunakan username untuk pembatasan login (throttling)
            $throttleKey = Str::transliterate(Str::lower($request->username).'|'.$request->ip());

            return Limit::perMinute(5)->by($throttleKey);
        });

        RateLimiter::for('two-factor', function (Request $request) {
            return Limit::perMinute(5)->by($request->session()->get('login.id'));
        });
    }
}

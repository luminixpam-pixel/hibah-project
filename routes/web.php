<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\AuthController;

//home
Route::get('/', function () {
    return view('auth.custom-login');
});

//login
Route::get('/login', [AuthController::class, 'showLoginForm'])->name('login');
Route::post('/login', [AuthController::class, 'login'])->name('login.post');
Route::post('/logout', [AuthController::class, 'logout'])->name('logout');

//lupa password
Route::get('/password/reset', function () {
    return 'Fitur lupa password akan tersedia melalui sistem LDAP Universitas.';
})->name('password.request');


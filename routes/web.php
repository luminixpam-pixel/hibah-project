<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\ScheduleController;

Route::get('/', function () {
    return view('welcome');
});

Route::controller(ScheduleController::class)->group(function () {
    Route::get('/fullcalender', 'index')->name('schedule.index');
});

<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class AdminController extends Controller
{
    public function hasilReview()
    {
        $reviews = []; // contoh
        return view('admin.hasil-review', compact('reviews'));
    }

    public function calendar()
    {
        return view('admin.calendar');
    }
}

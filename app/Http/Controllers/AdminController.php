<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Review;

class AdminController extends Controller
{
    // Halaman daftar seluruh hasil review
    public function hasilReview()
    {
        // Ambil semua review + relasi reviewer dan proposal
        $reviews = Review::with(['proposal', 'reviewer'])->get();

        return view('admin.hasil-review', compact('reviews'));
    }
}


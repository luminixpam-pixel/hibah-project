@extends('layouts.app')

@section('content')
<style>
    body {
        background-color: #f8f9fa;
    }

    .navbar-custom {
        background-color: #26a65b;
        color: white;
    }

    .navbar-custom .navbar-brand,
    .navbar-custom .nav-link,
    .navbar-custom .navbar-text {
        color: white;
    }

    .card-stat {
        border: none;
        border-radius: 8px;
        box-shadow: 0 1px 4px rgba(0, 0, 0, 0.1);
        background: white;
        margin-bottom: 15px;
    }

    .profile-section {
        margin-top: 30px;
        padding: 20px;
        background: #f9fdf9;
        border-radius: 8px;
    }

    .proposal-btn {
        background-color: #26a65b;
        color: white;
        border: none;
        padding: 8px 18px;
        border-radius: 5px;
    }

    .proposal-btn:hover {
        background-color: #1c8f4b;
    }
</style>

<nav class="navbar navbar-expand-lg navbar-custom px-4">
    <a class="navbar-brand fw-bold" href="#">Dashboard</a>
    <div class="collapse navbar-collapse">
        <ul class="navbar-nav me-auto mb-2 mb-lg-0">
            <li class="nav-item dropdown">
                <a class="nav-link dropdown-toggle" href="#" id="menuDashboard" role="button" data-bs-toggle="dropdown"
                    aria-expanded="false">
                    Dashboard
                </a>
                <ul class="dropdown-menu">
                    <li><a class="dropdown-item" href="#">Beranda</a></li>
                </ul>
            </li>
            <li class="nav-item dropdown">
                <a class="nav-link dropdown-toggle" href="#" id="menuData" role="button" data-bs-toggle="dropdown"
                    aria-expanded="false">
                    Monitoring & Data
                </a>
                <ul class="dropdown-menu">
                    <li><a class="dropdown-item" href="#">Data Usulan</a></li>
                    <li><a class="dropdown-item" href="#">Review</a></li>
                </ul>
            </li>
        </ul>
        <div class="d-flex align-items-center">
            <i class="bi bi-bell me-3"></i>
            <span><strong>{{ Auth::user()->name ?? 'nama pengguna' }}</strong></span>
        </div>
    </div>
</nav>

<div class="container mt-4">
    <div class="d-flex justify-content-end mb-3">
        <button class="proposal-btn">Unggah Proposal</button>
    </div>

    {{-- ====================== ROW 1 ====================== --}}
    <div class="row g-3">

        <div class="col-md-3">
            <div class="card-stat p-3">
                <h6>Daftar Proposal</h6>
                <p class="mb-0 fw-bold">25</p>
                <i class="bi bi-send"></i>
            </div>
        </div>

        <div class="col-md-3">
            <div class="card-stat p-3">
                <h6>Proposal Disetujui</h6>
                <p class="mb-0 fw-bold">25</p>
                <i class="bi bi-check-circle"></i>
            </div>
        </div>

        <div class="col-md-3">
            <div class="card-stat p-3">
                <h6>Proposal Ditolak</h6>
                <p class="mb-0 fw-bold">0</p>
                <i class="bi bi-x-circle"></i>
            </div>
        </div>

        <div class="col-md-3">
            <div class="card-stat p-3">
                <h6>Hasil Review</h6>
                <p class="mb-0 fw-bold">19</p>
                <i class="bi bi-pencil-square"></i>
            </div>
        </div>

    </div>

    {{-- ====================== ROW 2 ====================== --}}
    <div class="row g-3 mt-2">

        <div class="col-md-3">
            <div class="card-stat p-3">
                <h6>Proposal Direvisi</h6>
                <p class="mb-0 fw-bold">0</p>
                <i class="bi bi-question-circle"></i>
            </div>
        </div>

        {{-- ====================== BLOCKED FOR PENGAJU ====================== --}}
        @if(Auth::user()->role === 'admin' || Auth::user()->role === 'reviewer')

        <div class="col-md-3">
            <div class="card-stat p-3">
                <h6>Proposal Perlu Direview</h6>
                <p class="mb-0 fw-bold">7</p>
                <i class="bi bi-layers"></i>
            </div>
        </div>

        <div class="col-md-3">
            <div class="card-stat p-3">
                <h6>Proposal Sedang Direview</h6>
                <p class="mb-0 fw-bold">7</p>
                <i class="bi bi-pen"></i>
            </div>
        </div>

        @endif
        {{-- ====================== END BLOCK ====================== --}}

        <div class="col-md-3">
            <div class="card-stat p-3">
                <h6>Review Selesai</h6>
                <p class="mb-0 fw-bold">7</p>
                <i class="bi bi-file-earmark-check"></i>
            </div>
        </div>

    </div>

    <div class="profile-section">
        <h6><strong>Profil Pengguna - Anda login sebagai [{{ Auth::user()->role ?? 'Role' }}]</strong></h6>
        <table class="mt-2">
            <tr><td>Nama Lengkap</td><td>:</td><td>{{ Auth::user()->name ?? '-' }}</td></tr>
            <tr><td>NIDN / NIP</td><td>:</td><td>{{ Auth::user()->nidn ?? '-' }}</td></tr>
            <tr><td>Email</td><td>:</td><td>{{ Auth::user()->email ?? '-' }}</td></tr>
            <tr><td>Nomor Telepon</td><td>:</td><td>{{ Auth::user()->no_telepon ?? '-' }}</td></tr>
            <tr><td>Fakultas</td><td>:</td><td>{{ Auth::user()->fakultas ?? '-' }}</td></tr>
            <tr><td>Program Studi</td><td>:</td><td>{{ Auth::user()->program_studi ?? '-' }}</td></tr>
            <tr><td>Jabatan / Posisi</td><td>:</td><td>{{ Auth::user()->jabatan ?? '-' }}</td></tr>
        </table>
    </div>
</div>
@endsection

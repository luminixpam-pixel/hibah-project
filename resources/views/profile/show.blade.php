@extends('layouts.app')

@section('content')
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">
<style>
    @import url('https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@300;400;500;600;700&display=swap');

    .profile-wrapper {
        font-family: 'Plus Jakarta Sans', sans-serif;
        background-color: #f8fafc;
        min-height: 100vh;
        padding-top: 40px;
    }

    .glass-card {
        background: white;
        border-radius: 20px;
        border: 1px solid #e2e8f0;
        box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1);
        overflow: hidden;
    }

    .profile-sidebar {
        background: #1e293b;
        color: white;
        padding: 40px 30px;
        height: 100%;
    }

    .profile-main-content {
        padding: 40px;
    }

    .label-minimal {
        font-size: 0.7rem;
        font-weight: 700;
        color: #64748b;
        text-transform: uppercase;
        letter-spacing: 1px;
        margin-bottom: 4px;
    }

    .value-minimal {
        font-size: 1rem;
        font-weight: 600;
        color: #1e293b;
        margin-bottom: 24px;
        border-bottom: 1px solid #f1f5f9;
        padding-bottom: 12px;
    }

    .role-badge {
        display: inline-block;
        background: rgba(255, 255, 255, 0.1);
        padding: 6px 16px;
        border-radius: 8px;
        font-size: 0.8rem;
        font-weight: 600;
        margin-top: 10px;
    }

    .btn-edit-profile {
        background: #0d6efd;
        color: white;
        border: none;
        padding: 12px 24px;
        border-radius: 12px;
        font-weight: 600;
        transition: all 0.3s;
    }

    /* FIX MODAL REDUP: Menghilangkan backdrop standar Bootstrap yang sering bug */
    .modal-backdrop {
        display: none !important;
    }

    /* Membuat backdrop manual yang melekat pada modal agar tetap cerah */
    #adminEditModal {
        background: rgba(0, 0, 0, 0.5); /* Hitam transparan tipis */
        backdrop-filter: blur(4px);
    }

    .modal-content-popup {
        border-radius: 20px !important;
        border: none !important;
        box-shadow: 0 20px 40px rgba(0,0,0,0.2) !important;
        background: white !important; /* Memastikan modal tetap putih bersih */
    }
</style>

<div class="profile-wrapper">
    <div class="container">
        <div class="glass-card shadow-lg border-0">
            <div class="row g-0">

                {{-- Sisi Kiri: Ringkasan --}}
                <div class="col-lg-4 profile-sidebar d-flex flex-column justify-content-center text-center text-lg-start">
                    <h5 class="text-white-50 mb-1" style="font-size: 0.9rem;">Selamat Datang,</h5>
                    <h2 class="fw-bold mb-3 text-white">{{ $user->name }}</h2>
                    <div>
                        <span class="role-badge">
                            <i class="bi bi-shield-lock me-2"></i>{{ strtoupper($user->role) }}
                        </span>
                    </div>
                    <div class="mt-4 pt-4 border-top border-secondary">
                        <p class="small text-white-50 mb-0">Terdaftar sebagai:</p>
                        <p class="fw-medium text-white">{{ $user->email }}</p>
                    </div>
                </div>

                {{-- Sisi Kanan: Detail Informasi --}}
                <div class="col-lg-8 profile-main-content bg-white">
                    <div class="d-flex justify-content-between align-items-center mb-5">
                        <h4 class="fw-bold m-0 text-dark">Informasi Akun</h4>
                        <button type="button" class="btn btn-edit-profile" data-bs-toggle="modal" data-bs-target="#adminEditModal">
                            <i class="bi bi-pencil-square me-2"></i>Edit Profile
                        </button>
                    </div>

                    <div class="row">
                        <div class="col-md-6">
                            <p class="label-minimal">Username Akun</p>
                            <p class="value-minimal">{{ $user->username }}</p>
                        </div>
                        <div class="col-md-6">
                            <p class="label-minimal">NIDN / NIP</p>
                            <p class="value-minimal">{{ $user->nidn ?? 'Belum diisi' }}</p>
                        </div>
                        <div class="col-md-12">
                            <p class="label-minimal">Unit Kerja / Fakultas</p>
                            <p class="value-minimal text-primary">{{ $user->fakultas ?? 'Belum ditentukan' }}</p>
                        </div>
                        <div class="col-md-12">
                            <p class="label-minimal">Status Autentikasi</p>
                            <p class="value-minimal">
                                <i class="bi bi-check-circle-fill text-success me-2"></i>
                                {{ $user->password ? 'Akun Lokal Terverifikasi' : 'Login via SSO YARSI' }}
                            </p>
                        </div>
                    </div>

                    <div class="mt-4">
                        <a href="{{ route('dashboard') }}" class="text-decoration-none text-muted small fw-bold">
                            <i class="bi bi-arrow-left me-1"></i> Kembali ke Dashboard
                        </a>
                    </div>
                </div>

            </div>
        </div>
    </div>
</div>
{{-- Modal Edit - Logika Pemisahan Admin vs User --}}
<div class="modal fade" id="adminEditModal" tabindex="-1" aria-hidden="true" data-bs-backdrop="false">
    <div class="modal-dialog modal-dialog-centered" style="max-width: 450px;">
        <div class="modal-content modal-content-popup">
            <div class="modal-header border-0 p-4 pb-0">
                <h5 class="fw-bold m-0">
                    <i class="bi {{ Auth::user()->role === 'admin' ? 'bi-shield-lock' : 'bi-person-gear' }} me-2"></i>
                    {{ Auth::user()->role === 'admin' ? 'Pengaturan Akun Admin' : 'Perbarui Profil' }}
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
<form action="{{ route('admin.user.update', $user->id) }}" method="POST">
    @csrf
    @method('PUT')
    <div class="modal-body p-4">

        {{-- Field Email (Bisa diedit keduanya) --}}
        <div class="mb-3">
            <label class="label-minimal">Email Aktif</label>
            <input type="email" name="email" class="form-control bg-light border-0 py-3" style="border-radius: 10px;" value="{{ $user->email }}" required>
        </div>

        @if(Auth::user()->role === 'admin')
            {{-- Field Khusus Admin: Password --}}
            <div class="mb-3">
                <label class="label-minimal text-danger">Ganti Password Baru</label>
                <input type="password" name="password" class="form-control bg-light border-0 py-3" style="border-radius: 10px;" placeholder="Isi jika ingin ganti">
            </div>
        @else
            {{-- Field Khusus User: NIDN & Fakultas --}}
            <div class="mb-3">
                <label class="label-minimal">NIDN / NIP</label>
                <input type="text" name="nidn" class="form-control bg-light border-0 py-3" style="border-radius: 10px;" value="{{ $user->nidn }}" required>
            </div>
            <div class="mb-3">
                <label class="label-minimal">Fakultas</label>
                <select name="fakultas" class="form-select bg-light border-0 py-3" style="border-radius: 10px;" required>
                    <option value="">-- Pilih Fakultas --</option>
                    @foreach(($list_fakultas ?? []) as $f)
                        <option value="{{ $f->nama_fakultas }}" {{ $user->fakultas == $f->nama_fakultas ? 'selected' : '' }}>
                            {{ $f->nama_fakultas }}
                        </option>
                    @endforeach
                </select>
            </div>
        @endif

        <button type="submit" class="btn btn-primary w-100 py-3 fw-bold shadow-sm" style="border-radius: 12px;">
            Simpan Perubahan
        </button>
    </div>
</form>
        </div>
    </div>
</div>

@endsection

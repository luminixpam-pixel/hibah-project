@extends('layouts.app')

@section('content')
<div class="container mt-4  ">

    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
             <button class="btn btn-success">
        <i class="bi bi-upload"></i> Unggah Proposal
    </button>
        </div>
        <div class="d-flex align-items-center gap-3">
        </div>
    </div>

    <!-- Statistik Proposal -->
    <div class="row g-3 mb-4">
        <div class="col-md-3">
            <div class="card text-center p-3">
                <h6>Proposal Dikirim</h6>
                <h4>25</h4>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card text-center p-3">
                <h6>Proposal Disetujui</h6>
                <h4>25</h4>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card text-center p-3">
                <h6>Proposal Ditolak</h6>
                <h4>0</h4>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card text-center p-3">
                <h6>Proposal Direvisi</h6>
                <h4>0</h4>
            </div>
        </div>
    </div>

    <!-- Review Section -->
    <div class="row g-3 mb-4">
        <div class="col-md-3">
            <div class="card text-center p-3">
                <h6>Proposal Perlu Direview</h6>
                <h4>7</h4>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card text-center p-3">
                <h6>Proposal Sedang Direview</h6>
                <h4>7</h4>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card text-center p-3">
                <h6>Hasil Review</h6>
                <h4>19</h4>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card text-center p-3">
                <h6>Review Selesai</h6>
                <h4>7</h4>
            </div>
        </div>
    </div>

    <!-- Profil Pengguna -->
    <div class="card p-4">
        <h5 class="mb-3">Profil Pengguna - Anda login sebagai <b>{{ $user->role ?? 'Role' }}</b></h5>
        <p><strong>Nama Lengkap:</strong> {{ $user->name ?? '-' }}</p>
        <p><strong>NIDN / NIP:</strong> {{ $user->nidn ?? '-' }}</p>
        <p><strong>Email:</strong> {{ $user->email ?? '-' }}</p>
        <p><strong>Nomor Telepon:</strong> {{ $user->no_telepon ?? '-' }}</p>
        <p><strong>Fakultas:</strong> {{ $user->fakultas ?? '-' }}</p>
        <p><strong>Program Studi:</strong> {{ $user->program_studi ?? '-' }}</p>
        <p><strong>Jabatan / Posisi:</strong> {{ $user->jabatan ?? '-' }}</p>
    </div>
</div>
@endsection

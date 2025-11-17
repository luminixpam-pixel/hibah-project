@extends('layouts.app')

@section('title', 'Profil Pengguna')

@section('content')
<div class="card shadow-sm p-4" style="max-width: 650px; margin:auto;">
    <h4 class="fw-bold mb-4">Profil Pengguna</h4>

    <p class="text-muted">
        Anda login sebagai <strong>{{ $user->role ?? 'User' }}</strong>
    </p>

    <div class="mb-3"><strong>Nama Lengkap:</strong> {{ $user->name }}</div>
    <div class="mb-3"><strong>NIDN / NIP:</strong> {{ $user->nidn ?? '-' }}</div>
    <div class="mb-3"><strong>Email:</strong> {{ $user->email }}</div>
    <div class="mb-3"><strong>Nomor Telepon:</strong> {{ $user->phone ?? '-' }}</div>
    <div class="mb-3"><strong>Fakultas:</strong> {{ $user->fakultas ?? '-' }}</div>
    <div class="mb-3"><strong>Program Studi:</strong> {{ $user->prodi ?? '-' }}</div>
    <div class="mb-3"><strong>Jabatan / Posisi:</strong> {{ $user->jabatan ?? '-' }}</div>

    <a href="{{ route('profile.edit') }}" class="btn btn-success mt-3">
        <i class="bi bi-pencil"></i> Edit Profil
    </a>

    <a href="{{ route('dashboard') }}" class="btn btn-danger mt-3">
        <i class="bi bi-arrow-left-circle"></i> Kembali ke Dashboard
    </a>



</div>
@endsection

@extends('layouts.app')

@section('title', 'Edit Profil')

@section('content')
<div class="card shadow-sm p-4" style="max-width: 650px; margin:auto;">
    <h4 class="fw-bold mb-4">Edit Profil</h4>

    <form action="{{ route('profile.update') }}" method="POST">
        @csrf

        <div class="mb-3">
            <label>Nama Lengkap</label>
            <input type="text" name="name" class="form-control"
                   value="{{ $user->name }}" required>
        </div>

        <div class="mb-3">
            <label>NIDN / NIP</label>
            <input type="text" name="nidn" class="form-control"
                   value="{{ $user->nidn }}">
        </div>

        <div class="mb-3">
            <label>Email</label>
            <input type="email" name="email" class="form-control"
                   value="{{ $user->email }}" required>
        </div>

        <div class="mb-3">
            <label>Nomor Telepon</label>
            <input type="text" name="phone" class="form-control"
                   value="{{ $user->phone }}">
        </div>

        <div class="mb-3">
            <label class="fw-bold text-secondary small">Fakultas</label>
            <select name="fakultas" class="form-select shadow-sm" required>
                <option value="" disabled>-- Pilih Fakultas --</option>
                @foreach($list_fakultas as $f)
                    <option value="{{ $f->nama_fakultas }}"
                        {{ $user->fakultas == $f->nama_fakultas ? 'selected' : '' }}>
                        {{ $f->nama_fakultas }}
                    </option>
                @endforeach
            </select>
        </div>

        <div class="mb-3">
            <label>Program Studi</label>
            <input type="text" name="prodi" class="form-control"
                   value="{{ $user->prodi }}">
        </div>

        <div class="mb-3">
            <label>Jabatan / Posisi</label>
            <input type="text" name="jabatan" class="form-control"
                   value="{{ $user->jabatan }}">
        </div>

        <div class="mb-3">
            <label>Password Baru (opsional)</label>
            <input type="password" name="password" class="form-control">
        </div>

        <button class="btn btn-success">Simpan Perubahan</button>
    </form>
</div>
@endsection

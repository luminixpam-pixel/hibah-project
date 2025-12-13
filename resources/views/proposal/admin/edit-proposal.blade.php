@extends('layouts.app')

@section('content')
<div class="container mt-4">
    <h4 class="fw-bold mb-3">Edit Proposal</h4>

    {{-- Notifikasi sukses --}}
    @if(session('success'))
        <div class="alert alert-success">
            {{ session('success') }}
        </div>
    @endif

    <form action="{{ route('admin.proposals.update', $proposal->id) }}" method="POST">
        @csrf
        @method('PUT')

        {{-- Judul Proposal --}}
        <div class="mb-3">
            <label for="judul" class="form-label">Judul Proposal</label>
            <input type="text" name="judul" id="judul" class="form-control"
                   value="{{ old('judul', $proposal->judul) }}" required>
        </div>

        {{-- Ketua / Pengusul --}}
        <div class="mb-3">
            <label for="nama_ketua" class="form-label">Ketua / Pengusul</label>
            <input type="text" name="nama_ketua" id="nama_ketua" class="form-control"
                   value="{{ old('nama_ketua', $proposal->nama_ketua) }}" required>
        </div>

        {{-- Status Proposal --}}
        <div class="mb-3">
            <label for="status" class="form-label">Status Proposal</label>
            <select name="status" id="status" class="form-select">
                <option value="Perlu Direview" {{ $proposal->status === 'Perlu Direview' ? 'selected' : '' }}>Perlu Direview</option>
                <option value="Sedang Direview" {{ $proposal->status === 'Sedang Direview' ? 'selected' : '' }}>Sedang Direview</option>
                <option value="Disetujui" {{ $proposal->status === 'Disetujui' ? 'selected' : '' }}>Disetujui</option>
                <option value="Ditolak" {{ $proposal->status === 'Ditolak' ? 'selected' : '' }}>Ditolak</option>
                <option value="Direvisi" {{ $proposal->status === 'Direvisi' ? 'selected' : '' }}>Direvisi</option>
            </select>
        </div>

        {{-- Tenggat Waktu Review --}}
        <div class="mb-3">
            <label for="review_deadline" class="form-label">Tenggat Waktu Review</label>
            <input type="datetime-local" name="review_deadline" id="review_deadline"
                   class="form-control"
                   value="{{ old('review_deadline', optional($proposal->review_deadline)->format('Y-m-d\TH:i')) }}">
            <small class="text-muted">Format: Tahun-Bulan-Hari Jam:Menit</small>
        </div>

        {{-- Tombol Submit --}}
        <div class="text-end mt-4">
            <button type="submit" class="btn btn-primary">
                <i class="bi bi-save"></i> Simpan Perubahan
            </button>
            <a href="{{ route('admin.proposals.index') }}" class="btn btn-secondary">
                <i class="bi bi-arrow-left"></i> Kembali
            </a>
        </div>
    </form>
</div>
@endsection

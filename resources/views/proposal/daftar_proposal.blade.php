@extends('layouts.app')

@php
    $role = Auth::user()->role ?? null;
@endphp

@section('content')
<div class="container mt-4">
    {{-- Letakkan di atas tabel Monitoring --}}
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h4 class="fw-bold text-dark">Daftar Proposal Hibah Tahun {{ $tahun }}</h4>
        <form action="{{ route('proposal.index') }}" method="GET" class="d-flex gap-2">
            <select name="tahun" class="form-select form-select-sm border-0 shadow-sm" onchange="this.form.submit()">
                @foreach(range(date('Y'), 2023) as $year)
                    <option value="{{ $year }}" {{ $tahun == $year ? 'selected' : '' }}>Tahun {{ $year }}</option>
                @endforeach
            </select>
        </form>
    </div>

    {{-- SEARCH --}}
    <div class="d-flex justify-content-end mb-3">
        <div class="input-group shadow-sm" style="max-width: 350px;">
            <span class="input-group-text bg-white border-end-0"><i class="bi bi-search"></i></span>
            <input type="text" id="table-search" class="form-control border-start-0" placeholder="Cari data proposal...">
        </div>
    </div>

    <div class="table-responsive shadow-sm" style="border-radius: 12px;">
        <table class="table table-bordered table-hover align-middle bg-white mb-0">
            <thead class="table-light text-center">
                <tr>
                    <th width="5%">No</th>
                    <th>Judul Proposal</th>
                    <th>Pengusul</th>
                    <th>Status Alur</th>
                    <th width="15%">Aksi</th>
                    <th width="20%">Status Pendanaan</th> {{-- Kolom Fokus Utama --}}
                </tr>
            </thead>
            <tbody>
            @forelse ($proposals as $index => $proposal)
                <tr>
                    <td class="text-center">{{ $index + 1 }}</td>
                    <td>
                        <div class="fw-bold">{{ $proposal->judul }}</div>
                        <small class="text-muted">ID: #{{ $proposal->id }}</small>
                    </td>
                    <td class="text-center">{{ $proposal->nama_ketua }}</td>

                    {{-- Status Alur --}}
                    <td class="text-center">
                        @php
                            $alurClass = match($proposal->status) {
                                'Dikirim' => 'secondary',
                                'Perlu Direview' => 'warning',
                                'Direview' => 'info',
                                'Review Selesai' => 'primary',
                                default => 'dark'
                            };
                        @endphp
                        <span class="badge bg-{{ $alurClass }} rounded-pill">{{ $proposal->status }}</span>
                    </td>

                    {{-- Aksi --}}
                    <td class="text-center">
                        <div class="d-flex gap-2 justify-content-center">
                            @if ($proposal->file_path)
                                <a href="{{ route('proposal.download', $proposal->id) }}" class="btn btn-sm btn-outline-primary" title="Download">
                                    <i class="bi bi-download"></i>
                                </a>
                            @endif

                            @if($role === 'pengaju' && Auth::id() === $proposal->user_id && in_array($proposal->status, ['Dikirim', 'Direvisi']))
                                <a href="{{ url('/proposal/'.$proposal->id.'/edit') }}" class="btn btn-sm btn-warning text-white">
                                    <i class="bi bi-pencil"></i> Edit
                                </a>
                            @endif

                            <button type="button" class="btn btn-sm btn-success" data-bs-toggle="modal" data-bs-target="#tinjauModal{{ $proposal->id }}">
                                <i class="bi bi-eye"></i>
                            </button>

                            {{-- Tombol Hapus (Hanya untuk Pengaju) --}}
                            @if($role === 'pengaju' && Auth::id() === $proposal->user_id)
                                <form action="{{ route('proposal.destroy', $proposal->id) }}" method="POST" onsubmit="return confirm('Apakah Anda yakin ingin menghapus proposal ini? File juga akan terhapus dari server.')">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="btn btn-sm btn-outline-danger" title="Hapus Proposal">
                                        <i class="bi bi-trash"></i>
                                    </button>
                                </form>
                            @endif
                        </div>
                    </td>

                    {{-- STATUS PENDANAAN / INPUT ADMIN --}}
                    <td class="text-center bg-light">
                        @if($role === 'admin')
                            {{-- Jika Admin: Menampilkan Form Cepat di Dalam Kolom --}}
                            <form method="POST" action="{{ route('proposal.keputusan', $proposal->id) }}" class="d-flex gap-1">
                                @csrf
                                @method('PATCH')
                                <select name="status_pendanaan" class="form-select form-select-sm border-success" onchange="this.form.submit()">
                                    <option value="" disabled {{ is_null($proposal->status_pendanaan) ? 'selected' : '' }}>-- Beri Putusan --</option>
                                    <option value="Disetujui" {{ $proposal->status_pendanaan == 'Disetujui' ? 'selected' : '' }}>Setujui</option>
                                    <option value="Ditolak" {{ $proposal->status_pendanaan == 'Ditolak' ? 'selected' : '' }}>Tolak</option>
                                    <option value="Direvisi" {{ $proposal->status_pendanaan == 'Direvisi' ? 'selected' : '' }}>Revisi</option>
                                </select>
                            </form>
                        @else
                            {{-- Jika User: Menampilkan Hasil Badge --}}
                            @php
                                $pendanaanClass = match($proposal->status_pendanaan) {
                                    'Disetujui' => 'success',
                                    'Ditolak' => 'danger',
                                    'Direvisi' => 'warning',
                                    default => 'secondary'
                                };
                            @endphp
                            <span class="badge bg-{{ $pendanaanClass }} px-3 py-2 shadow-sm">
                                {{ $proposal->status_pendanaan ?? 'Proses Seleksi' }}
                            </span>
                        @endif
                    </td>
                </tr>
            @empty
                <tr><td colspan="6" class="text-center py-4">Tidak ada data proposal.</td></tr>
            @endforelse
            </tbody>
        </table>
    </div>
</div>

{{-- MODAL DETAIL (Hanya untuk Review Detail, Keputusan sudah pindah ke kolom) --}}
@foreach ($proposals as $proposal)
<div class="modal fade" id="tinjauModal{{ $proposal->id }}" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0">
            <div class="modal-header bg-dark text-white">
                <h5 class="modal-title small">Detail Informasi Proposal</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <p class="mb-1 text-muted small">Judul:</p>
                <p class="fw-bold">{{ $proposal->judul }}</p>
                <hr>
                <div class="row text-center">
                    <div class="col">
                        <small class="text-muted d-block">Status Alur</small>
                        <span class="badge bg-info">{{ $proposal->status }}</span>
                    </div>
                    <div class="col">
                        <small class="text-muted d-block">Hasil Pendanaan</small>
                        <span class="badge bg-dark">{{ $proposal->status_pendanaan ?? '-' }}</span>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endforeach

@endsection

@push('styles')
<style>
    body.modal-open #mainContent { backdrop-filter: none !important; filter: none !important; }
    .table select.form-select-sm { font-size: 0.75rem; padding: 0.25rem 0.5rem; }
    .badge { font-weight: 500; text-transform: uppercase; letter-spacing: 0.5px; }
</style>
@endpush

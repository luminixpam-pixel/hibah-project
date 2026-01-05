@extends('layouts.app')

@php
    $role = Auth::user()->role ?? null;
@endphp

@section('content')
<div class="container mt-4">
    {{-- Header & Filter Tahun --}}
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h4 class="fw-bold text-dark">Daftar Monitoring Proposal Tahun {{ $tahun }}</h4>
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
                </tr>
            </thead>
            <tbody>
                @forelse ($proposals as $index => $proposal)
                    <tr>
                        <td class="text-center">{{ $index + 1 }}</td>
                        <td>
                            <div class="fw-bold text-dark">{{ $proposal->judul }}</div>
                        </td>
                        <td class="text-center">{{ $proposal->nama_ketua }}</td>

                        {{-- Status Alur --}}
                        <td class="text-center">
                            @php
                                $alurClass = match($proposal->status) {
                                    'Dikirim' => 'secondary',
                                    'Perlu Direview' => 'warning',
                                    'Sedang Direview' => 'info',
                                    'Review Selesai' => 'primary',
                                    'Direvisi' => 'danger',
                                    default => 'dark'
                                };
                            @endphp
                            <span class="badge bg-{{ $alurClass }} rounded-pill" style="font-size: 10px;">{{ $proposal->status }}</span>
                        </td>

                        {{-- Aksi --}}
                        <td class="text-center">
                            <div class="d-flex gap-2 justify-content-center">
                                {{-- Download --}}
                                @if ($proposal->file_path)
                                    <a href="{{ route('proposal.download', $proposal->id) }}" class="btn btn-sm btn-outline-primary" title="Download">
                                        <i class="bi bi-download"></i>
                                    </a>
                                @endif

                                {{-- Admin: Push to Review --}}
                                @if($role === 'admin' && ($proposal->status === 'Dikirim' || $proposal->status === 'Hasil Revisi'))
                                    <form action="{{ route('proposal.set-review', $proposal->id) }}" method="POST" class="d-inline">
                                        @csrf
                                        @method('PATCH')
                                        <button type="submit" class="btn btn-sm btn-info text-white" title="Proses ke Review" onclick="return confirm('Pindahkan ke antrean review?')">
                                            <i class="bi bi-clipboard-check"></i>
                                        </button>
                                    </form>
                                @endif

                                {{-- Detail Modal Trigger --}}
                                <button type="button" class="btn btn-sm btn-success" data-bs-toggle="modal" data-bs-target="#tinjauModal{{ $proposal->id }}">
                                    <i class="bi bi-eye"></i>
                                </button>
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="5" class="text-center py-4 text-muted">Tidak ada data proposal untuk tahun ini.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>

{{-- MODAL DETAIL (Diletakkan di luar kontainer tabel agar tidak merusak layout) --}}
@foreach ($proposals as $proposal)
<div class="modal fade" id="tinjauModal{{ $proposal->id }}" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0">
            <div class="modal-header bg-dark text-white">
                <h5 class="modal-title fs-6">Detail Informasi Proposal</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <label class="text-muted small d-block mb-1">Judul Proposal:</label>
                <p class="fw-bold mb-3">{{ $proposal->judul }}</p>

                <div class="row g-3">
                    <div class="col-6">
                        <label class="text-muted small d-block mb-1">Nama Ketua:</label>
                        <p class="small fw-semibold">{{ $proposal->nama_ketua }}</p>
                    </div>
                    <div class="col-6">
                        <label class="text-muted small d-block mb-1">Status Saat Ini:</label>
                        <span class="badge bg-secondary">{{ $proposal->status }}</span>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endforeach

@endsection

@extends('layouts.app')

@php
    $role = Auth::user()->role ?? null;
@endphp

{{-- CSS Tambahan untuk memperbaiki masalah layar redup & tampilan PDF --}}
<style>
    .modal { z-index: 1060 !important; }
    .modal-backdrop { z-index: 1050 !important; }
    .pdf-container {
        background-color: #525659;
        display: flex;
        align-items: center;
        justify-content: center;
        min-height: 600px;
        border-radius: 0 0 12px 0;
    }

    <style>
    /* Efek Hover pada Tabel */
    .table-hover tbody tr:hover {
        background-color: #f8f9fa;
        transition: 0.3s;
        transform: scale(1.002);
    }

    /* Mempercantik shadow box tabel */
    .table-responsive {
        border: none !important;
        box-shadow: 0 10px 30px rgba(0,0,0,0.05);
    }

    /* Styling tombol soft-UI */
    .btn-sm {
        padding: 0.4rem 0.6rem;
        border-radius: 8px;
    }
</style>
</style>

@section('content')
<div class="container mt-4">
    {{-- Header & Filter Tahun --}}
    <div class="d-flex justify-content-between align-items-center mb-3">
        @if($role === 'admin')
            <h4 class="fw-bold text-dark">Daftar Proposal Masuk Tahun {{ $tahun }}</h4>
        @else
            <h4 class="fw-bold text-dark">Daftar Proposal Dikirim Tahun {{ $tahun }}</h4>
        @endif

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

    {{-- TABEL --}}
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

                       <td class="text-center">
                            @php
                                $statusMap = [
                                    'Dikirim'         => ['class' => 'bg-secondary', 'icon' => 'bi-send'],
                                    'Perlu Direview'  => ['class' => 'bg-warning text-dark', 'icon' => 'bi-hourglass-split'],
                                    'Sedang Direview' => ['class' => 'bg-info text-white', 'icon' => 'bi-search'],
                                    'Review Selesai'  => ['class' => 'bg-primary', 'icon' => 'bi-check-all'],
                                    'Direvisi'        => ['class' => 'bg-danger', 'icon' => 'bi-exclamation-triangle'],
                                ];
                                $currentStatus = $statusMap[$proposal->status] ?? ['class' => 'bg-dark', 'icon' => 'bi-info-circle'];
                            @endphp

                            <span class="badge {{ $currentStatus['class'] }} px-3 py-2 rounded-pill shadow-sm" style="font-size: 0.75rem; letter-spacing: 0.5px;">
                                <i class="{{ $currentStatus['icon'] }} me-1"></i>
                                {{ $role === 'admin' && $proposal->status === 'Dikirim' ? 'PROPOSAL BARU' : strtoupper($proposal->status) }}
                            </span>
                        </td>

                      <td class="text-center">
                            <div class="d-flex gap-2 justify-content-center">
                                {{-- Detail Modal Trigger - Soft Green --}}
                                <button type="button" class="btn btn-md border-0 shadow-sm d-flex align-items-center justify-content-center"
                                        data-bs-toggle="modal" data-bs-target="#tinjauModal{{ $proposal->id }}"
                                        title="Lihat Detail"
                                        style="background-color: #e8f5e9; color: #2e7d32; width: 38px; height: 38px; border-radius: 10px;">
                                    <i class="bi bi-eye-fill fs-5"></i>
                                </button>

                                {{-- Download - Soft Blue --}}
                                @if ($proposal->file_path)
                                    <a href="{{ route('proposal.download', $proposal->id) }}"
                                    class="btn btn-md border-0 shadow-sm d-flex align-items-center justify-content-center"
                                    title="Download"
                                    style="background-color: #e3f2fd; color: #1565c0; width: 38px; height: 38px; border-radius: 10px;">
                                        <i class="bi bi-cloud-arrow-down-fill fs-5"></i>
                                    </a>
                                @endif

                                {{-- Admin: Push to Review - Soft Cyan --}}
                                @if($role === 'admin' && ($proposal->status === 'Dikirim' || $proposal->status === 'Hasil Revisi'))
                                    <form action="{{ route('proposal.set-review', $proposal->id) }}" method="POST" class="d-inline">
                                        @csrf
                                        @method('PATCH')
                                        <button type="submit" class="btn btn-md border-0 shadow-sm d-flex align-items-center justify-content-center"
                                                title="Proses ke Review" onclick="return confirm('Pindahkan ke antrean review?')"
                                                style="background-color: #e0f7fa; color: #00838f; width: 38px; height: 38px; border-radius: 10px;">
                                            <i class="bi bi-arrow-right-circle-fill fs-5"></i>
                                        </button>
                                    </form>
                                @endif

                                {{-- Tombol Hapus: Pengaju & Reviewer - Soft Red --}}
                                @if(in_array($role, ['pengaju', 'reviewer']) && in_array($proposal->status, ['Dikirim', 'Ditolak', 'Direvisi', 'Sedang Direview', 'Hasil Revisi']))
                                    <form action="{{ route('proposal.destroy', $proposal->id) }}" method="POST" class="d-inline" onsubmit="return confirm('Yakin ingin menghapus proposal ini?')">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="btn btn-md border-0 shadow-sm d-flex align-items-center justify-content-center"
                                                title="Hapus"
                                                style="background-color: #ffebee; color: #c62828; width: 38px; height: 38px; border-radius: 10px;">
                                            <i class="bi bi-trash3-fill fs-5"></i>
                                        </button>
                                    </form>
                                @endif
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

{{-- --- MODAL SECTION (DI LUAR CONTAINER) --- --}}
@foreach ($proposals as $proposal)
<div class="modal fade" id="tinjauModal{{ $proposal->id }}" tabindex="-1" data-bs-focus="false" aria-hidden="true">
    <div class="modal-dialog modal-xl modal-dialog-centered modal-dialog-scrollable">
        <div class="modal-content border-0 shadow-lg">
            <div class="modal-header bg-dark text-white px-4">
                <h5 class="modal-title d-flex align-items-center">
                    <i class="bi bi-file-earmark-text me-2"></i>
                    <span class="fs-6">Detail Proposal</span>
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>

            <div class="modal-body p-0">
                <div class="container-fluid p-0">
                    <div class="row g-0">
                        {{-- Panel Kiri --}}
                        <div class="col-lg-4 bg-light border-end p-4">
                            <div class="mb-4">
                                <label class="text-muted small fw-bold text-uppercase d-block">Judul</label>
                                <p class="fw-bold text-dark">{{ $proposal->judul }}</p>
                            </div>
                            <div class="mb-3">
                                <label class="text-muted small fw-bold text-uppercase d-block">Ketua</label>
                                <p><i class="bi bi-person text-primary"></i> {{ $proposal->nama_ketua }}</p>
                            </div>
                            <div class="mb-4">
                                <label class="text-muted small fw-bold text-uppercase d-block">Anggota</label>
                                <div class="p-2 border rounded bg-white small">
                                    @if(!empty($proposal->anggota) && is_array($proposal->anggota))
                                        <ul class="list-unstyled mb-0">
                                            @foreach($proposal->anggota as $nama)
                                                <li><i class="bi bi-check2 text-success me-1"></i> {{ $nama }}</li>
                                            @endforeach
                                        </ul>
                                    @else
                                        <span class="text-muted italic">-</span>
                                    @endif
                                </div>
                            </div>
                            <div class="d-grid gap-2">
                                @if($proposal->file_path)
                                    <a href="{{ route('proposal.download', $proposal->id) }}" class="btn btn-primary btn-sm">
                                        <i class="bi bi-download"></i> Download Asli
                                    </a>
                                @endif
                                <button type="button" class="btn btn-outline-secondary btn-sm" data-bs-dismiss="modal">Tutup</button>
                            </div>
                        </div>

                      {{-- Panel Kanan (Pratinjau) --}}
                        <div class="col-lg-8 p-0" style="background-color: #525659; min-height: 700px;">
                            @if($proposal->file_path)
                                @php
                                    $extension = pathinfo($proposal->file_path, PATHINFO_EXTENSION);
                                @endphp

                                @if(strtolower($extension) === 'pdf')
                                    {{-- Tampilkan Iframe HANYA jika file adalah PDF --}}
                                    <iframe
                                        src="{{ asset('storage/' . $proposal->file_path) }}"
                                        width="100%"
                                        height="700px"
                                        style="border: none;">
                                    </iframe>
                                @else
                                    {{-- Tampilan untuk Word (.doc/.docx) agar TIDAK otomatis download --}}
                                    <div class="d-flex flex-column align-items-center justify-content-center text-white h-100 p-5">
                                        <i class="bi bi-file-earmark-word display-1 text-primary mb-3"></i>
                                        <h4 class="fw-bold">Pratinjau Tidak Tersedia</h4>
                                        <p class="text-center opacity-75">Browser tidak dapat menampilkan file Word secara langsung. Silakan unduh file untuk melihat isi proposal.</p>
                                        <a href="{{ asset('storage/' . $proposal->file_path) }}" class="btn btn-primary btn-lg mt-3 shadow">
                                            <i class="bi bi-download me-2"></i> Unduh File Proposal
                                        </a>
                                    </div>
                                @endif
                            @else
                                <div class="text-white text-center p-5">
                                    <i class="bi bi-file-earmark-x display-1 opacity-50"></i>
                                    <p>File tidak tersedia</p>
                                </div>
                            @endif
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endforeach
<script>
    document.addEventListener('DOMContentLoaded', function () {
        const modals = document.querySelectorAll('.modal');
        modals.forEach(modal => {
            // Memindahkan modal ke body agar tidak tertutup backdrop hitam
            document.body.appendChild(modal);
        });
    });
</script>

@endsection

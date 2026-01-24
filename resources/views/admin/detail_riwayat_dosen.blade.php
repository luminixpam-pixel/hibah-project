@extends('layouts.app')

@section('content')
<div class="container mt-4">

    {{-- ===================== HEADER ==================== --}}
    <div class="d-flex justify-content-between align-items-center mb-4 bg-white p-3 rounded shadow-sm border">
        <div>
            <div class="d-flex align-items-center gap-2">
                <a href="{{ route('admin.riwayatDosen') }}" class="btn btn-sm btn-outline-secondary">
                    <i class="bi bi-arrow-left"></i> Kembali
                </a>
                <h4 class="fw-bold mb-0 text-dark">Detail Produktivitas Dosen</h4>
            </div>
            <p class="text-muted mb-0 small">Ringkasan dan daftar pengajuan hibah untuk dosen terpilih.</p>
        </div>
        <button onclick="window.print()" class="btn btn-sm btn-outline-secondary">
            <i class="bi bi-printer me-1"></i> Cetak
        </button>
    </div>

    {{-- ===================== PROFIL DOSEN ==================== --}}
    <div class="card border-0 shadow-sm mb-4">
        <div class="card-body d-flex align-items-center gap-3">
            <div class="avatar-sm bg-primary-subtle text-primary fw-bold rounded-circle d-flex align-items-center justify-content-center"
                 style="width:46px;height:46px;">
                {{ strtoupper(substr($dosen->name, 0, 1)) }}
            </div>

            <div class="flex-grow-1">
                <div class="fw-bold text-dark" style="font-size: 1.1rem;">{{ $dosen->name }}</div>
                <div class="text-muted small">
                    NIDN: <span class="fw-semibold">{{ $dosen->nidn ?? 'N/A' }}</span>
                    @if(!empty($dosen->jabatan))
                        • Jabatan: <span class="fw-semibold">{{ $dosen->jabatan }}</span>
                    @endif
                    @if(!empty($dosen->program_studi))
                        • Prodi: <span class="fw-semibold">{{ $dosen->program_studi }}</span>
                    @endif
                    @if(!empty($dosen->fakultas))
                        • Fakultas: <span class="fw-semibold">{{ $dosen->fakultas }}</span>
                    @endif
                </div>
            </div>
        </div>
    </div>

    {{-- ===================== SUMMARY STATS ==================== --}}
    <div class="row g-3 mb-4">
        <div class="col-md-3">
            <div class="card border-0 shadow-sm p-3 bg-white border-start border-primary border-4">
                <small class="text-muted fw-bold text-uppercase">Total Pengajuan</small>
                <h3 class="fw-bold mb-0 text-primary">{{ number_format($stats['totalPengajuan'] ?? 0, 0, ',', '.') }}</h3>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card border-0 shadow-sm p-3 bg-white border-start border-success border-4">
                <small class="text-muted fw-bold text-uppercase">Disetujui</small>
                <h3 class="fw-bold mb-0 text-success">{{ number_format($stats['totalDisetujui'] ?? 0, 0, ',', '.') }}</h3>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card border-0 shadow-sm p-3 bg-white border-start border-danger border-4">
                <small class="text-muted fw-bold text-uppercase">Ditolak</small>
                <h3 class="fw-bold mb-0 text-danger">{{ number_format($stats['totalDitolak'] ?? 0, 0, ',', '.') }}</h3>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card border-0 shadow-sm p-3 bg-white border-start border-warning border-4">
                <small class="text-muted fw-bold text-uppercase">Direvisi</small>
                <h3 class="fw-bold mb-0 text-warning">{{ number_format($stats['totalDirevisi'] ?? 0, 0, ',', '.') }}</h3>
            </div>
        </div>

        <div class="col-md-6">
            <div class="card border-0 shadow-sm p-3 bg-white border-start border-dark border-4">
                <small class="text-muted fw-bold text-uppercase">Total Dana Terserap (Disetujui)</small>
                <h3 class="fw-bold mb-0 text-dark">Rp {{ number_format($stats['totalDana'] ?? 0, 0, ',', '.') }}</h3>
            </div>
        </div>

        <div class="col-md-6">
            <div class="card border-0 shadow-sm p-3 bg-white border-start border-info border-4">
                <small class="text-muted fw-bold text-uppercase">Success Rate</small>
                @php $sr = $stats['successRate'] ?? 0; @endphp
                <div class="d-flex align-items-center gap-2">
                    <div class="progress flex-grow-1" style="height: 8px;">
                        <div class="progress-bar" role="progressbar" style="width: {{ $sr }}%"></div>
                    </div>
                    <div class="fw-bold text-info" style="min-width:70px;">{{ number_format($sr, 1) }}%</div>
                </div>
                <div class="text-muted x-small mt-1">
                    (Disetujui / Total Pengajuan) × 100
                </div>
            </div>
        </div>
    </div>

    {{-- ===================== TABEL PROPOSAL DOSEN ==================== --}}
    <div class="card border-0 shadow-sm overflow-hidden">
        <div class="card-header bg-white py-3">
            <h6 class="fw-bold mb-0">
                <i class="bi bi-folder2-open me-2 text-primary"></i>Daftar Proposal Dosen
            </h6>
        </div>

        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="bg-light text-muted small text-uppercase">
                    <tr>
                        <th class="ps-4">Judul</th>
                        <th>Periode</th>
                        <th>Status</th>
                        <th>Status Pendanaan</th>
                        <th class="text-end">Biaya</th>
                        <th class="text-end pe-4">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($proposals as $p)
                        <tr>
                            <td class="ps-4">
                                <div class="fw-bold text-dark">{{ $p->judul }}</div>
                                <div class="text-muted x-small">
                                    Fakultas/Prodi: {{ $p->fakultas->nama_fakultas ?? '-' }}
                                </div>
                            </td>
                            <td>{{ $p->periode ?? '-' }}</td>
                            <td>
                                <span class="badge bg-secondary-subtle text-secondary border border-secondary-subtle">
                                    {{ $p->status ?? '-' }}
                                </span>
                            </td>
                            <td>
                                @php
                                    $sp = $p->status_pendanaan ?? '-';
                                    $cls = 'bg-secondary-subtle text-secondary border border-secondary-subtle';
                                    if ($sp === 'Disetujui') $cls = 'bg-success-subtle text-success border border-success-subtle';
                                    elseif ($sp === 'Ditolak') $cls = 'bg-danger-subtle text-danger border border-danger-subtle';
                                    elseif ($sp === 'Direvisi') $cls = 'bg-warning-subtle text-warning border border-warning-subtle';
                                @endphp
                                <span class="badge {{ $cls }}">{{ $sp }}</span>
                            </td>
                            <td class="text-end">
                                Rp {{ number_format($p->biaya ?? 0, 0, ',', '.') }}
                            </td>
                            <td class="text-end pe-4">
                                <a href="{{ route('proposal.tinjau', $p->id) }}" class="btn btn-sm btn-outline-primary">
                                    <i class="bi bi-eye"></i> Tinjau
                                </a>
                                <a href="{{ route('proposal.download', $p->id) }}" class="btn btn-sm btn-outline-secondary">
                                    <i class="bi bi-download"></i> Unduh
                                </a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="text-center py-5 text-muted small">
                                Belum ada proposal untuk dosen ini.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

</div>
@endsection

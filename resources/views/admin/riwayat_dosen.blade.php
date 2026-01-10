@extends('layouts.app')

@section('content')
<div class="container mt-4">

    {{-- ===================== HEADER & TOOLS ==================== --}}
    <div class="d-flex justify-content-between align-items-center mb-4 bg-white p-3 rounded shadow-sm border">
        <div>
            <h4 class="fw-bold mb-0 text-dark">Indeks Produktivitas Dosen</h4>
            <p class="text-muted mb-0 small">Analisis riwayat hibah dan efektivitas usulan penelitian.</p>
        </div>
        <div class="d-flex gap-2">
            <form action="{{ route('admin.riwayatDosen') }}" method="GET" class="d-flex gap-2">
                <input type="text" name="search" class="form-control form-control-sm" placeholder="Cari Nama / NIDN..." value="{{ request('search') }}">
                <button type="submit" class="btn btn-sm btn-primary">
                    <i class="bi bi-search"></i>
                </button>
            </form>
            <button onclick="window.print()" class="btn btn-sm btn-outline-secondary">
                <i class="bi bi-printer me-1"></i> Cetak Laporan
            </button>
        </div>
    </div>

    {{-- ===================== SUMMARY STATS ==================== --}}
    <div class="row g-3 mb-4">
        <div class="col-md-4">
            <div class="card border-0 shadow-sm p-3 bg-white border-start border-primary border-4">
                <small class="text-muted fw-bold text-uppercase">Rata-rata Dana / Dosen</small>
                <h3 class="fw-bold mb-0 text-primary">
                    {{-- FORMAT RUPIAH --}}
                    Rp {{ number_format($riwayatDosen->avg('total_dana') ?? 0, 0, ',', '.') }}
                </h3>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card border-0 shadow-sm p-3 bg-white border-start border-success border-4">
                <small class="text-muted fw-bold text-uppercase">Dosen Paling Aktif</small>
                @php $topDosen = $riwayatDosen->sortByDesc('total_pengajuan')->first(); @endphp
                <h4 class="fw-bold mb-0 text-dark">{{ $topDosen->name ?? '-' }}</h4>
                <div class="x-small text-success fw-bold">{{ $topDosen->total_pengajuan ?? 0 }} Total Pengajuan</div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card border-0 shadow-sm p-3 bg-white border-start border-warning border-4">
                <small class="text-muted fw-bold text-uppercase">Total Anggaran Terpakai</small>
                <h3 class="fw-bold mb-0 text-dark">
                    {{-- FORMAT RUPIAH --}}
                    Rp {{ number_format($riwayatDosen->sum('total_dana') ?? 0, 0, ',', '.') }}
                </h3>
            </div>
        </div>
    </div>

    {{-- ===================== TABEL RIWAYAT & PRODUKTIVITAS ==================== --}}
    <div class="card border-0 shadow-sm overflow-hidden">
        <div class="card-header bg-white py-3">
            <h6 class="fw-bold mb-0"><i class="bi bi-graph-up-arrow me-2 text-primary"></i>Ranking Produktivitas Dosen</h6>
        </div>
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="bg-light text-muted small text-uppercase">
                    <tr>
                        <th class="ps-4" style="width: 30%;">Dosen & NIDN</th>
                        <th>Fakultas</th>
                        <th class="text-center">Total Aju</th>
                        <th class="text-center">Diterima</th>
                        <th class="text-center">Success Rate</th>
                        <th class="text-end pe-4">Dana Terserap</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($riwayatDosen as $dosen)
                        @php
                            $rate = $dosen->total_pengajuan > 0 ? ($dosen->total_disetujui / $dosen->total_pengajuan) * 100 : 0;
                            $rateColor = $rate >= 70 ? 'bg-success' : ($rate >= 40 ? 'bg-warning' : 'bg-danger');
                        @endphp
                        <tr>
                            <td class="ps-4">
                                <a href="{{ route('admin.dosen.detail', $dosen->id) }}" class="text-decoration-none d-flex align-items-center">
                                    <div class="avatar-sm me-3 bg-primary-subtle text-primary fw-bold rounded-circle d-flex align-items-center justify-content-center">
                                        {{ strtoupper(substr($dosen->name, 0, 1)) }}
                                    </div>
                                    <div>
                                        <div class="fw-bold text-dark">{{ $dosen->name }}</div>
                                        <div class="text-muted x-small">ID: {{ $dosen->nidn ?? 'N/A' }}</div>
                                    </div>
                                </a>
                            </td>
                            <td><span class="small text-secondary">{{ $dosen->fakultas ?? '-' }}</span></td>
                            <td class="text-center fw-bold">{{ number_format($dosen->total_pengajuan, 0, ',', '.') }}</td>
                            <td class="text-center">
                                <span class="badge bg-success-subtle text-success border border-success-subtle px-3">
                                    {{ number_format($dosen->total_disetujui, 0, ',', '.') }}
                                </span>
                            </td>
                            <td class="text-center">
                                <div class="d-flex align-items-center justify-content-center gap-2">
                                    <div class="progress" style="width: 60px; height: 6px;">
                                        <div class="progress-bar {{ $rateColor }}" role="progressbar" style="width: {{ $rate }}%"></div>
                                    </div>
                                    <span class="small fw-bold">{{ number_format($rate, 1) }}%</span>
                                </div>
                            </td>
                            <td class="text-end pe-4">
                                {{-- FORMAT RUPIAH --}}
                                <div class="fw-bold text-primary">Rp {{ number_format($dosen->total_dana ?? 0, 0, ',', '.') }}</div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="text-center py-5 text-muted small">Tidak ada data dosen ditemukan.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    {{-- KETERANGAN --}}
    <div class="mt-3 bg-light p-3 rounded border">
        <p class="mb-0 x-small text-muted">
            <strong>Catatan:</strong>
            Success Rate dihitung dari persentase proposal yang berstatus <b>Disetujui</b> dibandingkan total proposal yang pernah diajukan.
            Gunakan tombol <b>Cari</b> untuk memfilter dosen spesifik atau cetak sebagai laporan fisik.
        </p>
    </div>
</div>
@endsection

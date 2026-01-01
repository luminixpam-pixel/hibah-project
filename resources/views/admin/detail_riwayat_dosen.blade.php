@extends('layouts.app')

@section('content')
<div class="container mt-4">
    <a href="{{ route('admin.riwayatDosen') }}" class="btn btn-sm btn-outline-secondary mb-3">
        <i class="bi bi-arrow-left"></i> Kembali ke Daftar
    </a>

    <div class="row">
        <div class="col-md-4">
            <div class="card border-0 shadow-sm p-4 text-center">
                <div class="mx-auto bg-primary text-white rounded-circle d-flex align-items-center justify-content-center mb-3" style="width: 80px; height: 80px; font-size: 30px; font-weight: bold;">
                    {{ strtoupper(substr($dosen->name, 0, 1)) }}
                </div>
                <h5 class="fw-bold mb-1">{{ $dosen->name }}</h5>
                <p class="text-muted small mb-3">NIDN: {{ $dosen->nidn ?? '-' }}</p>
                <hr>
                <div class="text-start">
                    <small class="text-muted d-block">Fakultas</small>
                    <p class="fw-semibold">{{ $dosen->fakultas ?? '-' }}</p>
                    <small class="text-muted d-block">Program Studi</small>
                    <p class="fw-semibold">{{ $dosen->program_studi ?? '-' }}</p>
                </div>
            </div>
        </div>

        <div class="col-md-8">
            <div class="card border-0 shadow-sm overflow-hidden">
                <div class="card-header bg-white py-3">
                    <h6 class="fw-bold mb-0">Riwayat Proposal & Hibah</h6>
                </div>
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0">
                        <thead class="bg-light small text-uppercase">
                            <tr>
                                <th class="ps-3">Judul Penelitian</th>
                                <th class="text-center">Tahun</th>
                                <th class="text-center">Status</th>
                                <th class="text-end pe-3">Dana</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($proposals as $p)
                            <tr>
                                <td class="ps-3">
                                    <div class="fw-bold small">{{ $p->judul }}</div>
                                    <div class="x-small text-muted">Skema: {{ $p->skema ?? 'Reguler' }}</div>
                                </td>
                                <td class="text-center small">{{ $p->created_at->format('Y') }}</td>
                                <td class="text-center">
                                    <span class="badge @if($p->status == 'Disetujui') bg-success @elseif($p->status == 'Ditolak') bg-danger @else bg-warning @endif x-small">
                                        {{ $p->status }}
                                    </span>
                                </td>
                                <td class="text-end pe-3 fw-bold text-primary small">
                                    Rp {{ number_format($p->biaya, 0, ',', '.') }}
                                </td>
                            </tr>
                            @empty
                            <tr><td colspan="4" class="text-center py-4 text-muted">Belum ada riwayat proposal.</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

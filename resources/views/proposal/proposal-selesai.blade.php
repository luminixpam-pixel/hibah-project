@extends('layouts.app')

@section('content')
<style>
    body { background-color: #f0fdf4; } /* Latar belakang hijau lembut tema YARSI */

    .main-card {
        background: white;
        border-radius: 20px;
        padding: 30px;
        box-shadow: 0 10px 25px rgba(0,0,0,0.05);
        margin-top: 20px;
        border: none;
    }

    .page-title { font-weight: 800; font-size: 24px; color: #333; }
    .page-subtitle { color: #777; font-size: 14px; }

    /* Search Box styling */
    .search-wrapper {
        position: relative;
        max-width: 350px;
        float: right;
        margin-bottom: 25px;
    }
    .search-wrapper input {
        border-radius: 12px;
        padding-left: 45px;
        height: 45px;
        border: 1px solid #e0e0e0;
        background: #fff;
    }
    .search-wrapper i {
        position: absolute;
        left: 15px;
        top: 13px;
        color: #aaa;
        font-size: 18px;
    }

    /* Table styling - Identik dengan halaman Sedang Direview */
    .table thead th {
        border: none;
        color: #888;
        font-size: 12px;
        text-transform: none; /* ✅ dulu uppercase, sekarang normal */
        letter-spacing: 1px;
        padding: 15px;
        text-align: center;
    }
    .table tbody td {
        padding: 15px;
        vertical-align: middle;
        border-bottom: 1px solid #f2f2f2;
    }

    /* Skor styling */
    .skor-akhir {
        color: #007bff;
        font-weight: 800;
        font-size: 20px;
        margin-bottom: 0;
        line-height: 1;
    }
    .skor-label {
        font-size: 9px;
        color: #999;
        font-weight: 600;
        text-transform: none; /* ✅ dulu uppercase, sekarang normal */
    }

    /* Action buttons PDF */
    .btn-pdf {
        border: 1px solid #ffcccc;
        color: #ff5b5b;
        background: white;
        font-size: 11px;
        font-weight: 700;
        padding: 6px 12px;
        border-radius: 8px;
        margin-bottom: 5px;
        display: flex;
        align-items: center;
        gap: 8px;
        text-decoration: none;
        transition: 0.3s;
    }
    .btn-pdf:hover {
        background: #fff5f5;
        color: #d94545;
    }

    /* Custom select pendanaan */
    .select-pendanaan {
        border-radius: 10px;
        font-size: 13px;
        font-weight: 700;
        padding: 8px 12px;
        cursor: pointer;
    }

    .nav-btn {
        font-size: 12px;
        font-weight: 600;
        border-radius: 10px;
        padding: 8px 16px;
        transition: 0.3s;
    }

    .badge-status-selesai {
        background-color: #e0f2ff;
        color: #007bff;
        font-size: 10px;
        font-weight: 700;
        padding: 4px 10px;
        border-radius: 6px;
        text-transform: none; /* ✅ dulu uppercase, sekarang normal */
    }
</style>

<div class="container pb-5">
    <div class="main-card">
        {{-- HEADER --}}
        <div class="row align-items-center mb-2">
            <div class="col-md-8">
                <h4 class="page-title">Daftar Review Selesai — Universitas YARSI</h4>
                <p class="page-subtitle">Keputusan pendanaan berdasarkan hasil rata-rata skor dari para reviewer.</p>
            </div>
            <div class="col-md-4">
                <div class="search-wrapper w-100">
                    <i class="bi bi-search"></i>
                    <input type="text" id="table-search" class="form-control shadow-sm" placeholder="Cari judul atau pengusul...">
                </div>
            </div>
        </div>

        {{-- TABLE --}}
        <div class="table-responsive mt-3">
            <table class="table align-middle">
                <thead>
                    <tr>
                        <th width="5%">No</th>
                        <th class="text-start" width="30%">Judul Proposal & Pengusul</th>
                        <th class="text-start" width="20%">Reviewer</th>
                        <th>Skor Akhir</th>
                        <th width="15%">Status Pendanaan</th>
                        <th width="15%">Hasil Review</th>
                    </tr>
                </thead>
                <tbody>
                @forelse($proposals as $index => $proposal)
                    @php
                        $proposalReviews = $proposal->reviews ?? collect();
                        $avgScore = $proposalReviews->avg('total_score') ?? 0;
                        $isDone = !is_null($proposal->status_pendanaan);
                    @endphp
                    <tr>
                        <td class="text-center fw-bold text-muted">{{ $index + 1 }}</td>

                        {{-- JUDUL & PENGUSUL --}}
                        <td>
                            <div class="fw-bold text-dark" style="font-size: 14px; line-height: 1.4;">{{ $proposal->judul }}</div>
                            <div class="text-primary small mt-1" style="font-size: 12px;">
                                <i class="bi bi-person me-1"></i>{{ $proposal->user->name ?? $proposal->nama_ketua }}
                            </div>
                            <span class="badge badge-status-selesai mt-2">Selesai</span>
                        </td>

                        {{-- REVIEWER LIST --}}
                        <td>
                            @forelse($proposalReviews as $rev)
                                <div class="small text-dark mb-2 d-flex align-items-center">
                                    <i class="bi bi-person-circle me-2 text-primary"></i>
                                    {{ $rev->reviewer->name ?? 'Reviewer' }}
                                </div>
                            @empty
                                <span class="text-muted small">-</span>
                            @endforelse
                        </td>

                        {{-- SKOR AKHIR --}}
                        <td class="text-center">
                            <p class="skor-akhir">{{ number_format($avgScore, 2) }}</p>
                            <span class="skor-label">Rata-rata</span>
                        </td>

                        {{-- STATUS PENDANAAN --}}
                         <td>
                            {{-- 1. Definisikan variabel di paling atas baris <td> --}}
                            @php
                                $isDone = in_array($proposal->status, ['Disetujui', 'Ditolak', 'Direvisi']);
                            @endphp

                            @if(Auth::user()->role === 'admin')
                                <form action="{{ route('proposal.keputusan', $proposal->id) }}" method="POST">
                                    @csrf
                                    @method('PATCH')
                                    <select name="status_pendanaan"
                                            class="form-select shadow-sm border-{{ $proposal->status == 'Disetujui' ? 'success' : ($proposal->status == 'Ditolak' ? 'danger' : 'primary') }}"
                                            onchange="if(confirm('Simpan keputusan ini?')) this.form.submit()">

                                        <option value="" disabled {{ !$isDone ? 'selected' : '' }}>-- Pilih Keputusan --</option>
                                        <option value="Disetujui" {{ $proposal->status == 'Disetujui' ? 'selected' : '' }}>Disetujui</option>
                                        <option value="Ditolak" {{ $proposal->status == 'Ditolak' ? 'selected' : '' }}>Ditolak</option>
                                        <option value="Direvisi" {{ $proposal->status == 'Direvisi' ? 'selected' : '' }}>Direvisi</option>
                                    </select>
                                </form>
                            @else
                                {{-- Tampilan untuk User --}}
                                <div class="text-center">
                                    <span class="badge {{ $isDone ? ($proposal->status == 'Disetujui' ? 'bg-success' : 'bg-danger') : 'bg-secondary' }} p-2 shadow-sm">
                                        {{ $proposal->status ?? 'PROSES' }}
                                    </span>
                                </div>
                            @endif
                        </td>

                        {{-- BERKAS PDF --}}
                       <td>
                            @foreach($proposalReviews as $rev)
                                <a href="{{ route('review.pdf', $rev->id) }}" class="btn-pdf shadow-sm" target="_blank">
                                    <i class="bi bi-file-earmark-pdf-fill"></i> PDF Rev {{ $loop->iteration }}
                                </a>
                            @endforeach
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="6" class="text-center py-5 text-muted small">Belum ada data review selesai.</td>
                    </tr>
                @endforelse
                </tbody>
            </table>
        </div>

        {{-- NAVIGATION BOTTOM --}}
        <div class="d-flex justify-content-between mt-4">
            <a href="{{ route('monitoring.proposalSedangDireview') }}" class="btn btn-outline-success nav-btn">
                <i class="bi bi-arrow-left me-1"></i> Sedang Direview
            </a>
            <span class="text-muted small">Universitas YARSI &copy; {{ date('Y') }}</span>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
    document.getElementById('table-search').addEventListener('keyup', function() {
        let term = this.value.toLowerCase();
        document.querySelectorAll('tbody tr').forEach(row => {
            row.style.display = row.innerText.toLowerCase().includes(term) ? '' : 'none';
        });
    });
</script>
@endpush

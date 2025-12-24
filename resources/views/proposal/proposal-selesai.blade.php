@extends('layouts.app')

@php
    $role = Auth::user()->role ?? null;
@endphp

@section('content')

<style>
    .page-title {
        font-weight: 700;
        font-size: 22px;
        color: #2d2d2d;
    }

    .table thead th {
        background: #f8f9fa !important;
        font-weight: 600;
    }

    .btn-action {
        padding: 4px 10px; /* 🔽 diperkecil */
        border-radius: 6px;
        font-size: 13px;   /* 🔽 diperkecil */
    }

    .page-subtitle {
        font-size: 15px;
        color: #6c757d;
    }

    /* ================= KOLOM WIDTH ================= */
    .col-reviewer {
        width: 260px;          /* 🔼 BESAR */
    }

    .col-jumlah-review {
        width: 90px;           /* 🔽 KECIL */
        text-align: center;
        white-space: nowrap;
    }

    .col-aksi {
        width: 120px;          /* 🔽 KECIL */
        white-space: nowrap;
    }

    .aksi-wrap {
        gap: 4px !important;
    }
</style>

<div class="container mt-4">

    {{-- TITLE --}}
    <h4 class="page-title mb-1"> Daftar Review Selesai — Universitas YARSI</h4>
    <p class="page-subtitle mb-4">
        Berikut daftar seluruh proposal yang review-nya sudah lengkap (2 reviewer submit).
    </p>

    {{-- 🔍 SEARCH --}}
    <div class="d-flex justify-content-end mb-3">
        <div class="input-group" style="max-width: 320px;">
            <span class="input-group-text"><i class="bi bi-search"></i></span>
            <input type="text" id="table-search" class="form-control"
                   placeholder="Cari Judul Proposal atau Nama Dosen">
        </div>
    </div>

    {{-- TABLE --}}
    <div class="table-responsive">
        <table class="table table-striped table-hover align-middle shadow-sm">
            <thead>
                <tr>
                    <th>No</th>
                    <th>Judul Proposal</th>
                    <th>Pengusul</th>
                    <th class="col-reviewer">Reviewer</th>
                    <th>Status Proposal</th>
                    <th>Skor Reviewer</th>
                    <th class="col-jumlah-review">Jumlah Review</th>
                    <th>Tanggal Review</th>
                    <th class="col-aksi">Aksi</th>
                </tr>
            </thead>

            <tbody>
            @forelse($proposals as $index => $proposal)
                @php
                    $pengusul   = $proposal->user->name ?? '-';
                    $judul      = $proposal->judul ?? '-';
                    $statusProp = $proposal->status ?? '-';

                    $proposalReviews = $proposal->reviews ?? collect();
                    $reviewCount = $proposalReviews->unique('reviewer_id')->count();
                    $lastReviewDate = optional($proposalReviews->sortByDesc('created_at')->first())->created_at;
                @endphp

                <tr>
                    <td>{{ $index + 1 }}</td>
                    <td>{{ $judul }}</td>
                    <td>{{ $pengusul }}</td>

                    {{-- REVIEWER (besar) --}}
                    <td class="col-reviewer">
                        @forelse($proposalReviews as $rev)
                            <div class="small">• {{ $rev->reviewer->name ?? 'Reviewer' }}</div>
                        @empty
                            <span class="text-muted">-</span>
                        @endforelse
                    </td>

                    <td>
                        <span class="badge bg-info">{{ $statusProp }}</span>
                    </td>

                    {{-- SKOR --}}
                    <td>
                        @forelse($proposalReviews as $rev)
                            <div class="small">
                                <strong>{{ $rev->reviewer->name ?? 'Reviewer' }}:</strong>
                                {{ $rev->total_score !== null ? number_format($rev->total_score, 2) : '-' }}
                            </div>
                        @empty
                            <span class="text-muted">-</span>
                        @endforelse
                    </td>

                    {{-- JUMLAH REVIEW (kecil) --}}
                    <td class="col-jumlah-review">
                        <span class="badge bg-secondary">{{ $reviewCount }}</span>
                        <span class="text-muted">/2</span>
                    </td>

                    <td>
                        {{ $lastReviewDate ? $lastReviewDate->format('d M Y') : '-' }}
                    </td>

                    {{-- AKSI (kecil & rapi) --}}
                    <td class="col-aksi">
                        <div class="d-flex flex-column aksi-wrap">
                            @foreach($proposalReviews as $rev)
                                <a href="{{ route('review.pdf', $rev->id) }}"
                                   class="btn btn-outline-primary btn-sm btn-action">
                                    Hasil Review
                                </a>
                            @endforeach

                            @if($role === 'admin')
                                <form action="{{ route('proposal.approve', $proposal->id) }}" method="POST">
                                    @csrf
                                    @method('PUT')
                                    <button class="btn btn-success btn-sm btn-action w-100">
                                        Terima
                                    </button>
                                </form>

                                <form action="{{ route('proposal.reject', $proposal->id) }}" method="POST">
                                    @csrf
                                    @method('PUT')
                                    <button class="btn btn-danger btn-sm btn-action w-100">
                                        Tolak
                                    </button>
                                </form>
                            @endif
                        </div>
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="9" class="text-center text-muted py-3">
                        Belum ada proposal yang review-nya selesai.
                    </td>
                </tr>
            @endforelse
            </tbody>

        </table>
    </div>

</div>

@endsection

@push('scripts')
<script>
document.addEventListener("DOMContentLoaded", function () {
    const searchInput = document.getElementById('table-search');
    if (!searchInput) return;

    const rows = document.querySelectorAll('table tbody tr');

    searchInput.addEventListener('keyup', function () {
        const term = this.value.toLowerCase();

        rows.forEach(row => {
            row.style.display = row.innerText.toLowerCase().includes(term) ? '' : 'none';
        });
    });
});
</script>
@endpush

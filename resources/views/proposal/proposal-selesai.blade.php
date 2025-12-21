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
        padding: 6px 14px;
        border-radius: 6px;
        font-size: 14px;
    }

    .page-subtitle {
        font-size: 15px;
        color: #6c757d;
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
                    <th>Reviewer</th>
                    <th>Status Proposal</th>
                    <th>Skor Reviewer</th>
                    <th>Jumlah Review Masuk</th>
                    <th>Tanggal Review Terakhir</th>
                    <th>Aksi</th>
                </tr>
            </thead>

            <tbody>
            @forelse($proposals as $index => $proposal)
                @php
                    $pengusul   = $proposal->user->name ?? '-';
                    $judul      = $proposal->judul ?? '-';
                    $statusProp = $proposal->status ?? '-';

                    // tampilkan 2 reviewer dari pivot
                    $reviewerNames = optional($proposal->reviewers ?? collect())->pluck('name')->implode(', ') ?: '-';

                    // semua review yang masuk untuk proposal ini
                    $proposalReviews = $proposal->reviews ?? collect();

                    $reviewCount = $proposalReviews->unique('reviewer_id')->count();

                    // tanggal review terakhir
                    $lastReviewDate = optional($proposalReviews->sortByDesc('created_at')->first())->created_at;
                @endphp

                <tr>
                    <td>{{ $index + 1 }}</td>

                    <td>{{ $judul }}</td>

                    <td>{{ $pengusul }}</td>

                    <td>{{ $reviewerNames }}</td>

                    <td>
                        <span class="badge bg-info">{{ $statusProp }}</span>
                    </td>

                    {{-- ✅ Skor masing-masing reviewer (bukan rata-rata) --}}
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

                    <td>
                        <span class="badge bg-secondary">{{ $reviewCount }}</span>
                        <span class="text-muted">/ {{ optional($proposal->reviewers)->count() ?? 0 }}</span>
                    </td>

                    <td>
                        {{ $lastReviewDate ? $lastReviewDate->format('d M Y') : '-' }}
                    </td>

                    <td class="d-flex gap-1 flex-wrap">

                        {{-- Download hasil review per reviewer --}}
                        @foreach($proposalReviews as $rev)
                            <a href="{{ route('review.pdf', $rev->id) }}"
                               class="btn btn-outline-primary btn-sm btn-action">
                                PDF ({{ $rev->reviewer->name ?? 'Reviewer' }})
                            </a>
                        @endforeach

                        {{-- ✅ ADMIN: tiap proposal selalu ada 2 tombol --}}
                        @if($role === 'admin')

                            {{-- Terima Proposal --}}
                            <form action="{{ route('proposal.approve', $proposal->id) }}"
                                  method="POST"
                                  onsubmit="return confirm('Yakin setujui proposal ini?')">
                                @csrf
                                @method('PUT')

                                <button type="submit"
                                        class="btn btn-success btn-sm btn-action">
                                    Terima Proposal
                                </button>
                            </form>

                            {{-- Tolak Proposal --}}
                            <form action="{{ route('proposal.reject', $proposal->id) }}"
                                  method="POST"
                                  onsubmit="return confirm('Yakin tolak proposal ini?')">
                                @csrf
                                @method('PUT')

                                <button type="submit"
                                        class="btn btn-danger btn-sm btn-action">
                                    Tolak Proposal
                                </button>
                            </form>

                        @endif

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

    {{-- 🔁 PREV / NEXT --}}
    <div class="d-flex justify-content-between mt-3">
        @if($role === 'pengaju')
            <a href="{{ route('monitoring.proposalDikirim') }}"
               class="btn btn-outline-success btn-sm">
                &laquo; Daftar Proposal
            </a>
        @else
            <a href="{{ route('monitoring.proposalSedangDireview') }}"
               class="btn btn-outline-success btn-sm">
                &laquo; Proposal Sedang Direview
            </a>
        @endif

        <a href="{{ route('monitoring.proposalDisetujui') }}"
           class="btn btn-outline-success btn-sm">
            Proposal Disetujui &raquo;
        </a>
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

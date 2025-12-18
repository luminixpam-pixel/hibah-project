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
        Berikut daftar seluruh review yang sudah disimpan.
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
                    <th>Total Skor</th>
                    <th>Status Review</th>
                    <th>Catatan</th>
                    <th>Tanggal Review</th>
                    <th>Aksi</th>
                </tr>
            </thead>

           <tbody>
@forelse($reviews as $index => $review)
    @php
        $proposal   = $review->proposal;
        $pengusul   = $proposal->user->name ?? '-';
        $judul      = $proposal->judul ?? '-';
        $statusProp = $proposal->status ?? '-';
        $statusRev  = $review->status ?? '-';

        $reviewer = $review->reviewer->name
                    ?? optional($proposal->reviewers ?? collect())->pluck('name')->implode(', ')
                    ?? '-';

        $templatePdf = $review->template_pdf
                        ?? $proposal->template_pdf
                        ?? null;
    @endphp

    <tr>
        <td>{{ $index + 1 }}</td>

        {{-- Judul Proposal --}}
        <td>{{ $judul }}</td>

        {{-- Pengusul --}}
        <td>{{ $pengusul }}</td>

        {{-- Reviewer --}}
        <td>{{ $reviewer }}</td>

        {{-- Status Proposal --}}
        <td>
            <span class="badge bg-info">
                {{ $statusProp }}
            </span>
        </td>

        {{-- Total Skor --}}
        <td>{{ $review->total_score ?? '-' }}</td>

        {{-- Status Review --}}
        <td>
            <span class="badge bg-secondary">
                {{ $statusRev }}
            </span>
        </td>

        {{-- Catatan --}}
        <td style="max-width: 250px; white-space: pre-wrap;">
            {{ $review->catatan ?? '-' }}
        </td>

        {{-- Tanggal Review --}}
        <td>
            {{ $review->created_at?->format('d M Y') ?? '-' }}
        </td>

        {{-- AKSI --}}
        <td class="d-flex gap-1 flex-wrap">

            {{-- Download Proposal --}}
            @if($proposal)
               <a href="{{ route('review.pdf', $review->id) }}"
                    class="btn btn-outline-primary btn-sm btn-action">
                        Download PDF
                    </a>

            @endif

            {{-- Download Template Penilaian (PDF) --}}
            @if($templatePdf)
                <a href="{{ asset('storage/template_penilaian/' . $templatePdf) }}"
                   target="_blank"
                   class="btn btn-outline-primary btn-sm btn-action">
                    Template PDF
                </a>
            @endif

            {{-- Approve Proposal --}}
            @if($role === 'admin' && $statusProp !== 'Disetujui')
                <form action="{{ route('proposal.approve', $proposal->id) }}"
                      method="POST"
                      onsubmit="return confirm('Yakin setujui proposal ini?')">
                    @csrf
                    @method('PUT')

                    <button type="submit"
                            class="btn btn-success btn-sm btn-action">
                        Approve
                    </button>
                </form>
            @endif
        </td>
    </tr>
@empty
    <tr>
        <td colspan="10" class="text-center text-muted py-3">
            Belum ada review yang selesai.
        </td>
    </tr>
@endforelse
</tbody>

        </table>
    </div>

    {{-- 🔁 PREV / NEXT --}}
    <div class="d-flex justify-content-between mt-3">
        @if($role === 'pengaju')
            {{-- Pengaju: balik ke Daftar Proposal --}}
            <a href="{{ route('monitoring.proposalDikirim') }}"
               class="btn btn-outline-success btn-sm">
                &laquo; Daftar Proposal
            </a>
        @else
            {{-- Admin / Reviewer: tetap ke Proposal Sedang Direview --}}
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

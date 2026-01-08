@extends('layouts.app')

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
    <h4 class="page-title mb-1"> Daftar Proposal Direvisi — Universitas YARSI</h4>
    <p class="page-subtitle mb-4">Berikut daftar seluruh proposal yang Direvisi.</p>

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
                    <th>Status Review</th>
                    <th>Aksi</th>
                </tr>
            </thead>

            <tbody>
                @forelse($proposals as $index => $proposal)
                    @php
                        $judul = $proposal->judul ?? '-';
                        $pengusul = $proposal->user->name ?? '-';

                        // tampilkan 2 reviewer dari pivot
                        $reviewer = optional($proposal->reviewers ?? collect())->pluck('name')->implode(', ') ?: '-';

                        // status review (kalau sudah lewat review selesai, kita tampilkan "Selesai")
                        $statusReview = 'Selesai';

                        // status proposal asli (Ditolak / Direvisi)
                        $statusProposal = $proposal->status ?? '-';
                    @endphp

                    <tr>
                        <td>{{ $index + 1 }}</td>
                        <td>{{ $judul }}</td>
                        <td>{{ $pengusul }}</td>
                        <td>{{ $reviewer }}</td>

                        <td>
                            <span class="badge bg-success">{{ $statusReview }}</span>

                            @if($statusProposal === 'Ditolak')
                                <span class="badge bg-danger ms-1">Ditolak</span>
                            @elseif($statusProposal === 'Direvisi')
                                <span class="badge bg-warning text-dark ms-1">Direvisi</span>
                            @endif
                        </td>

                        <td class="d-flex gap-2 flex-wrap">

                            {{-- Tombol Revisi (edit) --}}
                            <a href="{{ route('proposal.edit', $proposal->id) }}"
                               class="btn btn-warning btn-action">
                                <i class="bi bi-pencil-square"></i> Revisi
                            </a>

                            {{-- Download Proposal --}}
                            <a href="{{ route('proposal.download', $proposal->id) }}"
                               class="btn btn-primary btn-action">
                                <i class="bi bi-download"></i> Download
                            </a>

                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="6" class="text-center text-muted py-3">
                            Belum ada proposal yang Direvisi.
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    {{-- 🔁 PREV / NEXT --}}
    <div class="d-flex justify-content-between mt-3">
        <a href="{{ route('monitoring.proposalDitolak') }}"
           class="btn btn-outline-success btn-sm">
            &laquo; Proposal Ditolak
        </a>

        <a href="{{ route('monitoring.hasilRevisi') }}"
           class="btn btn-outline-success btn-sm">
            Hasil Revisi &raquo;
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

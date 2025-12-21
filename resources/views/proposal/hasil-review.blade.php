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
    <h4 class="page-title mb-1"> Hasil Review Proposal — Universitas YARSI</h4>
    <p class="page-subtitle mb-4">Berikut daftar seluruh proposal yang telah selesai direview.</p>

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
                @forelse($proposals as $i => $proposal)
                    <tr>
                        <td>{{ $i + 1 }}</td>

                        <td>{{ $proposal->judul ?? '-' }}</td>

                        {{-- Pengusul --}}
                        <td>
                            {{ $proposal->user->name ?? ($proposal->nama_ketua ?? '-') }}
                        </td>

                        {{-- Reviewer --}}
                        <td>
                            @php
                                $reviewerNames = '-';
                                if (!empty($proposal->reviewers) && $proposal->reviewers->count() > 0) {
                                    $reviewerNames = $proposal->reviewers->pluck('name')->implode(', ');
                                }
                            @endphp
                            {{ $reviewerNames }}
                        </td>

                        {{-- Status --}}
                        <td>
                            <span class="badge bg-success">Hasil Revisi</span>
                        </td>

                        {{-- Aksi --}}
                        <td>
                            <a href="{{ route('proposal.edit', $proposal->id) }}" class="btn btn-warning btn-action me-1">
                                <i class="bi bi-pencil-square"></i> Revisi
                            </a>

                            <a href="{{ route('proposal.download', $proposal->id) }}" class="btn btn-primary btn-action">
                                <i class="bi bi-download"></i> Download
                            </a>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="6" class="text-center text-secondary py-4">
                            Belum ada data hasil revisi.
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    {{-- 🔁 PREV / NEXT --}}
    <div class="d-flex justify-content-between mt-3">
        <a href="{{ route('monitoring.proposalDirevisi') }}"
           class="btn btn-outline-success btn-sm">
            &laquo; Proposal Direvisi
        </a>

        <a href="{{ route('monitoring.proposalDikirim') }}"
           class="btn btn-outline-success btn-sm">
            Daftar Proposal &raquo;
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

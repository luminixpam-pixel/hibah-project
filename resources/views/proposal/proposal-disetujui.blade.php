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
    <h4 class="page-title mb-1">Daftar Proposal Yang Disetujui — Universitas YARSI</h4>
    <p class="page-subtitle mb-4">Berikut daftar proposal yang telah Disetujui (setelah review selesai).</p>

    {{-- 🔍 SEARCH --}}
    <div class="d-flex justify-content-end mb-3">
        <div class="input-group" style="max-width: 360px;">
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
                    <th>Tanggal</th>
                    <th>Pengusul</th>
                    <th>Judul Proposal</th>
                    <th>Reviewer</th>
                    <th>Status</th>
                    <th>Aksi</th>
                </tr>
            </thead>

            <tbody>
                @forelse($proposals as $index => $proposal)
                    @php
                        $pengusul = $proposal->user->name ?? '-';
                        $judul    = $proposal->judul ?? '-';
                        $tanggal  = $proposal->created_at?->format('d M Y') ?? '-';
                        $reviewer = optional($proposal->reviewers ?? collect())->pluck('name')->implode(', ') ?: '-';
                        $status   = $proposal->status ?? '-';
                    @endphp

                    <tr>
                        <td>{{ $index + 1 }}</td>

                        {{-- Tanggal --}}
                        <td>{{ $tanggal }}</td>

                        {{-- Pengusul --}}
                        <td>{{ $pengusul }}</td>

                        {{-- Judul Proposal --}}
                        <td>{{ $judul }}</td>

                        {{-- Reviewer --}}
                        <td>{{ $reviewer }}</td>

                        {{-- Status --}}
                        <td>
                            <span class="badge bg-success">{{ $status }}</span>
                        </td>

                        {{-- Aksi --}}
                        <td>
                            @if($proposal->file_path)
                                <a href="{{ route('proposal.download', $proposal->id) }}"
                                   class="btn btn-primary btn-action">
                                    <i class="bi bi-download"></i> Download
                                </a>
                            @else
                                <button class="btn btn-secondary btn-action" disabled>
                                    <i class="bi bi-x-circle"></i> Tidak ada file
                                </button>
                            @endif
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="7" class="text-center text-muted py-3">
                            Belum ada proposal yang disetujui.
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    {{-- 🔁 PREV / NEXT --}}
    <div class="d-flex justify-content-between mt-3">
        <a href="{{ route('monitoring.reviewSelesai') }}"
           class="btn btn-outline-success btn-sm">
            &laquo; Review Selesai
        </a>

        <a href="{{ route('monitoring.proposalDitolak') }}"
           class="btn btn-outline-success btn-sm">
            Proposal Ditolak &raquo;
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

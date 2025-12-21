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
    <h4 class="page-title mb-1"> Daftar Proposal Ditolak — Universitas YARSI</h4>
    <p class="page-subtitle mb-4">Berikut daftar seluruh proposal yang Ditolak.</p>

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
                    <th>Tanggal</th>
                    <th>Pengusul</th>
                    <th>Judul Proposal</th>
                    <th>Status </th>
                    <th>Aksi</th>
                </tr>
            </thead>

            <tbody>
                @forelse($proposals as $index => $proposal)
                    @php
                        $pengusul = $proposal->user->name ?? '-';
                        $judul    = $proposal->judul ?? '-';
                        $tanggal  = $proposal->updated_at?->format('d M Y') ?? ($proposal->created_at?->format('d M Y') ?? '-');
                    @endphp

                    <tr>
                        <td>{{ $index + 1 }}</td>
                        <td>{{ $tanggal }}</td>
                        <td>{{ $pengusul }}</td>
                        <td>{{ $judul }}</td>
                        <td><span class="badge bg-success">Ditolak</span></td>
                        <td>
                            <a href="{{ route('proposal.download', $proposal->id) }}"
                               class="btn btn-primary btn-action">
                                <i class="bi bi-download"></i> Download
                            </a>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="6" class="text-center text-muted py-3">
                            Belum ada proposal yang ditolak.
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    {{-- 🔁 PREV / NEXT --}}
    <div class="d-flex justify-content-between mt-3">
        <a href="{{ route('monitoring.proposalDisetujui') }}"
           class="btn btn-outline-success btn-sm">
            &laquo; Proposal Disetujui
        </a>

        <a href="{{ route('monitoring.proposalDirevisi') }}"
           class="btn btn-outline-success btn-sm">
            Proposal Direvisi &raquo;
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

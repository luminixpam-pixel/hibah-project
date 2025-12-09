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
                <tr>
                    <td>1</td>
                    <td>Analisis Efektivitas Obat Herbal</td>
                    <td>Prof. Pratiwi P. Sudarmono</td>
                    <td>Dr. Ahmad Faisal</td>
                    <td><span class="badge bg-success">Ditolak</span></td>
                    <td>

                        <button class="btn btn-primary btn-action">
                            <i class="bi bi-download"></i> Download
                        </button>
                    </td>
                </tr>

                <tr>
                    <td>2</td>
                    <td>Pemanfaatan AI untuk Deteksi Penyakit Kulit</td>
                    <td>Dr. Ratna Sitompul</td>
                    <td>Prof. Hartono</td>
                    <td><span class="badge bg-success">Ditolak</span></td>
                    <td>
                        <button class="btn btn-primary btn-action">
                            <i class="bi bi-download"></i> Download
                        </button>
                    </td>
                </tr>

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

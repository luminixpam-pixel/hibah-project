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
    <h4 class="page-title mb-1">📄 Proposal Selesai— Universitas YARSI</h4>
    <p class="page-subtitle mb-4">Berikut daftar seluruh proposal yang telah selesai.</p>

    {{-- DOWNLOAD ALL --}}
    <div class="d-flex align-items-center mb-4">
        <button class="btn btn-primary d-flex align-items-center">
            <i class="bi bi-file-earmark-arrow-down me-2"></i> Download Semua (Word)
        </button>
        <span class="ms-2 text-muted">Unduh seluruh laporan akhir dalam bentuk dokumen Word.</span>
    </div>

    {{-- TABLE --}}
    <div class="table-responsive">
        <table class="table table-striped table-hover align-middle shadow-sm">
            <thead>
                <tr>
                    <th>No</th>
                    <th>Reviewer</th>
                    <th>Pengusul</th>
                    <th>Judul Proposal</th>
                    <th>Status</th>
                    <th>Aksi</th>
                </tr>
            </thead>

            <tbody>
                <tr>
                    <td>1</td>
                    <td>Analisis Efektivitas Obat Herbal</td>
                    <td>Prof. Pratiwi P. Sudarmono</td>
                    <td>Dr. Ahmad Faisal</td>
                    <td><span class="badge bg-success">Disetujui</span></td>
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

</div>

@endsection

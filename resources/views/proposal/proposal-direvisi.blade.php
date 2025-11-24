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
    <h4 class="page-title mb-1"> Proposal Direvisi — Universitas YARSI</h4>
    <p class="page-subtitle mb-4">Berikut daftar seluruh proposal yang Direvisi.</p>


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
                <tr>
                    <td>1</td>
                    <td>Analisis Efektivitas Obat Herbal</td>
                    <td>Prof. Pratiwi P. Sudarmono</td>
                    <td>Dr. Ahmad Faisal</td>
                    <td><span class="badge bg-success">Selesai</span></td>
                    <td>
                        <button class="btn btn-warning btn-action me-1">
                            <i class="bi bi-pencil-square"></i> Revisi
                        </button>

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
                    <td><span class="badge bg-success">Selesai</span></td>
                    <td>
                        <button class="btn btn-warning btn-action me-1">
                            <i class="bi bi-pencil-square"></i> Revisi
                        </button>

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

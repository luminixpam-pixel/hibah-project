@extends('layouts.app')

@section('content')

<div class="container mt-4">

    {{-- TITLE --}}
    <h4 class="fw-bold">Daftar Proposal Hibah Internal Universitas YARSI</h4>

    {{-- DOWNLOAD BUTTON --}}
    <div class="mt-3">
        <button class="btn btn-success d-flex align-items-center">
            <i class="bi bi-file-earmark-word me-2"></i> WORD
        </button>
        <span class="ms-2">Download Laporan Akhir secara Keseluruhan</span>
    </div>

    {{-- FILTER SECTION --}}
    <div class="card mt-4">
        <div class="card-body">

            <div class="row">

                {{-- Filter kiri --}}
                <div class="col-md-6">
                    <p class="fw-semibold text-success">Filter Berdasarkan:</p>

                    <label>Status Proposal</label>
                    <select class="form-select mb-3">
                        <option>Dikirim</option>
                        <option>Ditolak</option>
                        <option>Disetujui</option>
                        <option>Direview</option>
                        <option>Hasil Review</option>
                        <option>Selesai Direview</option>
                    </select>

                    <label>Periode Hibah</label>
                    <select class="form-select mb-3">
                        <option>Semester Genap 2025 / Tahun 2025</option>
                        <option>Semester Ganjil 2025</option>
                    </select>

                    <label>Status Proposal</label>
                    <select class="form-select mb-3">
                        <option>Dikirim</option>
                        <option>Direvisi</option>
                        <option>Disetujui</option>
                    </select>
                </div>

                {{-- Filter kanan --}}
                <div class="col-md-6">

                    <p class="fw-semibold text-success">Filter Berdasarkan:</p>

                    <div class="form-check">
                        <input class="form-check-input" type="radio" name="filter" checked>
                        <label class="form-check-label">Fakultas</label>
                    </div>

                    <div class="form-check">
                        <input class="form-check-input" type="radio" name="filter">
                        <label class="form-check-label">Prodi</label>
                    </div>

                    <button class="btn btn-success mt-3 px-4">SEND</button>
                </div>

            </div>

        </div>
    </div>

    {{-- TABLE --}}
    <div class="table-responsive mt-4">
        <table class="table table-bordered align-middle">
            <thead class="table-light">
                <tr>
                    <th>No</th>
                    <th>Judul Proposal</th>
                    <th>Pengusul</th>
                    <th>Reviewer</th>
                    <th>Status</th>
                    <th>Aksi</th>
                </tr>
            </thead>

            <tbody>
                <tr>
                    <td>1</td>
                    <td>Analisis Efektivitas Obat Herbal</td>
                    <td>Prof.dr. Pratiwi Pujiestari Sudarmono, Ph.D, Sp.M.K (K)</td>
                    <td>Prof.dr. Pratiwi Pujiestari Sudarmono, Ph.D, Sp.M.K (K)</td>
                    <td>Disetujui</td>
                    <td><a href="#" class="text-primary">Detail</a></td>
                </tr>

                <tr>
                    <td>2</td>
                    <td>Analisis Efektivitas Obat Herbal</td>
                    <td>Achmad Sofwan, dr. H. M.Kes., PA.</td>
                    <td>Prof.dr. Pratiwi Pujiestari Sudarmono, Ph.D, Sp.M.K (K)</td>
                    <td>Ditolak</td>
                    <td><a href="#" class="text-primary">Detail</a></td>
                </tr>

                <tr>
                    <td>3</td>
                    <td>Analisis Efektivitas Obat Herbal</td>
                    <td>Ratna Sitompul, Dr. dr. Sp.M(K)</td>
                    <td>Prof.dr. Pratiwi Pujiestari Sudarmono, Ph.D, Sp.M.K (K)</td>
                    <td>Ditolak</td>
                    <td><a href="#" class="text-primary">Detail</a></td>
                </tr>

            </tbody>
        </table>
    </div>

</div>

@endsection

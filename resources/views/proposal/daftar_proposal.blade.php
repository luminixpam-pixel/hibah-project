@extends('layouts.app')

@section('content')

<div class="container mt-4">

    <h4 class="fw-bold">Daftar Proposal Hibah Internal Universitas YARSI</h4>

    <div class="card mt-4">
        <div class="card-body">
            <div class="row">

                <div class="col-md-6">
                    <p class="fw-semibold text-success">Filter Berdasarkan:</p>

                    <label>Periode Hibah</label>
                    <select class="form-select mb-3">
                        <option>Semester Genap 2025 / Tahun 2025</option>
                        <option>Semester Ganjil 2025</option>
                    </select>
                </div>

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

                    <button class="btn btn-success mt-3 px-4">Cari</button>
                </div>

            </div>
        </div>
    </div>

    <div class="table-responsive mt-4">
        <table class="table table-bordered align-middle">
            <thead class="table-light">
                <tr>
                    <th>No</th>
                    <th>Judul Proposal</th>
                    <th>Pengusul</th>
                    <th>Aksi</th>
                </tr>
            </thead>

            <tbody>
                @forelse ($proposals as $index => $proposal)
                <tr>
                    <td>{{ $index + 1 }}</td>
                    <td>{{ $proposal->judul }}</td>
                    <td>{{ $proposal->nama_ketua }}</td>

                    <td>
                        @if ($proposal->file_path)
                            <a href="{{ route('proposal.download', $proposal->id) }}" class="text-primary">
                                Download
                            </a>
                        @else
                            <span class="text-muted">No File</span>
                        @endif
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="6" class="text-center text-muted">Belum ada proposal yang dikirim.</td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>

</div>

@endsection

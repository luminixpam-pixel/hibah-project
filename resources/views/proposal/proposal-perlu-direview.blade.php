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
    <h4 class="page-title mb-1"> Daftar Proposal Yang Perlu Direview — Universitas YARSI</h4>
    <p class="page-subtitle mb-4">Berikut daftar seluruh proposal yang perlu direview.</p>

    {{-- TABLE --}}
    <div class="table-responsive">
        <table class="table table-striped table-hover align-middle shadow-sm">
            <thead>
                <tr>
                    <th>No</th>
                    <th>Reviewer</th>
                    <th>Pengusul</th>
                    <th>Judul Proposal</th>
                    <th>Aksi</th>
                </tr>
            </thead>

            <tbody>
                @forelse ($proposals as $index => $proposal)
                <tr>
                    <td>{{ $index + 1 }}</td>

                    {{-- Reviewer: belum ada kolomnya → sementara "-" --}}
                    <td>{{ $proposal->reviewer ?? '-' }}</td>

                    {{-- Pengusul (ketua) --}}
                    <td>{{ $proposal->nama_ketua }}</td>

                    {{-- Judul Proposal --}}
                    <td>{{ $proposal->judul }}</td>

                    <td>
                        <a href="#"
                           class="btn btn-primary btn-action">
                            <i class="bi bi-download"></i> Beri Review
                        </a>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="5" class="text-center text-muted py-3">
                        Belum ada proposal yang perlu direview.
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>

</div>

@endsection

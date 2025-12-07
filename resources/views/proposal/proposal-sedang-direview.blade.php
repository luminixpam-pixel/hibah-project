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
    <h4 class="page-title mb-1"> Daftar Proposal Sedang Direview — Universitas YARSI</h4>
    <p class="page-subtitle mb-4">Berikut daftar seluruh proposal yang sedang direview.</p>

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
                @forelse($proposals as $index => $proposal)
                    <tr>
                        <td>{{ $index + 1 }}</td>
                        <td>{{ $proposal->reviewer ?? '-' }}</td>
                        <td>{{ $proposal->nama_ketua }}</td>
                        <td>{{ $proposal->judul }}</td>
                        <td>
                            <span class="badge bg-success">Sedang Direview</span>
                        </td>
                        <td>
                            @if(auth()->user()->role === 'reviewer')
                                @if($proposal->reviewer === auth()->user()->name)
                                    {{-- tombol khusus reviewer yang ditugaskan --}}
                                    <a href="{{ route('reviewer.isi-review', $proposal->id) }}"
                                       class="btn btn-success btn-sm">
                                        Beri Review
                                    </a>
                                @else
                                    <span class="text-muted">Bukan reviewer proposal ini</span>
                                @endif
                            @else
                                {{-- misalnya admin, kalau mau bisa dikasih tombol Detail di sini --}}
                                -
                            @endif
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="6" class="text-center text-muted py-3">
                            Belum ada proposal yang sedang direview.
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

</div>

@endsection

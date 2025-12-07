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

                    {{-- REVIEWER: dropdown hanya untuk admin --}}
                    <td>
                        @if(auth()->user()->role === 'admin')
                            <form action="{{ route('proposal.assignReviewer', $proposal->id) }}" method="POST">
                                @csrf
                                @method('PATCH')

                                <select name="reviewer"
                                        class="form-select form-select-sm"
                                        onchange="this.form.submit()">
                                    <option value="">- Pilih Reviewer -</option>

                                    @foreach ($reviewers as $rev)
                                        <option value="{{ $rev->name }}"
                                            {{ $proposal->reviewer == $rev->name ? 'selected' : '' }}>
                                            {{ $rev->name }}
                                        </option>
                                    @endforeach
                                </select>
                            </form>
                        @else
                            {{-- selain admin hanya lihat nama reviewer --}}
                            {{ $proposal->reviewer ?? '-' }}
                        @endif
                    </td>

                    {{-- Pengusul (ketua) --}}
                    <td>{{ $proposal->nama_ketua }}</td>

                    {{-- Judul Proposal --}}
                    <td>{{ $proposal->judul }}</td>

                    <td>
                        @if(auth()->user()->role === 'reviewer')
                            {{-- Reviewer hanya boleh klik jika dia yang ditetapkan --}}
                            @if(!$proposal->reviewer)
                                <span class="text-muted">Reviewer belum ditetapkan</span>
                            @elseif(auth()->user()->name === $proposal->reviewer)
                                <a href="{{ route('reviewer.isi-review', $proposal->id) }}" class="btn btn-success btn-sm">
                                    Beri Review
                                </a>
                            @else
                                <span class="text-muted">Bukan reviewer proposal ini</span>
                            @endif
                        @else
                            {{-- Admin (atau role lain) tetap bisa buka halaman review --}}
                            <a href="{{ route('reviewer.isi-review', $proposal->id) }}" class="btn btn-success btn-sm">
                                Beri Review
                            </a>
                        @endif
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

@extends('layouts.app')

@section('content')
<div class="container mt-4">

    <h4 class="mb-3">Detail Proposal — {{ $proposal->judul }}</h4>

    <div class="card mb-3">
        <div class="card-body">
            <p><strong>Judul:</strong> {{ $proposal->judul }}</p>
            <p><strong>Nama Ketua:</strong> {{ $proposal->nama_ketua }}</p>
            <p><strong>Biaya:</strong> {{ $proposal->biaya ?? '-' }}</p>
            <p><strong>Status:</strong> {{ $proposal->status ?? '-' }}</p>

            <p><strong>Anggota:</strong>
                @php
                    $anggota = $proposal->anggota ? json_decode($proposal->anggota, true) : [];
                @endphp
                @if($anggota && is_array($anggota))
                    {{ implode(', ', $anggota) }}
                @else
                    -
                @endif
            </p>

            @if($proposal->file_path)
                <a href="{{ route('proposal.download', $proposal->id) }}"
                   class="btn btn-primary btn-sm">
                    Download Proposal
                </a>
            @else
                <span class="text-muted">File belum diupload</span>
            @endif

            <a href="{{ route('proposal.index') }}" class="btn btn-link btn-sm">
                Kembali ke Daftar Proposal
            </a>
        </div>
    </div>

</div>
@endsection

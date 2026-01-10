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
                {{--
                   Karena di Model Proposal.php sudah ada protected $casts = ['anggota' => 'array'],
                   kita tidak perlu lagi menggunakan json_decode().
                   Variabel $proposal->anggota sudah otomatis menjadi Array PHP.
                --}}
                @if(!empty($proposal->anggota) && is_array($proposal->anggota))
                    {{ implode(', ', $proposal->anggota) }}
                @else
                    -
                @endif
            </p>

            <hr>

            <div class="d-flex gap-2">
                @if($proposal->file_path)
                    <a href="{{ route('proposal.download', $proposal->id) }}"
                       class="btn btn-primary btn-sm">
                        <i class="bi bi-download"></i> Download Proposal
                    </a>
                @else
                    <span class="text-muted">File belum diupload</span>
                @endif

                <a href="{{ route('proposal.index') }}" class="btn btn-outline-secondary btn-sm">
                    Kembali ke Daftar Proposal
                </a>
            </div>
        </div>
    </div>

</div>
@endsection

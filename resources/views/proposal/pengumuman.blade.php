@extends('layouts.app')

@section('content')
<div class="container">
    <h3 class="mb-4">📢 Pengumuman Proposal Lolos Pendanaan</h3>

    @if($proposals->count())
        <div class="table-responsive">
            <table class="table table-bordered">
                <thead class="table-light">
                    <tr>
                        <th>No</th>
                        <th>Judul Proposal</th>
                        <th>Pengusul</th>
                        <th>Tanggal Disetujui</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($proposals as $index => $proposal)
                        <tr>
                            <td>{{ $index + 1 }}</td>
                            <td>{{ $proposal->judul }}</td>
                            <td>{{ $proposal->user->name ?? '-' }}</td>
                            <td>{{ $proposal->updated_at->format('d M Y') }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    @else
        <div class="alert alert-info">
            Belum ada proposal yang lolos pendanaan.
        </div>
    @endif
</div>
@endsection

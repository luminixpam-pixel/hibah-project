@extends('layouts.app')

@section('content')
<div class="container mt-4">

    <h4 class="fw-bold mb-3">Hasil Review Proposal</h4>

    <table class="table table-bordered">
        <thead class="table-success text-center">
            <tr>
                <th>Reviewer</th>
                <th>Judul Proposal</th>
                <th>Total Score</th>
                <th>Kategori</th>
            </tr>
        </thead>

        <tbody>
            @foreach ($reviews as $r)
            <tr>
                <td>{{ $r->reviewer->name }}</td>
                <td>{{ $r->proposal->judul }}</td>
                <td class="text-center fw-bold">{{ $r->total_score }}</td>
                <td class="text-center">
                    @if($r->total_score >= 401)
                        <span class="badge bg-success">Baik Sekali</span>
                    @elseif($r->total_score >= 301)
                        <span class="badge bg-primary">Baik</span>
                    @elseif($r->total_score >= 201)
                        <span class="badge bg-info text-dark">Sedang</span>
                    @elseif($r->total_score >= 101)
                        <span class="badge bg-warning text-dark">Kurang</span>
                    @else
                        <span class="badge bg-danger">Sangat Kurang</span>
                    @endif
                </td>
            </tr>
            @endforeach
        </tbody>
    </table>

</div>
@endsection

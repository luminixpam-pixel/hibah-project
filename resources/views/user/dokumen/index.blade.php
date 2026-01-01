@extends('layouts.app')

@section('title','Dokumen')

@section('content')
<h4>Dokumen dari Admin</h4>

<table class="table table-striped">
<thead>
<tr>
    <th>Judul</th>
    <th>Tanggal</th>
    <th>Aksi</th>
</tr>
</thead>
<tbody>
@foreach($docs as $doc)
<tr>
    <td>{{ $doc->judul }}</td>
    <td>{{ $doc->created_at->format('d M Y') }}</td>
    <td>
        <a href="{{ route('dokumen.download',$doc->id) }}" class="btn btn-sm btn-success">
            Download
        </a>
    </td>
</tr>
@endforeach
</tbody>
</table>
@endsection


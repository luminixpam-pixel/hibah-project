@extends('layouts.app')

@section('title','Upload Dokumen')

@section('content')
<h4>Upload Dokumen</h4>

<form action="{{ route('admin.dokumen.store') }}" method="POST" enctype="multipart/form-data">
@csrf
<div class="mb-3">
    <label>Judul Dokumen</label>
    <input type="text" name="judul" class="form-control" required>
</div>

<div class="mb-3">
    <label>File</label>
    <input type="file" name="file" class="form-control" required>
</div>

<button class="btn btn-success">Upload</button>
</form>

<hr>

<table class="table table-bordered">
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
        <a href="{{ route('dokumen.download',$doc->id) }}" class="btn btn-sm btn-primary">
            Download
        </a>
    </td>
</tr>
@endforeach
</tbody>
</table>
@endsection

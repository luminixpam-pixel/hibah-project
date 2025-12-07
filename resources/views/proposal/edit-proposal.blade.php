@extends('layouts.app')

@section('content')

<div class="container mt-4">

    <h4 class="mb-3">Edit Proposal</h4>

    @if($errors->any())
        <div class="alert alert-danger">
            <ul class="mb-0">
                @foreach($errors->all() as $err)
                    <li>{{ $err }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <form action="{{ route('proposal.update', $proposal->id) }}" method="POST" enctype="multipart/form-data">
        @csrf
        @method('PUT')

        <div class="mb-3">
            <label class="form-label">Nama Ketua</label>
            <input type="text" name="nama_ketua" class="form-control"
                   value="{{ old('nama_ketua', $proposal->nama_ketua) }}" required>
        </div>

        <div class="mb-3">
            <label class="form-label">Anggota</label>
            @php
                $anggota = $proposal->anggota ? json_decode($proposal->anggota, true) : [];
            @endphp

            <div id="anggota-container">
                @if(!empty($anggota))
                    @foreach($anggota as $a)
                        <div class="d-flex gap-2 mb-2 anggota-row">
                            <input type="text" name="anggota[]" class="form-control" value="{{ $a }}">
                            <button type="button" class="btn btn-danger btn-sm remove-anggota">Hapus</button>
                        </div>
                    @endforeach
                @else
                    <div class="d-flex gap-2 mb-2 anggota-row">
                        <input type="text" name="anggota[]" class="form-control">
                    </div>
                @endif
            </div>
            <button type="button" class="btn btn-sm btn-primary mt-2" id="addAnggotaBtn">+ Tambah Anggota</button>
        </div>

        <div class="mb-3">
            <label class="form-label">Biaya</label>
            <input type="text" name="biaya" class="form-control"
                   value="{{ old('biaya', $proposal->biaya) }}">
        </div>

        <div class="mb-3">
            <label class="form-label">Judul Proposal</label>
            <input type="text" name="judul" class="form-control"
                   value="{{ old('judul', $proposal->judul) }}">
        </div>

        <div class="mb-3">
            <label class="form-label">File Proposal</label>
            @if($proposal->file_path)
                <p class="mb-1">
                    File saat ini:
                    <a href="{{ route('proposal.download', $proposal->id) }}" target="_blank">
                        {{ basename($proposal->file_path) }}
                    </a>
                </p>
            @endif
            <input type="file" name="file" class="form-control" accept=".pdf,.doc,.docx">
            <small class="text-muted">Kosongkan jika tidak ingin mengganti file.</small>
        </div>

        <button type="submit" class="btn btn-success">Simpan Perubahan</button>
        <a href="{{ route('proposal.index') }}" class="btn btn-secondary">Kembali</a>
    </form>

</div>

@endsection

@push('scripts')
<script>
document.addEventListener("DOMContentLoaded", function () {
    const anggotaContainer = document.getElementById("anggota-container");
    const addBtn = document.getElementById("addAnggotaBtn");

    if(addBtn){
        addBtn.addEventListener("click", function () {
            const div = document.createElement("div");
            div.classList.add("d-flex", "gap-2", "mb-2", "anggota-row");
            div.innerHTML = `
                <input type="text" name="anggota[]" class="form-control">
                <button type="button" class="btn btn-danger btn-sm remove-anggota">Hapus</button>
            `;
            anggotaContainer.appendChild(div);
        });
    }

    document.addEventListener("click", function(e){
        if(e.target.classList.contains("remove-anggota")){
            e.target.parentElement.remove();
        }
    });
});
</script>
@endpush

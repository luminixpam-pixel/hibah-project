@extends('layouts.app')

@section('title', 'Unggah Proposal')

@section('content')

<div class="container">

    <h4 class="fw-bold mb-4">Kirimkan Pengajuan Proposal</h4>

    {{-- CARD 1 --}}
    <div class="card shadow-sm border-0 mb-3">
        <div class="card-header bg-light fw-semibold">Nama Ketua?</div>
        <div class="card-body p-3">
            <textarea class="form-control editor-box" id="ketua"></textarea>
        </div>
    </div>

    {{-- CARD 2 --}}
    <div class="card shadow-sm border-0 mb-3">
        <div class="card-header bg-light fw-semibold">Tuliskan Anggota?</div>
        <div class="card-body p-3">
            <textarea class="form-control editor-box" id="anggota"></textarea>
        </div>
    </div>

    {{-- CARD 3 --}}
    <div class="card shadow-sm border-0 mb-3">
        <div class="card-header bg-light fw-semibold">
            Berapa biaya yang dibutuhkan?
            <span class="text-muted">(Rp. nominal total biaya) (terbilang)</span>
        </div>
        <div class="card-body p-3">
            <textarea class="form-control editor-box" id="biaya"></textarea>
        </div>
    </div>

    {{-- CARD 4 --}}
    <div class="card shadow-sm border-0 mb-3">
        <div class="card-header bg-light fw-semibold">Judul Proposal?</div>
        <div class="card-body p-3">
            <textarea class="form-control editor-box" id="judul"></textarea>
        </div>
    </div>

    {{-- UPLOAD FILE CARD --}}
    <div class="card shadow-sm border-0 mb-4">
        <div class="card-body p-4">

            <label class="fw-semibold">Upload Proposal</label>

            <div class="upload-area mt-3 mb-2" id="uploadBox">
                <div class="text-center">
                    <i class="bi bi-arrow-down-circle" style="font-size: 60px;"></i>
                    <p class="mt-3 text-muted">
                        Anda dapat seret dan lepas berkas di sini untuk menambahkan.
                    </p>
                </div>
                <input type="file" id="fileInput" class="d-none">
            </div>

            <small class="text-muted">Ukuran maksimum untuk berkas: 100 MB, lampiran maksimum: 15</small>
        </div>
    </div>

    {{-- BUTTONS --}}
    <div class="d-flex justify-content-start gap-2 mb-5">
        <button class="btn btn-success px-4">Kirim</button>
        <a href="{{ route('monitoring.data') }}" class="btn btn-secondary px-4">Batal</a>
    </div>

</div>

@endsection

@push('styles')
<style>
    .editor-box {
        height: 110px;
        resize: none;
    }

    .upload-area {
        border: 2px dashed #1fbd4c;
        border-radius: 12px;
        padding: 40px;
        cursor: pointer;
        background: #f8fff9;
        transition: 0.2s ease-in-out;
    }

    .upload-area:hover {
        background: #ecffef;
        border-color: #17a445;
    }
</style>
@endpush

@push('scripts')
<script>
    // upload klik area
    document.getElementById("uploadBox").addEventListener("click", () => {
        document.getElementById("fileInput").click();
    });

    // highlight drag
    let uploadBox = document.getElementById("uploadBox");

    uploadBox.addEventListener("dragover", (e) => {
        e.preventDefault();
        uploadBox.style.background = "#e5ffe9";
        uploadBox.style.borderColor = "#1fbd4c";
    });

    uploadBox.addEventListener("dragleave", () => {
        uploadBox.style.background = "#f8fff9";
        uploadBox.style.borderColor = "#1fbd4c";
    });

    uploadBox.addEventListener("drop", (e) => {
        e.preventDefault();
        uploadBox.style.background = "#f8fff9";
        document.getElementById("fileInput").files = e.dataTransfer.files;
    });
</script>
@endpush

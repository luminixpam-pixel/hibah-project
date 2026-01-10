@extends('layouts.app')

@section('content')
<div class="container mt-4">
    <h4 class="mb-3 fw-bold">Edit & Revisi Proposal</h4>

    {{-- Alert untuk Error Limit atau Validasi --}}
    @if(session('error'))
        <div class="alert alert-danger">{{ session('error') }}</div>
    @endif

    <form action="{{ route('proposal.update', $proposal->id) }}" method="POST" enctype="multipart/form-data">
        @csrf
        @method('PUT')

        {{-- NAMA KETUA: Auto-filled & Readonly --}}
        <div class="mb-3">
            <label class="form-label fw-bold">Nama Ketua</label>
            <input type="text" name="nama_ketua" class="form-control bg-light"
                   value="{{ auth()->user()->name }}" readonly>
            <small class="text-muted">Diambil otomatis dari nama akun Anda.</small>
        </div>

        {{-- ANGGOTA: Auto-complete dari Seeder ($users) --}}
        <div class="mb-3">
            <label class="form-label fw-bold">Anggota</label>
            @php
                $anggota = is_array($proposal->anggota) ? $proposal->anggota : (json_decode($proposal->anggota, true) ?? []);
            @endphp

            <div id="anggota-container">
                @foreach($anggota as $a)
                    <div class="d-flex gap-2 mb-2 anggota-row">
                        <input type="text" name="anggota[]" class="form-control" value="{{ $a }}" list="user-list" placeholder="Ketik nama anggota...">
                        <button type="button" class="btn btn-danger btn-sm remove-anggota">Hapus</button>
                    </div>
                @endforeach
            </div>
            <button type="button" class="btn btn-sm btn-outline-primary mt-2" id="addAnggotaBtn">+ Tambah Anggota</button>
        </div>

        {{-- Data List dari Seeder --}}
        <datalist id="user-list">
            @foreach($users as $user)
                <option value="{{ $user->name }}">
            @endforeach
        </datalist>

        {{-- BIAYA: Format Ribuan --}}
        <div class="mb-3">
            <label class="form-label fw-bold">Biaya (Rp)</label>
            <input type="text" id="biaya_display" class="form-control"
                   value="{{ number_format($proposal->biaya, 0, ',', '.') }}" required>
            <input type="hidden" name="biaya" id="biaya_real" value="{{ $proposal->biaya }}">
        </div>

        <div class="mb-3">
            <label class="form-label fw-bold">Judul Proposal</label>
            <input type="text" name="judul" class="form-control" value="{{ old('judul', $proposal->judul) }}" required>
        </div>

        <div class="mb-3 border p-3 rounded bg-light">
            <label class="form-label fw-bold">Update File Proposal (PDF)</label>
            <input type="file" name="file" class="form-control" accept=".pdf">
            <small class="text-danger">*Kosongkan jika tidak ingin mengganti file.</small>
        </div>

        <button type="submit" class="btn btn-success px-4 fw-bold">Simpan Perubahan & Kirim Revisi</button>
        <a href="{{ route('proposal.index') }}" class="btn btn-secondary">Batal</a>
    </form>
</div>
@endsection

@push('scripts')
<script>
document.addEventListener("DOMContentLoaded", function () {
    const biayaDisplay = document.getElementById('biaya_display');
    const biayaReal = document.getElementById('biaya_real');

    // 1. Logika Format Rupiah saat Input
    biayaDisplay.addEventListener('input', function(e) {
        let value = this.value.replace(/[^,\d]/g, '').toString();
        let split = value.split(',');
        let sisa = split[0].length % 3;
        let rupiah = split[0].substr(0, sisa);
        let ribuan = split[0].substr(sisa).match(/\d{3}/gi);

        if (ribuan) {
            let separator = sisa ? '.' : '';
            rupiah += separator + ribuan.join('.');
        }

        this.value = rupiah;
        biayaReal.value = value.replace(/\./g, '');
    });

    // 2. Tambah Baris Anggota (Ditambah atribut list="user-list" agar tetap AUTO)
    const container = document.getElementById("anggota-container");
    document.getElementById("addAnggotaBtn").addEventListener("click", function () {
        const div = document.createElement("div");
        div.className = "d-flex gap-2 mb-2 anggota-row";
        div.innerHTML = `
            <input type="text" name="anggota[]" class="form-control" list="user-list" placeholder="Ketik nama anggota...">
            <button type="button" class="btn btn-danger btn-sm remove-anggota">Hapus</button>
        `;
        container.appendChild(div);
    });

    // 3. Hapus Baris Anggota
    document.addEventListener("click", function(e){
        if(e.target.classList.contains("remove-anggota")){
            e.target.parentElement.remove();
        }
    });
});
</script>
@endpush++

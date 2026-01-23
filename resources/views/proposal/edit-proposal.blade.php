@extends('layouts.app')

@section('content')

 @if ($errors->any())
                    <div class="alert alert-danger">{{ implode('', $errors->all(':message')) }}</div>
                @endif

<div class="container mt-5 mb-5">
    <div class="row justify-content-center">
        <div class="col-lg-9">
                  @if ($errors->any())
                    <div class="alert alert-danger">{{ implode('', $errors->all(':message')) }}</div>
                @endif

            {{-- Card Header & Container --}}
            <div class="card border-0 shadow-sm" style="border-radius: 20px; overflow: hidden;">
                <div class="card-header p-4 border-0" style="background-color: #fff9c4;">
                    <div class="d-flex align-items-center">
                        <div class="rounded-circle d-flex align-items-center justify-content-center shadow-sm"
                             style="background-color: #fbc02d; width: 45px; height: 45px;">
                            <i class="bi bi-pencil-square text-white fs-4"></i>
                        </div>
                        <div class="ms-3">
                            <h4 class="mb-0 fw-bold" style="color: #827717;">Edit & Revisi Proposal</h4>
                            <p class="text-muted small mb-0">Perbarui informasi proposal Anda di sini</p>
                        </div>
                    </div>
                </div>


                <div class="card-body p-4 p-md-5 bg-white">
                    {{-- Alert untuk Error --}}
                    @if(session('error'))
                        <div class="alert alert-danger border-0 shadow-sm" style="border-radius: 12px;">
                            <i class="bi bi-exclamation-triangle-fill me-2"></i> {{ session('error') }}
                        </div>
                    @endif

                    <form action="{{ route('proposal.update', $proposal->id) }}" method="POST" enctype="multipart/form-data">
                        @csrf
                    @method('PUT')

                     {{-- Input Hidden yang sangat penting agar validasi tidak gagal --}}
                    <input type="hidden" name="fakultas_prodi" value="{{ $proposal->fakultas_prodi }}">
                    <input type="hidden" name="nama_ketua" value="{{ $proposal->nama_ketua }}">

                        <div class="row g-4">
                            {{-- NAMA KETUA --}}
                            <div class="col-md-6">
                                <label class="form-label fw-bold text-secondary">Nama Ketua</label>
                                <div class="input-group">
                                    <span class="input-group-text border-0 bg-light"><i class="bi bi-person-fill"></i></span>
                                    <input type="text" name="nama_ketua" class="form-control border-0 bg-light"
                                           value="{{ auth()->user()->name }}" readonly style="border-radius: 0 10px 10px 0;">
                                </div>
                                <small class="text-muted ms-1">Otomatis sesuai akun</small>
                            </div>

                            {{-- BIAYA --}}
                            <div class="col-md-6">
                                <label class="form-label fw-bold text-secondary">Estimasi Biaya (Rp)</label>
                                <div class="input-group">
                                    <span class="input-group-text border-0 bg-light" style="color: #2e7d32;"><b>Rp</b></span>
                                    <input type="text" id="biaya_display" class="form-control border-0 bg-light"
                                           value="{{ number_format($proposal->biaya, 0, ',', '.') }}" required
                                           style="border-radius: 0 10px 10px 0; font-weight: bold; color: #2e7d32;">
                                    <input type="hidden" name="biaya" id="biaya_real" value="{{ $proposal->biaya }}">
                                </div>
                            </div>

                            {{-- JUDUL PROPOSAL --}}
                            <div class="col-12">
                                <label class="form-label fw-bold text-secondary">Judul Proposal</label>
                                <textarea name="judul" class="form-control border-0 bg-light p-3" rows="2"
                                          style="border-radius: 12px;" placeholder="Masukkan judul lengkap..." required>{{ old('judul', $proposal->judul) }}</textarea>
                            </div>

                            {{-- ANGGOTA --}}
                            <div class="col-12">
                                <div class="p-3 rounded-4 border border-light-subtle bg-light-subtle">
                                    <label class="form-label fw-bold text-secondary d-flex justify-content-between align-items-center">
                                        Anggota Tim
                                        <button type="button" class="btn btn-sm btn-primary rounded-pill px-3" id="addAnggotaBtn">
                                            <i class="bi bi-plus-circle me-1"></i> Tambah
                                        </button>
                                    </label>

                                    @php
                                        $anggota = is_array($proposal->anggota) ? $proposal->anggota : (json_decode($proposal->anggota, true) ?? []);
                                    @endphp

                                    <div id="anggota-container">
                                        @foreach($anggota as $a)
                                            <div class="d-flex gap-2 mb-2 anggota-row animate__animated animate__fadeIn">
                                                <input type="text" name="anggota[]" class="form-control border-0 shadow-sm"
                                                       value="{{ $a }}" list="user-list" placeholder="Nama anggota..." style="border-radius: 10px;">
                                                <button type="button" class="btn btn-danger-soft remove-anggota"
                                                        style="border-radius: 10px; width: 45px;">
                                                    <i class="bi bi-trash3-fill"></i>
                                                </button>
                                            </div>
                                        @endforeach
                                    </div>
                                </div>
                            </div>

                            {{-- UPDATE FILE --}}
                            <div class="col-12">
                                <div class="p-4 border-2 border-dashed rounded-4 text-center" style="border-style: dashed !important; border-color: #dee2e6 !important;">
                                    <label class="form-label fw-bold text-secondary">Berkas Proposal (PDF)</label>
                                    <div class="d-flex justify-content-center mb-2">
                                        <i class="bi bi-file-earmark-pdf-fill text-danger fs-1"></i>
                                    </div>
                                    <input type="file" name="file" class="form-control shadow-sm mx-auto" style="max-width: 400px; border-radius: 10px;" accept=".pdf">
                                    <p class="mt-2 small text-muted mb-0">
                                        <span class="badge bg-info-subtle text-info">File saat ini:</span>
                                        {{ basename($proposal->file_path) ?: 'Belum ada file' }}
                                    </p>
                                    <small class="text-danger mt-1 d-block font-italic">*Kosongkan jika tidak ingin mengganti file.</small>
                                </div>
                            </div>
                        </div>

                        {{-- BUTTONS --}}
                        <div class="mt-5 d-flex gap-3 justify-content-end">
                            <a href="{{ route('monitoring.proposalDikirim') }}" class="btn btn-light px-4 py-2 fw-bold text-secondary" style="border-radius: 12px;">
                                Batal
                            </a>
                            <button type="submit" class="btn btn-success px-5 py-2 fw-bold shadow-sm" style="border-radius: 12px; background-color: #2e7d32;">
                                <i class="bi bi-send-check-fill me-2"></i>Simpan & Kirim
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

{{-- Data List --}}
<datalist id="user-list">
    @foreach($users as $user)
        <option value="{{ $user->name }}">
    @endforeach
</datalist>

<style>
    /* Custom Styling Nuansa Soft */
    body { background-color: #f8f9fa; }
    .form-control:focus {
        background-color: #fff !important;
        box-shadow: 0 0 0 0.25rem rgba(251, 192, 45, 0.2) !important;
        border: 1px solid #fbc02d !important;
    }
    .btn-danger-soft {
        background-color: #ffebee;
        color: #c62828;
        border: none;
    }
    .btn-danger-soft:hover {
        background-color: #ef5350;
        color: white;
    }
    .animate__animated { --animate-duration: 0.5s; }
</style>
<script>
document.addEventListener('DOMContentLoaded', function() {
    const biayaDisplay = document.getElementById('biaya_display');
    const biayaReal = document.getElementById('biaya_real');

    // 1. Format Rupiah saat Input
    biayaDisplay.addEventListener('input', function(e) {
        let value = this.value.replace(/[^0-9]/g, '');
        biayaReal.value = value; // Simpan angka murni ke hidden input
        this.value = new Intl.NumberFormat('id-ID').format(value);
    });

    // 2. Tambah Anggota Secara Dinamis
    const container = document.getElementById('anggota-container');
    const addBtn = document.getElementById('addAnggotaBtn');

    addBtn.addEventListener('click', function() {
        const div = document.createElement('div');
        div.className = 'd-flex gap-2 mb-2 anggota-row animate__animated animate__fadeIn';
        div.innerHTML = `
            <input type="text" name="anggota[]" class="form-control border-0 shadow-sm"
                   list="user-list" placeholder="Nama anggota..." style="border-radius: 10px;">
            <button type="button" class="btn btn-danger-soft remove-anggota" style="border-radius: 10px; width: 45px;">
                <i class="bi bi-trash3-fill"></i>
            </button>
        `;
        container.appendChild(div);
    });

    // 3. Hapus Anggota
    container.addEventListener('click', function(e) {
        if (e.target.closest('.remove-anggota')) {
            e.target.closest('.anggota-row').remove();
        }
    });
});
</script>
@endsection

@extends('layouts.app')

@section('title', 'Dokumen Penting')

@section('content')
<style>
    body { background-color: #f0fdf4; } /* Hijau YARSI */

    .main-card {
        background: white;
        border-radius: 20px;
        padding: 30px;
        box-shadow: 0 10px 25px rgba(0,0,0,0.05);
        border: none;
    }

    .admin-setting-card {
        background-color: #fff9db;
        border: 1px solid #ffe066;
        border-radius: 15px;
    }

    .table thead th {
        background-color: #28a745;
        color: white;
        border: none;
        font-size: 13px;
        font-weight: 600;
        padding: 15px;
        text-align: center;
    }

    .table tbody td {
        padding: 15px;
        vertical-align: middle;
        font-size: 13px;
    }

    /* Indikator Baris yang Disembunyikan */
    .row-hidden {
        background-color: #f8f9fa !important;
        opacity: 0.7;
    }

    .btn-download-doc {
        background-color: #e3f2fd;
        color: #1565c0;
        font-weight: 700;
        font-size: 11px;
        border-radius: 8px;
        padding: 8px 15px;
        display: inline-flex;
        align-items: center;
        gap: 8px;
        transition: 0.3s;
        border: none;
        text-decoration: none;
    }

    .btn-download-doc:hover {
        background-color: #bbdefb;
        color: #0d47a1;
        transform: translateY(-2px);
    }

    .status-badge {
        font-size: 10px;
        padding: 4px 8px;
        border-radius: 6px;
        font-weight: bold;
    }
</style>

<div class="container py-4">
    <div class="main-card">
        {{-- HEADER --}}
        <div class="d-flex justify-content-between align-items-center mb-4">
            <div>
                <h4 class="fw-extrabold text-dark mb-1">Dokumen & Pedoman</h4>
                <p class="text-muted small">Unduh berkas penting, panduan, dan regulasi penelitian/pengabdian.</p>
            </div>
            <div class="text-success small fw-bold">
                <i class="bi bi-file-earmark-check-fill fs-2"></i>
            </div>
        </div>

        {{-- ALERT --}}
        @if(session('success'))
            <div class="alert alert-success border-0 shadow-sm mb-4">
                <i class="bi bi-check-circle-fill me-2"></i> {{ session('success') }}
            </div>
        @endif

        {{-- KHUSUS ADMIN: FORM UPLOAD --}}
        @if(Auth::user()->role === 'admin')
        <div class="admin-setting-card p-4 mb-5 shadow-sm">
            <h6 class="fw-bold text-dark mb-3"><i class="bi bi-cloud-arrow-up-fill me-2"></i>Unggah Dokumen Baru</h6>
            <form action="{{ route('admin.dokumen.store') }}" method="POST" enctype="multipart/form-data">
                @csrf
                <div class="row align-items-end">
                    <div class="col-md-5 mb-2">
                        <label class="small fw-bold text-muted">Judul Dokumen</label>
                        <input type="text" name="judul" class="form-control" placeholder="Judul Dokumen" required style="border-radius: 8px;">
                    </div>
                    <div class="col-md-4 mb-2">
                        <label class="small fw-bold text-muted">Pilih File (PDF/DOCX)</label>
                        <input type="file" name="file" class="form-control" required style="border-radius: 8px;">
                    </div>
                    <div class="col-md-3 mb-2">
                        <button type="submit" class="btn btn-warning w-100 fw-bold shadow-sm" style="border-radius: 8px; height: 42px;">
                            <i class="bi bi-upload me-1"></i> Publikasikan
                        </button>
                    </div>
                </div>
            </form>
        </div>
        @endif

        {{-- TABEL DOKUMEN --}}
        <h5 class="fw-bold mb-3 text-dark">Daftar Dokumen Tersedia</h5>
        <div class="table-responsive">
            <table class="table table-hover align-middle">
                <thead>
                    <tr>
                        <th width="5%" style="border-top-left-radius: 15px;">No</th>
                        <th class="text-start">Nama Dokumen</th>
                        <th width="15%">Tanggal Unggah</th>
                        @if(Auth::user()->role === 'admin')
                            <th width="10%">Status</th>
                        @endif
                        <th width="20%" style="border-top-right-radius: 15px;">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                   @forelse($docs as $index => $doc)
    <tr class="{{ !$doc->is_visible ? 'row-hidden' : '' }}">
        <td class="text-center fw-bold text-muted">{{ $index + 1 }}</td>
        <td class="text-start">
            <div class="fw-bold text-dark">{{ $doc->judul }}</div>
            <small class="text-muted">ID: #DOC-{{ $doc->id }}</small>
        </td>
        <td class="text-center">{{ $doc->created_at->translatedFormat('d M Y') }}</td>

        {{-- PERBAIKAN DI SINI: Tutup tag IF untuk kolom status --}}
        @if(Auth::user()->role === 'admin')
            <td class="text-center">
                <span class="badge {{ $doc->is_visible ? 'bg-success' : 'bg-secondary' }}">
                    {{ $doc->is_visible ? 'Tampil' : 'Sembunyi' }}
                </span>
            </td>
        @endif

        <td class="text-center">
            <div class="d-flex justify-content-center gap-2">
                <a href="{{ route('dokumen.download', $doc->id) }}" class="btn-download-doc shadow-sm">
                    <i class="bi bi-download"></i> UNDUH
                </a>

                @if(Auth::user()->role === 'admin')
                    <form action="{{ route('admin.dokumen.toggle', $doc->id) }}" method="POST">
                        @csrf
                        @method('PATCH') {{-- Sesuai permintaan Anda sebelumnya menggunakan PATCH --}}
                        <button type="submit" class="btn btn-sm {{ $doc->is_visible ? 'btn-outline-danger' : 'btn-outline-success' }}"
                                style="border-radius: 8px;">
                            <i class="bi {{ $doc->is_visible ? 'bi-eye-slash' : 'bi-eye' }}"></i>
                        </button>
                    </form>
                @endif
            </div>
        </td>
    </tr>
@empty
    <tr>
        <td colspan="{{ Auth::user()->role === 'admin' ? '5' : '4' }}" class="text-center py-5 text-muted fst-italic">
            Belum ada dokumen yang tersedia.
        </td>
    </tr>
@endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection

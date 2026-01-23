@extends('layouts.app')

@section('title', Auth::user()->role === 'admin' ? 'Laporan Akhir' : 'Unggah Laporan Akhir')

@section('content')
<style>
    body { background-color: #f0fdf4; } /* Tema Hijau YARSI */

    .main-card {
        background: white;
        border-radius: 20px;
        padding: 30px;
        box-shadow: 0 10px 25px rgba(0,0,0,0.05);
        border: none;
    }

    .form-section {
        background: #fcfdfd;
        border-radius: 15px;
        border: 1px solid #eef2f3;
        padding: 25px;
    }

    .admin-setting-card {
        background-color: #fff9db;
        border: 1px solid #ffe066;
        border-radius: 15px;
    }

    .table thead th {
        background-color: #28a745; /* Samakan dengan Laporan Kemajuan */
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

    .btn-laporan {
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

    .btn-laporan:hover {
        background-color: #bbdefb;
        color: #0d47a1;
        transform: translateY(-2px);
    }

    .badge-empty {
        background-color: #f8f9fa;
        color: #adb5bd;
        border: 1px dashed #dee2e6;
        padding: 6px 12px;
        border-radius: 8px;
        font-size: 11px;
    }

    .action-btn {
        width: 38px;
        height: 38px;
        display: flex;
        align-items: center;
        justify-content: center;
        border-radius: 10px;
        transition: 0.3s;
    }
</style>

<div class="container py-4">
    <div class="main-card">

        {{-- HEADER --}}
        <div class="d-flex justify-content-between align-items-center mb-4">
            <div>
                <h4 class="fw-extrabold text-dark mb-1">
                    {{ Auth::user()->role === 'admin' ? 'Monitoring Laporan Akhir' : 'Unggah Laporan Akhir' }}
                </h4>
                <p class="text-muted small">Kelola berkas finalisasi penelitian dan pengabdian masyarakat.</p>
            </div>
            <div class="text-end">
                <div class="text-success small fw-bold">
                    <i class="bi bi-calendar-check me-1"></i> {{ now()->translatedFormat('d F Y') }}
                </div>
            </div>
        </div>

        {{-- ALERT --}}
        @if(session('success'))
            <div class="alert alert-success border-0 shadow-sm mb-4">
                <i class="bi bi-check-circle-fill me-2"></i> {{ session('success') }}
            </div>
        @endif

        {{-- ADMIN : TEMPLATE --}}
        @if(Auth::user()->role === 'admin')
        <div class="admin-setting-card p-4 mb-5 shadow-sm">
            <h6 class="fw-bold text-dark mb-3"><i class="bi bi-gear-fill me-2"></i>Pengaturan Template Laporan Akhir (Admin)</h6>
            <form action="{{ route('admin.template.upload') }}" method="POST" enctype="multipart/form-data">
                @csrf
                <input type="hidden" name="jenis" value="laporan_akhir">

                <div class="row align-items-end">
                    <div class="col-md-6 mb-2">
                        <label class="small fw-bold text-muted">File Template Akhir (.pdf / .docx)</label>
                        <input type="file" name="file_template" class="form-control form-control-sm" required style="border-radius: 8px;">
                    </div>
                    <div class="col-md-3 mb-2">
                        <button class="btn btn-warning btn-sm w-100 fw-bold shadow-sm" style="border-radius: 8px; height: 38px;">
                            <i class="bi bi-cloud-upload me-1"></i> Perbarui Template
                        </button>
                    </div>
                </div>
            </form>
        </div>
        @endif

        {{-- USER : FORM UPLOAD --}}
        @if(Auth::user()->role !== 'admin')
        <div class="form-section mb-5">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <h5 class="fw-bold text-dark mb-0">Submit Laporan Akhir</h5>

                @php
                    $template = \App\Models\Template::where('jenis', 'laporan_akhir')->first();
                @endphp

                @if($template && $template->file_path)
                    <a href="{{ asset('storage/' . $template->file_path) }}"
                       class="btn btn-sm btn-outline-success border-2 fw-bold px-3"
                       style="border-radius: 8px;"
                       download="{{ $template->nama_template }}">
                        <i class="bi bi-file-earmark-arrow-down-fill me-1"></i> Unduh Template Laporan Akhir
                    </a>
                @else
                    <span class="text-muted small fst-italic">
                        <i class="bi bi-info-circle me-1"></i> Template belum tersedia.
                    </span>
                @endif
            </div>

            <form action="{{ route('laporan.akhir.store') }}" method="POST" enctype="multipart/form-data" id="uploadForm">
                @csrf
                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label class="form-label fw-bold small text-muted">Pilih Judul Proposal</label>
                        <select name="proposal_id" class="form-select @error('proposal_id') is-invalid @enderror" required style="border-radius: 10px; padding: 10px;">
                            <option value="">-- Pilih Proposal --</option>
                            @foreach($myProposals as $p)
                                <option value="{{ $p->id }}">{{ $p->judul }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div class="col-md-6 mb-3">
                        <label class="form-label fw-bold small text-muted">File Laporan Akhir (PDF)</label>
                        <input type="file" name="file" class="form-control @error('file') is-invalid @enderror" accept=".pdf" required style="border-radius: 10px; padding: 10px;">
                    </div>

                    <div class="col-12 mb-4">
                        <label class="form-label fw-bold small text-muted">Keterangan Akhir (Opsional)</label>
                        <textarea name="keterangan" class="form-control" rows="3" placeholder="Contoh: Berkas final penelitian telah selesai..." style="border-radius: 10px;">{{ old('keterangan') }}</textarea>
                    </div>
                </div>

                <button type="submit" class="btn btn-success px-5 fw-bold shadow-sm" style="border-radius: 10px; height: 45px;" id="submitBtn">
                    <span class="spinner-border spinner-border-sm d-none" id="btnSpinner"></span>
                    <span id="btnText">
                        <i class="bi bi-cloud-arrow-up-fill me-2"></i>Unggah Laporan Akhir
                    </span>
                </button>
            </form>
        </div>
        @endif

        {{-- TABEL --}}
        <div class="d-flex justify-content-between align-items-center mb-3">
            <h5 class="fw-bold text-dark mb-0">Data Arsip Laporan Akhir</h5>
            <div class="search-wrapper">
                <form action="{{ route('laporan.akhir.index') }}" method="GET">
                    <input type="text" name="search" class="form-control form-control-sm shadow-sm" placeholder="Cari judul..." value="{{ request('search') }}" style="border-radius: 8px; width: 250px;">
                </form>
            </div>
        </div>

        <div class="table-responsive">
            <table class="table table-hover align-middle">
                <thead>
                    <tr>
                        <th width="5%" style="border-top-left-radius: 15px;">No</th>
                        <th class="text-start" width="30%">Judul & Pengusul</th>
                        <th width="15%">Reviewer</th>
                        <th width="20%">Berkas Akhir</th>
                        <th width="15%" style="border-top-right-radius: 15px;">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                @forelse($proposals as $i => $p)
                    <tr>
                        <td class="text-center fw-bold text-muted">{{ $i+1 }}</td>
                        <td>
                            <div class="fw-bold text-dark mb-1">{{ Str::limit($p->judul, 70) }}</div>
                            <div class="text-primary small">
                                <i class="bi bi-person-circle me-1"></i> {{ $p->user->name ?? '-' }}
                            </div>
                        </td>
                        <td class="text-center small">
                            @if($p->reviewers->isNotEmpty())
                                @foreach($p->reviewers as $rev)
                                    <span class="badge bg-light text-dark border">{{ $rev->name }}</span>
                                @endforeach
                            @else
                                <span class="text-muted small">-</span>
                            @endif
                        </td>
                        <td class="text-center">
                            @if($p->file_laporan_akhir)
                                <a href="{{ route('laporan.akhir.download', $p->id) }}" class="btn-laporan shadow-sm">
                                    <i class="bi bi-file-earmark-check-fill fs-6"></i>
                                    <span>UNDUH LAPORAN AKHIR</span>
                                </a>
                                <div class="mt-1 text-muted" style="font-size: 10px;">
                                    Diperbarui: {{ \Carbon\Carbon::parse($p->updated_at)->format('d/m/Y') }}
                                </div>
                            @else
                                <span class="badge-empty">
                                    <i class="bi bi-info-circle me-1"></i> Belum Ada Berkas
                                </span>
                            @endif
                        </td>
                        <td class="text-center">
                            <div class="d-flex justify-content-center gap-2">
                                <a href="{{ route('laporan.akhir.download', $p->id) }}"
                                   class="action-btn btn btn-light-success"
                                   style="background: #e8f5e9; color: #2e7d32;" title="Tinjau Detail">
                                    <i class="bi bi-eye-fill"></i>
                                </a>
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="5" class="text-center py-5 text-muted">
                            Belum ada data laporan akhir.
                        </td>
                    </tr>
                @endforelse
                </tbody>
            </table>
        </div>

    </div>
</div>

@push('scripts')
<script>
    const uploadForm = document.getElementById('uploadForm');
    const submitBtn = document.getElementById('submitBtn');
    const btnSpinner = document.getElementById('btnSpinner');
    const btnText = document.getElementById('btnText');

    if(uploadForm){
        uploadForm.onsubmit = () => {
            submitBtn.classList.add('disabled');
            btnSpinner.classList.remove('d-none');
            btnText.innerText = 'Sedang Mengunggah...';
        }
    }
</script>
@endpush
@endsection

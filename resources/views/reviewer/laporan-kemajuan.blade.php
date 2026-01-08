@extends('layouts.app')

@section('title', Auth::user()->role === 'admin' ? 'Laporan Kemajuan' : 'Unggah Laporan Kemajuan Final')

@section('content')
<div class="container-fluid py-4 px-md-5">

    <h4 class="mb-4 fw-bold text-secondary">
        {{ Auth::user()->role === 'admin' ? 'Laporan Kemajuan' : 'Unggah Laporan Kemajuan' }}
    </h4>

    {{-- ✅ ALERT SUCCESS --}}
    @if (session('success'))
        <div class="alert alert-success border-0 shadow-sm alert-dismissible fade show" role="alert" id="success-alert">
            <i class="bi bi-check-circle me-2"></i> {{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif

    {{-- ✅ ALERT ERROR (BIAR KELIHATAN KENAPA DOWNLOAD GAGAL) --}}
    @if (session('error'))
        <div class="alert alert-danger border-0 shadow-sm alert-dismissible fade show" role="alert">
            <i class="bi bi-exclamation-triangle me-2"></i> {{ session('error') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif

    {{-- ✅ VALIDATION ERROR --}}
    @if ($errors->any())
        <div class="alert alert-danger border-0 shadow-sm alert-dismissible fade show" role="alert">
            <i class="bi bi-exclamation-triangle me-2"></i> Terjadi kesalahan:
            <ul class="mb-0 mt-2">
                @foreach ($errors->all() as $err)
                    <li>{{ $err }}</li>
                @endforeach
            </ul>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif

    <div class="row">
        <div class="col-12">

            @if(Auth::user()->role !== 'admin')
            <div class="card border-0 shadow-sm mb-4" style="border-radius: 12px;">
                <div class="card-body p-4">
                    <h5 class="mb-3 fw-bold">Unggah Laporan Kemajuan</h5>

                    <div class="mb-4 d-flex justify-content-between align-items-center">
                        <div class="text-success small">
                            <i class="bi bi-clock me-1"></i> {{ now()->translatedFormat('l, d F Y, H.i') }} WIB
                        </div>
                        <a href="{{ asset('templates/template_laporan_kemajuan.docx') }}" class="text-success small text-decoration-none fw-bold hover-underline">
                            <i class="bi bi-file-earmark-word me-1"></i> Download Template Laporan Kemajuan.docx
                        </a>
                    </div>

                    <form action="{{ route('laporan.kemajuan.store') }}" method="POST" enctype="multipart/form-data" id="uploadForm">
                        @csrf

                        {{-- ✅ PILIH PROPOSAL (WAJIB karena store() minta proposal_id) --}}
                        <div class="mb-3">
                            <label class="form-label fw-bold">Pilih Proposal</label>
                            <select name="proposal_id" class="form-select @error('proposal_id') is-invalid @enderror" required>
                                <option value="">-- Pilih Proposal --</option>
                                @if(!empty($myProposals))
                                    @foreach($myProposals as $p)
                                        <option value="{{ $p->id }}" {{ old('proposal_id') == $p->id ? 'selected' : '' }}>
                                            {{ $p->judul }}
                                        </option>
                                    @endforeach
                                @endif
                            </select>
                            @error('proposal_id') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>

                        <div class="mb-3">
                            <label class="form-label fw-bold">File Proposal</label>
                            <input type="file" name="file" id="fileInput" class="form-control @error('file') is-invalid @enderror" accept=".pdf,.doc,.docx" required>
                            <small class="text-muted mt-1 d-block">Format: PDF, DOC, DOCX (Max 2MB)</small>
                            @error('file') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>

                        <div class="mb-4">
                            <label class="form-label fw-bold">Saran / Keterangan Tambahan</label>
                            <textarea name="keterangan" class="form-control" rows="4" placeholder="Masukkan keterangan tambahan jika ada...">{{ old('keterangan') }}</textarea>
                        </div>

                        <div class="mt-2">
                            <button type="submit" class="btn btn-success px-5 py-2 fw-bold" style="background-color: #28a745; border: none; border-radius: 8px;" id="submitBtn">
                                <span class="spinner-border spinner-border-sm d-none" role="status" aria-hidden="true" id="btnSpinner"></span>
                                <span id="btnText">Kirim</span>
                            </button>
                            <a href="{{ route('dashboard') }}" class="btn btn-light px-4 py-2 border ms-2" style="border-radius: 8px;">Batal</a>
                        </div>
                    </form>
                </div>
            </div>
            @endif

            <div class="card border-0 shadow-sm" style="border-radius: 12px;">
                <div class="card-body p-4">
                    <div class="d-flex justify-content-between align-items-center mb-3">
                        <h5 class="fw-bold mb-0">Riwayat Laporan</h5>
                        <form action="{{ route('laporan.kemajuan.index') }}" method="GET">
                            <div class="input-group input-group-sm" style="width: 300px;">
                                <span class="input-group-text bg-white border-end-0"><i class="bi bi-search"></i></span>
                                <input type="text" name="search" class="form-control border-start-0" placeholder="Cari Judul..." value="{{ request('search') }}">
                            </div>
                        </form>
                    </div>

                    <div class="table-responsive">
                        <table class="table table-hover align-middle">
                            <thead class="text-white" style="background-color: #28a745;">
                                <tr>
                                    <th class="py-3 ps-3" style="border-top-left-radius: 12px; border: none;">No</th>
                                    <th class="py-3" style="border: none;">Reviewer</th>
                                    <th class="py-3" style="border: none;">Pengusul</th>
                                    <th class="py-3" style="border: none;">Judul Proposal</th>
                                    <th class="py-3 text-center" style="border-top-right-radius: 12px; border: none;">Aksi</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse ($proposals as $proposal)
                                    <tr class="border-bottom">
                                        <td class="ps-3">{{ ($proposals->currentPage() - 1) * $proposals->perPage() + $loop->iteration }}</td>
                                        <td class="small">{{ optional($proposal->reviewer)->name ?? '-' }}</td>
                                        <td class="small">{{ optional($proposal->user)->name ?? '-' }}</td>
                                        <td class="small fw-medium">{{ $proposal->judul }}</td>

                                        <td class="text-center">
                                            <div class="d-flex justify-content-center gap-1">
                                                <a href="{{ route('proposal.tinjau', $proposal->id) }}" class="btn btn-outline-secondary btn-sm" style="font-size: 0.75rem;">Review</a>

                                                {{-- ✅ DOWNLOAD KHUSUS ADMIN --}}
                                                @if(strtolower(Auth::user()->role ?? '') === 'admin')
                                                    @if($proposal->file_laporan)
                                                        <a href="{{ route('laporan.kemajuan.download', $proposal->id) }}"
                                                           class="btn btn-success btn-sm"
                                                           style="font-size: 0.75rem; background-color: #28a745;">
                                                            Download
                                                        </a>
                                                    @else
                                                        <button type="button"
                                                                class="btn btn-success btn-sm disabled"
                                                                style="font-size: 0.75rem; background-color: #28a745; opacity:.55;"
                                                                title="Belum ada file laporan">
                                                            Download
                                                        </button>
                                                    @endif
                                                @endif
                                            </div>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="5" class="text-center py-4 text-muted small">Data tidak ditemukan</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

        </div>
    </div>

    @if(Auth::user()->role !== 'admin')
    <div class="d-flex justify-content-between mt-4 mb-5">
        <a href="{{ url('/status/review') }}" class="btn btn-outline-success btn-sm px-3 bg-white fw-bold shadow-sm border-success text-success">
            <i class="bi bi-chevron-left"></i> Sedang Direview
        </a>
        <a href="{{ url('/status/selesai') }}" class="btn btn-outline-success btn-sm px-3 bg-white fw-bold shadow-sm border-success text-success">
            Proposal Selesai <i class="bi bi-chevron-right"></i>
        </a>
    </div>
    @endif

</div>

<style>
    .container-fluid { max-width: 1400px; margin: 0 auto; }
    .card { border-radius: 12px !important; }
    .table thead th { font-weight: 500; border: none; }
    .btn-outline-success:hover { background-color: #28a745 !important; color: white !important; }
    .hover-underline:hover { text-decoration: underline !important; }
</style>

@push('scripts')
@if(Auth::user()->role !== 'admin')
<script>
    document.getElementById('uploadForm').onsubmit = function() {
        document.getElementById('submitBtn').classList.add('disabled');
        document.getElementById('btnSpinner').classList.remove('d-none');
        document.getElementById('btnText').innerText = 'Mengirim...';
    };
</script>
@endif
@endpush
@endsection

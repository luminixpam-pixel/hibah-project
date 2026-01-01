@extends('layouts.app')

@section('content')

<div class="container mt-4">

    {{-- HEADER & FILTER TAHUN (Hanya Admin) --}}
    @if($role === 'admin')
        <div class="d-flex justify-content-between align-items-center mb-4 bg-white p-3 rounded shadow-sm border">
            <div>
                <h4 class="fw-bold mb-0">Dashboard Laporan</h4>
                <p class="text-muted mb-0 small">Monitoring hibah tahunan dan rekapitulasi data fakultas.</p>
            </div>
            <form action="{{ route('dashboard') }}" method="GET" class="d-flex align-items-center gap-2">
                <label class="small fw-bold text-muted">TAHUN:</label>
                <select name="tahun" class="form-select form-select-sm border shadow-sm" onchange="this.form.submit()" style="width: 100px;">
                    @for ($y = date('Y'); $y >= 2023; $y--)
                        <option value="{{ $y }}" {{ $tahun == $y ? 'selected' : '' }}>{{ $y }}</option>
                    @endfor
                </select>
            </form>
        </div>

        {{-- RINGKASAN ANGKA KUNCI ADMIN --}}
        <div class="row g-3 mb-4">
            <div class="col-md-4">
                <div class="card border-0 shadow-sm p-3 bg-primary text-white">
                    <div class="small fw-bold opacity-75">TOTAL DANA DISALURKAN ({{ $tahun }})</div>
                    <h3 class="fw-bold mb-0">Rp {{ number_format($ringkasanLaporan['total_dana'] ?? 0, 0, ',', '.') }}</h3>
                </div>
            </div>
            <div class="col-md-4">
                <div class="card border-0 shadow-sm p-3 bg-success text-white">
                    <div class="small fw-bold opacity-75">TOTAL PENERIMA HIBAH</div>
                    <h3 class="fw-bold mb-0">{{ $ringkasanLaporan['total_penerima'] ?? 0 }} Proposal</h3>
                </div>
            </div>
            <div class="col-md-4">
                <div class="card border-0 shadow-sm p-3 bg-white border">
                    <div class="small fw-bold text-muted">TOTAL PENGAJUAN MASUK</div>
                    <h3 class="fw-bold mb-0 text-dark">{{ $ringkasanLaporan['total_pengajuan'] ?? 0 }}</h3>
                </div>
            </div>
        </div>
    @endif

    {{-- ===================== DASHBOARD PROGRESS (Khusus Pengaju) ==================== --}}
    @php
        $direvisiDisplay = $direvisiFileCount ?? ($direvisiCount ?? 0);
        $currentStep = 0;

        if (($disetujuiCount ?? 0) > 0 || ($direvisiDisplay ?? 0) > 0 || ($hasilRevisiCount ?? 0) > 0) {
            $currentStep = 4;
        } elseif (($reviewSelesaiCount ?? 0) > 0) {
            $currentStep = 3;
        } elseif (($perluDireviewCount ?? 0) > 0 || ($sedangDireviewCount ?? 0) > 0) {
            $currentStep = 2;
        } elseif (($daftarProposalCount ?? 0) > 0) {
            $currentStep = 1;
        }

        $progressLabel = 'Belum ada proposal.';
        if ($currentStep === 1) $progressLabel = 'Proposal sudah dikirim.';
        if ($currentStep === 2) $progressLabel = 'Proposal sedang diproses review.';
        if ($currentStep === 3) $progressLabel = 'Review lengkap, menunggu keputusan.';
        if ($currentStep === 4) {
            if (($disetujuiCount ?? 0) > 0) $progressLabel = 'Ada proposal yang disetujui.';
            elseif (($hasilRevisiCount ?? 0) > 0) $progressLabel = 'Ada revisi yang sudah dikirim (Hasil Revisi).';
            else $progressLabel = 'Ada proposal yang perlu revisi (Proposal Direvisi).';
        }
    @endphp

    @if($role === 'pengaju')
        <div class="card p-4 mb-4 shadow-sm border">
            <div class="d-flex justify-content-between align-items-start flex-wrap gap-2">
                <div>
                    <h6 class="mb-1 fw-semibold">Progress Proposal Terbaru</h6>
                    <div class="text-muted small">{{ $progressLabel }}</div>
                </div>
                <div class="badge rounded-pill bg-light text-dark border">Step {{ $currentStep }}/4</div>
            </div>

            <div class="stepper mt-3">
                @php
                    $steps = [1 => 'Dikirim', 2 => 'Direview', 3 => 'Keputusan', 4 => 'Selesai'];
                @endphp

                @foreach($steps as $stepNum => $stepText)
                    @php
                        $isDone = ($stepNum === 4) ? ($currentStep >= 4) : ($currentStep > $stepNum);
                        $isActive = $currentStep === $stepNum;
                        $dotClass = $isDone ? 'done' : ($isActive ? 'active' : 'todo');
                    @endphp

                    <div class="step">
                        <div class="dot {{ $dotClass }}">
                            @if($isDone) <i class="bi bi-check-lg"></i> @else {{ $stepNum }} @endif
                        </div>
                        <div class="label">{{ $stepText }}</div>
                        <div class="sub">
                            @if($stepNum === 1) Upload & submit
                            @elseif($stepNum === 2) Proses review
                            @elseif($stepNum === 3) Menunggu keputusan
                            @else Disetujui / Ditolak
                            @endif
                        </div>
                    </div>

                    @if($stepNum < 4)
                        <div class="line {{ $currentStep > $stepNum ? 'filled' : '' }}"></div>
                    @endif
                @endforeach
            </div>
        </div>
    @endif

    {{-- ===================== DASHBOARD COUNTER CARDS ==================== --}}
    @php
        $dashboardItems = [
            ['title'=>'Daftar Proposal','count'=>$daftarProposalCount ?? 0,'route'=>'monitoring.proposalDikirim'],
            ['title'=>'Review Masuk','count'=>$perluDireviewCount ?? 0,'route'=>'monitoring.proposalPerluDireview'],
            ['title'=>'Proses Review','count'=>$sedangDireviewCount ?? 0,'route'=>'monitoring.proposalSedangDireview'],
            ['title'=>'Review Selesai','count'=>$reviewSelesaiCount ?? 0,'route'=>'monitoring.reviewSelesai'],
            ['title'=>'Disetujui','count'=>$disetujuiCount ?? 0,'route'=>'monitoring.proposalDisetujui'],
            ['title'=>'Ditolak','count'=>$ditolakCount ?? 0,'route'=>'monitoring.proposalDitolak'],
            ['title'=>'Perlu Revisi','count'=>$direvisiDisplay,'route'=>'monitoring.proposalDirevisi'],
            ['title'=>'Hasil Revisi','count'=>$hasilRevisiCount ?? 0,'route'=>'monitoring.hasilRevisi'],
        ];

        if($role==='pengaju'){
            $dashboardItems = array_filter($dashboardItems, function($item){
                return !in_array($item['title'], ['Review Masuk', 'Proses Review']);
            });
        }
        $currentRoute = Route::currentRouteName();
    @endphp

    <div class="row g-3 mb-4">
        @foreach($dashboardItems as $item)
            <div class="col-6 col-md-3">
                <a href="{{ route($item['route']) }}" class="text-decoration-none dashboard-link {{ $currentRoute === $item['route'] ? 'dashboard-link-active' : '' }}">
                    <div class="card text-center p-3 border shadow-sm h-100 bg-white">
                        <h6 class="mb-2 text-muted small fw-bold">{{ strtoupper($item['title']) }}</h6>
                        <h4 class="text-dark fw-bold mb-0">{{ $item['count'] }}</h4>
                    </div>
                </a>
            </div>
        @endforeach
    </div>

    {{-- ===================== REKAP FAKULTAS (Hanya Admin) ==================== --}}
    @if($role === 'admin' && isset($rekapFakultas))
    <div class="card border-0 shadow-sm mb-4">
        <div class="card-header bg-white py-3 d-flex justify-content-between align-items-center">
            <h6 class="fw-bold mb-0"><i class="bi bi-bar-chart-fill me-2 text-primary"></i>Distribusi Hibah per Fakultas</h6>
            <span class="badge bg-light text-dark border">Data Tahun {{ $tahun }}</span>
        </div>
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="bg-light">
                    <tr class="small text-muted">
                        <th class="ps-4">FAKULTAS</th>
                        <th class="text-center">PENGAJUAN</th>
                        <th class="text-center">DISETUJUI</th>
                        <th class="text-end pe-4">TOTAL DANA</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($rekapFakultas as $rekap)
                    <div class="mb-3">
    <label class="form-label fw-bold small text-muted">FAKULTAS</label>
    <select name="fakultas" class="form-select @error('fakultas') is-invalid @enderror">
        <option value="">-- Pilih Fakultas --</option>
        @php
            $daftarFakultas = [
                'Fakultas Ekonomi dan Bisnis',
                'Fakultas Hukum',
                'Fakultas Teknik',
                'Fakultas Kedokteran',
                'Fakultas Ilmu Komputer',
                'Fakultas Keguruan dan Ilmu Pendidikan',
                'Fakultas Pertanian',
                'Fakultas Ilmu Sosial dan Politik'
            ];
        @endphp
        @foreach($daftarFakultas as $fak)
            <option value="{{ $fak }}" {{ (old('fakultas') ?? $user->fakultas) == $fak ? 'selected' : '' }}>
                {{ $fak }}
            </option>
        @endforeach
    </select>
    @error('fakultas')
        <div class="invalid-feedback">{{ $message }}</div>
    @enderror
</div>
                    @empty
                    <tr><td colspan="4" class="text-center py-4 text-muted">Belum ada data fakultas terekam.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
    @endif

    {{-- ===================== PROFIL & DOKUMEN ==================== --}}
    <div class="row g-4">
        <div class="col-md-7">
            <div class="card p-4 border-0 shadow-sm h-100">
                <h5 class="fw-bold mb-4 border-bottom pb-2 text-dark">Profil Pengguna</h5>
                <div class="row mb-2">
                    <div class="col-5 text-muted small fw-bold text-uppercase">Role Anda:</div>
                    <div class="col-7 fw-bold text-primary">{{ $user->role_label ?? strtoupper($role) }}</div>
                </div>
                <div class="row mb-2">
                    <div class="col-5 text-muted small fw-bold text-uppercase">Nama Lengkap:</div>
                    <div class="col-7 text-dark">{{ $user->name ?? '-' }}</div>
                </div>
                <div class="row mb-2">
                    <div class="col-5 text-muted small fw-bold text-uppercase">Fakultas:</div>
                    <div class="col-7 text-dark">{{ $user->fakultas ?? '-' }}</div>
                </div>
                <div class="row mb-2">
                    <div class="col-5 text-muted small fw-bold text-uppercase">Email:</div>
                    <div class="col-7 text-dark">{{ $user->email ?? '-' }}</div>
                </div>
            </div>
        </div>
        <div class="col-md-5">
            <div class="card p-4 border-0 shadow-sm h-100">
                <h5 class="fw-bold mb-4 border-bottom pb-2 text-dark">Panduan & Dokumen</h5>
                @forelse($dokumenResmi as $dok)
                    <div class="d-flex align-items-center mb-3">
                        <i class="bi bi-file-earmark-pdf text-danger fs-3 me-3"></i>
                        <div>
                            <div class="fw-semibold small text-dark">{{ $dok->nama_dokumen }}</div>
                            <a href="{{ asset('storage/'.$dok->file_path) }}" target="_blank" class="text-decoration-none small">Download PDF</a>
                        </div>
                    </div>
                @empty
                    <p class="text-muted small">Belum ada dokumen resmi tersedia.</p>
                @endforelse
            </div>
        </div>
    </div>

</div>

@endsection

@push('styles')
<style>
.dashboard-link .card { transition: all 0.2s ease; border-radius: 12px; }
.dashboard-link:hover .card { transform:translateY(-3px); border-color: #2563eb !important; box-shadow: 0 10px 15px -3px rgba(0, 0, 0, 0.1) !important; }
.dashboard-link-active .card { border:2px solid #2563eb !important; background:#f8faff !important; }

/* ===== Stepper Styling ===== */
.stepper{display:flex;align-items:flex-start;justify-content:space-between;gap:10px;padding:10px 0;}
.step{min-width:100px;display:flex;flex-direction:column;align-items:center;text-align:center;}
.dot{width:40px;height:40px;border-radius:50%;display:flex;align-items:center;justify-content:center;font-weight:bold;border:2px solid #e2e8f0;background:#fff;color:#64748b;transition: 0.3s;}
.dot.done{background:#2563eb;border-color:#2563eb;color:#fff;}
.dot.active{border-color:#2563eb;color:#2563eb;box-shadow:0 0 0 4px rgba(37,99,235,.15);}
.label{margin-top:10px;font-weight:700;font-size:13px;color:#1e293b;}
.sub{font-size:11px;color:#94a3b8;}
.line{flex:1;height:4px;border-radius:10px;background:#e2e8f0;margin-top:20px;}
.line.filled{background:#2563eb;}

/* Badge styling */
.bg-success-subtle { background-color: #dcfce7; }
.text-success { color: #15803d; }
</style>
@endpush

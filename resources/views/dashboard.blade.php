@extends('layouts.app')

@section('content')

<div class="container mt-4">

    {{-- HEADER & FILTER TAHUN (Hanya Admin) --}}
    @if($role === 'admin')
        <div class="d-flex justify-content-between align-items-center mb-4 bg-white p-3 rounded shadow-sm border">
            <div>
                <h4 class="fw-bold mb-0 text-primary">Dashboard Laporan</h4>
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
                <div class="card border-0 shadow-sm p-3 bg-primary text-white h-100">
                    <div class="small fw-bold opacity-75 text-uppercase">Total Dana Disalurkan ({{ $tahun }})</div>
                    <h3 class="fw-bold mb-0">Rp {{ number_format($ringkasanLaporan['total_dana'] ?? 0, 0, ',', '.') }}</h3>
                </div>
            </div>
            <div class="col-md-4">
                <div class="card border-0 shadow-sm p-3 bg-success text-white h-100">
                    <div class="small fw-bold opacity-75 text-uppercase">Total Penerima Hibah</div>
                    <h3 class="fw-bold mb-0">{{ $ringkasanLaporan['total_penerima'] ?? 0 }} Proposal</h3>
                </div>
            </div>
            <div class="col-md-4">
                <div class="card border-0 shadow-sm p-3 bg-white border h-100">
                    <div class="small fw-bold text-muted text-uppercase">Total Pengajuan Masuk</div>
                    <h3 class="fw-bold mb-0 text-dark">{{ $ringkasanLaporan['total_pengajuan'] ?? 0 }}</h3>
                </div>
            </div>
        </div>
    @endif

    {{-- ===================== DASHBOARD PROGRESS (Khusus Pengaju) ==================== --}}
    @if($role === 'pengaju')
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
                elseif (($hasilRevisiCount ?? 0) > 0) $progressLabel = 'Ada revisi yang sudah dikirim.';
                else $progressLabel = 'Ada proposal yang perlu revisi.';
            }
        @endphp

        <div class="card p-4 mb-4 shadow-sm border-0">
            <div class="d-flex justify-content-between align-items-start flex-wrap gap-2">
                <div>
                    <h6 class="mb-1 fw-bold text-dark">Status Progress Pengajuan</h6>
                    <div class="text-muted small">{{ $progressLabel }}</div>
                </div>
                <div class="badge rounded-pill bg-primary px-3">Step {{ $currentStep }}/4</div>
            </div>

            <div class="stepper mt-4">
                @php $steps = [1 => 'Dikirim', 2 => 'Direview', 3 => 'Keputusan', 4 => 'Selesai']; @endphp
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
                    </div>
                    @if($stepNum < 4)
                        <div class="line {{ $currentStep > $stepNum ? 'filled' : '' }}"></div>
                    @endif
                @endforeach
            </div>
        </div>
    @endif

    {{-- ===================== DASHBOARD PROGRESS (Khusus Reviewer) ==================== --}}
    @if($role === 'reviewer')
        @php
            $direvisiDisplay = $direvisiFileCount ?? ($direvisiCount ?? 0);
            $currentStep = 0;

            // Ini dihitung dari proposal yang ditugaskan ke reviewer (sudah di-scope di controller)
            if (($disetujuiCount ?? 0) > 0 || ($direvisiDisplay ?? 0) > 0 || ($hasilRevisiCount ?? 0) > 0 || ($ditolakCount ?? 0) > 0) {
                $currentStep = 4;
            } elseif (($reviewSelesaiCount ?? 0) > 0) {
                $currentStep = 3;
            } elseif (($perluDireviewCount ?? 0) > 0 || ($sedangDireviewCount ?? 0) > 0) {
                $currentStep = 2;
            } elseif (($daftarProposalCount ?? 0) > 0) {
                $currentStep = 1;
            }

            $progressLabel = 'Belum ada proposal yang ditugaskan ke Anda.';
            if ($currentStep === 1) $progressLabel = 'Ada proposal masuk (dikirim).';
            if ($currentStep === 2) $progressLabel = 'Ada proposal yang sedang/menunggu review Anda.';
            if ($currentStep === 3) $progressLabel = 'Ada review selesai, menunggu keputusan.';
            if ($currentStep === 4) $progressLabel = 'Ada proposal yang sudah masuk tahap akhir (disetujui/ditolak/revisi).';
        @endphp

        <div class="card p-4 mb-4 shadow-sm border-0">
            <div class="d-flex justify-content-between align-items-start flex-wrap gap-2">
                <div>
                    <h6 class="mb-1 fw-bold text-dark">Status Progress Pengajuan (Reviewer)</h6>
                    <div class="text-muted small">{{ $progressLabel }}</div>
                </div>
                <div class="badge rounded-pill bg-primary px-3">Step {{ $currentStep }}/4</div>
            </div>

            <div class="stepper mt-4">
                @php $steps = [1 => 'Masuk', 2 => 'Direview', 3 => 'Menunggu Keputusan', 4 => 'Selesai']; @endphp
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
                    </div>
                    @if($stepNum < 4)
                        <div class="line {{ $currentStep > $stepNum ? 'filled' : '' }}"></div>
                    @endif
                @endforeach
            </div>
        </div>
    @endif

   {{-- ===================== COUNTER CARDS ==================== --}}
@php
    $dashboardItems = [
        ['title'=>'Daftar Proposal','count'=>$daftarProposalCount ?? 0,'route'=>'monitoring.proposalDikirim','icon'=>'bi-file-earmark-text'],
        ['title'=>'Review Masuk','count'=>$perluDireviewCount ?? 0,'route'=>'monitoring.proposalPerluDireview','icon'=>'bi-envelope-paper'],
        ['title'=>'Proses Review','count'=>$sedangDireviewCount ?? 0,'route'=>'monitoring.proposalSedangDireview','icon'=>'bi-arrow-repeat'],
        ['title'=>'Review Selesai','count'=>$reviewSelesaiCount ?? 0,'route'=>'monitoring.reviewSelesai','icon'=>'bi-clipboard-check'],
        ['title'=>'Disetujui','count'=>$disetujuiCount ?? 0,'route'=>'monitoring.proposalDisetujui','icon'=>'bi-check-circle'],
        ['title'=>'Ditolak','count'=>$ditolakCount ?? 0,'route'=>'monitoring.proposalDitolak','icon'=>'bi-x-circle'],
        ['title'=>'Perlu Revisi','count'=>$direvisiDisplay ?? 0,'route'=>'monitoring.proposalDirevisi','icon'=>'bi-pencil-square'],
        ['title'=>'Hasil Revisi','count'=>$hasilRevisiCount ?? 0,'route'=>'monitoring.hasilRevisi','icon'=>'bi-send-check'],
    ];

    if($role === 'pengaju'){
        $dashboardItems = array_filter($dashboardItems, fn($item) => !in_array($item['title'], ['Review Masuk', 'Proses Review']));
    }
@endphp

<div class="row g-3 mb-4 justify-content-center">
    @foreach($dashboardItems as $item)
        <div class="col-6 col-md-3">
            <a href="{{ route($item['route']) }}" class="text-decoration-none dashboard-link">
                <div class="card border-0 shadow-sm p-3 h-100 bg-white">
                    <div class="d-flex justify-content-between align-items-center mb-2">
                        <i class="bi {{ $item['icon'] }} text-primary fs-4"></i>
                        <h4 class="fw-bold mb-0 text-dark">{{ $item['count'] }}</h4>
                    </div>
                    <h6 class="mb-0 text-muted small fw-bold text-uppercase text-center text-md-start">
                        {{ $item['title'] }}
                    </h6>
                </div>
            </a>
        </div>
    @endforeach
</div>

    {{-- ===================== REKAP FAKULTAS (Hanya Admin) ==================== --}}
    @if($role === 'admin' && isset($rekapFakultas))
    <div class="card border-0 shadow-sm mb-4">
        <div class="card-header bg-white py-3">
            <h6 class="fw-bold mb-0 text-dark"><i class="bi bi-bar-chart-fill me-2 text-primary"></i>Distribusi Hibah per Fakultas</h6>
        </div>
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="bg-light">
                    <tr class="small text-muted text-uppercase">
                        <th class="ps-4">Fakultas</th>
                        <th class="text-center">Pengajuan</th>
                        <th class="text-center">Disetujui</th>
                        <th class="text-end pe-4">Total Dana</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($rekapFakultas as $rekap)
                    <tr>
                        <td class="ps-4">
                            <span class="fw-semibold text-dark">{{ $rekap->fakultas }}</span>
                        </td>
                        <td class="text-center">
                            <span class="badge bg-light text-primary border">{{ $rekap->total_pengajuan }}</span>
                        </td>
                        <td class="text-center">
                            <span class="badge bg-success-subtle text-success">{{ $rekap->total_disetujui }}</span>
                        </td>
                        <td class="text-end pe-4 fw-bold">
                            Rp {{ number_format($rekap->total_biaya ?? 0, 0, ',', '.') }}
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="4" class="text-center py-4 text-muted">Belum ada data fakultas terekam untuk tahun {{ $tahun }}.</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
    @endif

    {{-- ===================== PROFIL & DOKUMEN ==================== --}}
    <div class="row g-4">
        <div class="col-md-6">
            <div class="card p-4 border-0 shadow-sm h-100">
                <h6 class="fw-bold mb-3 border-bottom pb-2"><i class="bi bi-person-badge me-2 text-primary"></i>Profil Pengguna</h6>
                <div class="d-flex flex-column gap-2">
                    <div class="d-flex justify-content-between">
                        <span class="text-muted small fw-bold text-uppercase">Role:</span>
                        <span class="badge bg-primary-subtle text-primary">{{ strtoupper($role) }}</span>
                    </div>
                    <div class="d-flex justify-content-between border-bottom pb-1">
                        <span class="text-muted small fw-bold text-uppercase">Nama:</span>
                        <span class="text-dark fw-medium">{{ $user->name ?? '-' }}</span>
                    </div>
                    <div class="d-flex justify-content-between border-bottom pb-1">
                        <span class="text-muted small fw-bold text-uppercase">Fakultas:</span>
                        <span class="text-dark fw-medium">{{ $user->fakultas ?? '-' }}</span>
                    </div>
                    <div class="d-flex justify-content-between">
                        <span class="text-muted small fw-bold text-uppercase">Email:</span>
                        <span class="text-dark fw-medium">{{ $user->email ?? '-' }}</span>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-6">
            <div class="card p-4 border-0 shadow-sm h-100 bg-light">
                <h6 class="fw-bold mb-3"><i class="bi bi-info-circle me-2 text-primary"></i>Informasi Layanan</h6>
                <p class="small text-muted">Silakan gunakan menu <strong>Unggah</strong> untuk mengirimkan proposal baru atau laporan kemajuan sesuai dengan jadwal yang telah ditentukan.</p>
                <a href="{{ route('dokumen.user') }}" class="btn btn-outline-primary btn-sm mt-auto">Lihat Panduan Dokumen</a>
            </div>
        </div>
    </div>

</div>

@endsection

@push('styles')
<style>
    /* Card Hover Effect */
    .dashboard-link .card { transition: all 0.25s ease; border-radius: 15px; }
    .dashboard-link:hover .card { transform: translateY(-5px); box-shadow: 0 10px 20px rgba(0,0,0,0.08) !important; border-left: 4px solid #0d6efd !important; }

    /* Stepper Styling */
    .stepper { display: flex; align-items: center; justify-content: space-between; }
    .step { display: flex; flex-direction: column; align-items: center; z-index: 2; position: relative; }
    .dot { width: 35px; height: 35px; border-radius: 50%; display: flex; align-items: center; justify-content: center; font-weight: bold; font-size: 14px; background: #fff; border: 2px solid #dee2e6; color: #adb5bd; transition: 0.3s; }
    .dot.done { background: #198754; border-color: #198754; color: #fff; }
    .dot.active { border-color: #0d6efd; color: #0d6efd; background: #e7f1ff; box-shadow: 0 0 0 4px rgba(13,110,253,0.15); }
    .label { margin-top: 8px; font-weight: 600; font-size: 12px; color: #495057; }
    .line { flex: 1; height: 3px; background: #dee2e6; margin-top: -20px; z-index: 1; border-radius: 10px; }
    .line.filled { background: #198754; }

    /* Custom Table & Badge */
    .bg-success-subtle { background-color: #d1e7dd; }
    .text-success { color: #0f5132; }
    .table thead th { font-size: 11px; letter-spacing: 0.5px; }
</style>
@endpush

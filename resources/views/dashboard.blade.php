@extends('layouts.app')

@section('content')

<div class="container mt-4 mb-5">

    {{-- ===================== HEADER & FILTER (Hanya Admin) ==================== --}}
    @if($role === 'admin')
        <div class="d-flex justify-content-between align-items-center mb-4 bg-white p-3 rounded shadow-sm border">
            <div>
                <h4 class="fw-bold mb-1 text-primary">Dashboard Laporan</h4>
                <p class="text-muted mb-0 small">Monitoring hibah tahunan dan rekapitulasi data fakultas.</p>
            </div>
            <form action="{{ route('dashboard') }}" method="GET" class="d-flex align-items-center gap-2">
                <label class="small fw-bold text-muted text-uppercase" style="font-size: 10px;">Periode Tahun:</label>
                <select name="tahun" class="form-select form-select-sm border shadow-sm" onchange="this.form.submit()" style="width: 110px; font-weight: 600;">
                    @for ($y = date('Y'); $y >= 2023; $y--)
                        <option value="{{ $y }}" {{ $tahun == $y ? 'selected' : '' }}>{{ $y }}</option>
                    @endfor
                </select>
            </form>
        </div>

        {{-- RINGKASAN ANGKA KUNCI ADMIN --}}
        <div class="row g-3 mb-4">
            <div class="col-md-4">
                <div class="card border-0 shadow-sm p-4 bg-primary text-white h-100 position-relative overflow-hidden">
                    <div class="position-relative" style="z-index: 2;">
                        <div class="small fw-bold opacity-75 text-uppercase mb-1">Total Dana Disetujui ({{ $tahun }})</div>
                        <h3 class="fw-bold mb-0">Rp {{ number_format($ringkasanLaporan['total_dana'] ?? 0, 0, ',', '.') }}</h3>
                    </div>
                    <i class="bi bi-cash-stack position-absolute end-0 bottom-0 opacity-25 m-3" style="font-size: 3rem;"></i>
                </div>
            </div>
            <div class="col-md-4">
                <div class="card border-0 shadow-sm p-4 bg-success text-white h-100 position-relative overflow-hidden">
                    <div class="position-relative" style="z-index: 2;">
                        <div class="small fw-bold opacity-75 text-uppercase mb-1">Total Penerima Hibah</div>
                        <h3 class="fw-bold mb-0">{{ number_format($ringkasanLaporan['total_penerima'] ?? 0, 0, ',', '.') }} <span class="fs-6 fw-normal">Proposal</span></h3>
                    </div>
                    <i class="bi bi-people position-absolute end-0 bottom-0 opacity-25 m-3" style="font-size: 3rem;"></i>
                </div>
            </div>
            <div class="col-md-4">
                <div class="card border-0 shadow-sm p-4 bg-white border h-100 position-relative overflow-hidden">
                    <div class="position-relative" style="z-index: 2;">
                        <div class="small fw-bold text-muted text-uppercase mb-1">Total Pengajuan Masuk</div>
                        <h3 class="fw-bold mb-0 text-dark">{{ number_format($ringkasanLaporan['total_pengajuan'] ?? 0, 0, ',', '.') }}</h3>
                    </div>
                    <i class="bi bi-file-earmark-arrow-up position-absolute end-0 bottom-0 text-light m-3" style="font-size: 3rem;"></i>
                </div>
            </div>
        </div>
    @endif

    {{-- ===================== PROGRESS STEPPER (Pengaju & Reviewer) ==================== --}}
    @if($role === 'pengaju' || $role === 'reviewer')
        @php
            $direvisiDisplay = $direvisiFileCount ?? ($direvisiCount ?? 0);
            $currentStep = 0;

            if (($disetujuiCount ?? 0) > 0 || ($direvisiDisplay ?? 0) > 0 || ($hasilRevisiCount ?? 0) > 0 || ($ditolakCount ?? 0) > 0) {
                $currentStep = 4;
            } elseif (($reviewSelesaiCount ?? 0) > 0) {
                $currentStep = 3;
            } elseif (($perluDireviewCount ?? 0) > 0 || ($sedangDireviewCount ?? 0) > 0) {
                $currentStep = 2;
            } elseif (($daftarProposalCount ?? 0) > 0) {
                $currentStep = 1;
            }

            $labelHeader = $role === 'reviewer' ? 'Status Monitoring Penilaian' : 'Status Progress Pengajuan';
        @endphp

        <div class="card p-4 mb-4 shadow-sm border-0">
            <div class="d-flex justify-content-between align-items-start mb-4">
                <div>
                    <h6 class="mb-1 fw-bold text-dark">{{ $labelHeader }}</h6>
                    <p class="text-muted small mb-0">Pelacakan tahapan proposal secara real-time.</p>
                </div>
                <div class="badge rounded-pill bg-primary px-3 py-2">Tahap {{ $currentStep }} dari 4</div>
            </div>

            <div class="stepper position-relative">
                @php
                    $steps = ($role === 'reviewer')
                        ? [1 => 'Masuk', 2 => 'Proses', 3 => 'Selesai', 4 => 'Final']
                        : [1 => 'Dikirim', 2 => 'Direview', 3 => 'Keputusan', 4 => 'Selesai'];
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
                    </div>
                    @if($stepNum < 4)
                        <div class="line {{ $currentStep > $stepNum ? 'filled' : '' }}"></div>
                    @endif
                @endforeach
            </div>
        </div>
    @endif

    {{-- ===================== MONITORING CARDS ==================== --}}
    @php
        $dashboardItems = [
            ['title'=>'Proposal Masuk','count'=>$daftarProposalCount ?? 0,'route'=>'monitoring.proposalDikirim','icon'=>'bi-file-earmark-text'],
            ['title'=>'Butuh Review','count'=>$perluDireviewCount ?? 0,'route'=>'monitoring.proposalPerluDireview','icon'=>'bi-envelope-paper'],
            ['title'=>'Sedang Direview','count'=>$sedangDireviewCount ?? 0,'route'=>'monitoring.proposalSedangDireview','icon'=>'bi-arrow-repeat'],
            ['title'=>'Review Selesai','count'=>$reviewSelesaiCount ?? 0,'route'=>'monitoring.reviewSelesai','icon'=>'bi-clipboard-check'],
            ['title'=>'Disetujui','count'=>$disetujuiCount ?? 0,'route'=>'monitoring.proposalDisetujui','icon'=>'bi-check-circle'],
            ['title'=>'Ditolak','count'=>$ditolakCount ?? 0,'route'=>'monitoring.proposalDitolak','icon'=>'bi-x-circle'],
            ['title'=>'Perlu Revisi','count'=>$direvisiDisplay ?? 0,'route'=>'monitoring.proposalDirevisi','icon'=>'bi-pencil-square'],
            ['title'=>'Hasil Revisi','count'=>$hasilRevisiCount ?? 0,'route'=>'monitoring.hasilRevisi','icon'=>'bi-send-check'],
        ];

        if($role === 'pengaju'){
            $dashboardItems = array_filter($dashboardItems, fn($item) => !in_array($item['title'], ['Butuh Review', 'Sedang Direview']));
        }
    @endphp

    <div class="row g-3 mb-4">
        @foreach($dashboardItems as $item)
            <div class="col-6 col-md-3">
                <a href="{{ route($item['route']) }}" class="text-decoration-none dashboard-link">
                    <div class="card border-0 shadow-sm p-3 h-100 bg-white">
                        <div class="d-flex justify-content-between align-items-center mb-2">
                            <div class="icon-box bg-primary-subtle p-2 rounded">
                                <i class="bi {{ $item['icon'] }} text-primary fs-5"></i>
                            </div>
                            <h4 class="fw-bold mb-0 text-dark">{{ $item['count'] }}</h4>
                        </div>
                        <h6 class="mb-0 text-muted small fw-bold text-uppercase" style="font-size: 10px;">{{ $item['title'] }}</h6>
                    </div>
                </a>
            </div>
        @endforeach
    </div>

    {{-- ===================== REKAP FAKULTAS (Hanya Admin) ==================== --}}
    @if($role === 'admin' && isset($rekapFakultas))
    <div class="card border-0 shadow-sm mb-4 overflow-hidden">
        <div class="card-header bg-white py-3 d-flex justify-content-between align-items-center">
            <h6 class="fw-bold mb-0 text-dark"><i class="bi bi-bar-chart-fill me-2 text-primary"></i>Distribusi Hibah per Fakultas</h6>
            <span class="badge bg-light text-muted border fw-normal">Data Tahun {{ $tahun }}</span>
        </div>
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="bg-light">
                    <tr class="small text-muted text-uppercase" style="font-size: 10px;">
                        <th class="ps-4 py-3">Fakultas</th>
                        <th class="text-center">Pengajuan</th>
                        <th class="text-center">Disetujui</th>
                        <th class="text-end pe-4">Total Dana (Rp)</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($rekapFakultas as $rekap)
                    <tr>
                        <td class="ps-4">
                            <div class="fw-bold text-dark">{{ $rekap->fakultas ?? 'N/A' }}</div>
                        </td>
                        <td class="text-center">
                            <span class="fw-bold">{{ $rekap->total_pengajuan }}</span>
                        </td>
                        <td class="text-center">
                            <span class="badge bg-success-subtle text-success border border-success-subtle px-3">{{ $rekap->total_disetujui }}</span>
                        </td>
                        <td class="text-end pe-4 fw-bold text-primary">
                            {{ number_format($rekap->total_biaya ?? 0, 0, ',', '.') }}
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="4" class="text-center py-5 text-muted">
                            <i class="bi bi-inbox fs-2 d-block mb-2"></i>
                            Belum ada rekapan data untuk tahun {{ $tahun }}.
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
    @endif

    {{-- ===================== PROFIL & INFORMASI ==================== --}}
    <div class="row g-4">
        <div class="col-md-7">
            <div class="card p-4 border-0 shadow-sm h-100">
                <h6 class="fw-bold mb-3 border-bottom pb-2"><i class="bi bi-person-badge me-2 text-primary"></i>Profil Pengguna</h6>
                <div class="row g-3">
                    <div class="col-sm-6">
                        <label class="text-muted small fw-bold text-uppercase d-block" style="font-size: 10px;">Nama Lengkap</label>
                        <span class="text-dark fw-medium">{{ $user->name ?? '-' }}</span>
                    </div>
                    <div class="col-sm-6">
                        <label class="text-muted small fw-bold text-uppercase d-block" style="font-size: 10px;">Fakultas / Unit</label>
                        <span class="text-dark fw-medium">{{ $user->fakultas ?? '-' }}</span>
                    </div>
                    <div class="col-sm-6">
                        <label class="text-muted small fw-bold text-uppercase d-block" style="font-size: 10px;">Hak Akses</label>
                        <span class="badge bg-primary-subtle text-primary border border-primary-subtle">{{ strtoupper($role) }}</span>
                    </div>
                    <div class="col-sm-6">
                        <label class="text-muted small fw-bold text-uppercase d-block" style="font-size: 10px;">Email Terdaftar</label>
                        <span class="text-dark fw-medium">{{ $user->email ?? '-' }}</span>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-5">
            <div class="card p-4 border-0 shadow-sm h-100 bg-light border">
                <h6 class="fw-bold mb-2"><i class="bi bi-info-circle me-2 text-primary"></i>Pusat Bantuan</h6>
                <p class="small text-muted mb-4">Butuh panduan pengisian? Unduh dokumen petunjuk teknis di bawah ini atau hubungi admin fakultas.</p>
                <div class="d-grid">
                    <a href="{{ route('dokumen.user') }}" class="btn btn-primary btn-sm rounded-pill shadow-sm">
                        <i class="bi bi-download me-2"></i>Unduh Panduan Layanan
                    </a>
                </div>
            </div>
        </div>
    </div>
</div>

@endsection

@push('styles')
<style>
    /* Global Card styling */
    .card { border-radius: 12px; }

    /* Interactive Dashboard Cards */
    .dashboard-link .card { transition: all 0.2s ease-in-out; border: 1px solid transparent; }
    .dashboard-link:hover .card { transform: translateY(-3px); box-shadow: 0 8px 15px rgba(0,0,0,0.05) !important; border-color: #0d6efd; }
    .icon-box { width: 40px; height: 40px; display: flex; align-items: center; justify-content: center; }

    /* Stepper Logic */
    .stepper { display: flex; align-items: center; justify-content: space-between; padding: 0 10px; }
    .step { display: flex; flex-direction: column; align-items: center; z-index: 2; position: relative; width: 60px; }
    .dot { width: 32px; height: 32px; border-radius: 50%; display: flex; align-items: center; justify-content: center; font-weight: bold; font-size: 12px; background: #fff; border: 2px solid #e9ecef; color: #adb5bd; transition: 0.4s; }
    .dot.done { background: #198754; border-color: #198754; color: #fff; }
    .dot.active { border-color: #0d6efd; color: #0d6efd; background: #eef6ff; box-shadow: 0 0 0 4px rgba(13,110,253,0.1); }
    .label { margin-top: 8px; font-weight: 700; font-size: 11px; color: #6c757d; text-transform: uppercase; }
    .line { flex: 1; height: 3px; background: #e9ecef; margin-top: -22px; z-index: 1; border-radius: 10px; }
    .line.filled { background: #198754; }

    /* Table & Badges */
    .bg-success-subtle { background-color: #e8f5e9; color: #2e7d32; }
    .bg-primary-subtle { background-color: #eef6ff; }
</style>
@endpush

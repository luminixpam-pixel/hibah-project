@extends('layouts.app')

@section('content')

<div class="container mt-4">

{{-- ===================== DASHBOARD PROGRESS ==================== --}}
@php
    $role = Auth::user()->role;

    // ✅ kalau controller belum kirim $direvisiFileCount, fallback ke direvisiCount
    $direvisiDisplay = $direvisiFileCount ?? ($direvisiCount ?? 0);

    // ===== Tentukan step aktif (berdasarkan data yang SUDAH ADA di dashboard) =====
    // 0 = belum ada proposal
    // 1 = Dikirim
    // 2 = Direview (Perlu/Sedang)
    // 3 = Keputusan (Review Selesai)
    // 4 = Selesai (Disetujui / Direvisi / Hasil Revisi)
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

    // teks kecil status terakhir (biar jelas)
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
                $steps = [
                    1 => 'Dikirim',
                    2 => 'Direview',
                    3 => 'Keputusan',
                    4 => 'Selesai',
                ];
            @endphp

            @foreach($steps as $stepNum => $stepText)
                @php
                    // ✅ FIX: step 4 harus bisa centang juga saat currentStep = 4
                    $isDone = ($stepNum === 4) ? ($currentStep >= 4) : ($currentStep > $stepNum);

                    $isActive = $currentStep === $stepNum;
                    $dotClass = $isDone ? 'done' : ($isActive ? 'active' : 'todo');
                @endphp

                <div class="step">
                    <div class="dot {{ $dotClass }}">
                        @if($isDone)
                            <i class="bi bi-check-lg"></i>
                        @else
                            {{ $stepNum }}
                        @endif
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

        <div class="mt-3 small text-muted">
            Catatan: kalau proposal <b>Ditolak</b>, proposal akan muncul di menu <b>Proposal Direvisi</b> untuk diperbaiki.
        </div>
    </div>
@endif

{{-- ===================== DASHBOARD CARD ==================== --}}
@php
    $dashboardItems = [
        ['title'=>'Daftar Proposal','count'=>$daftarProposalCount ?? 0,'route'=>'monitoring.proposalDikirim'],
        ['title'=>'Proposal Perlu Direview','count'=>$perluDireviewCount ?? 0,'route'=>'monitoring.proposalPerluDireview'],
        ['title'=>'Proposal Sedang Direview','count'=>$sedangDireviewCount ?? 0,'route'=>'monitoring.proposalSedangDireview'],
        ['title'=>'Review Selesai','count'=>$reviewSelesaiCount ?? 0,'route'=>'monitoring.reviewSelesai'],
        ['title'=>'Proposal Disetujui','count'=>$disetujuiCount ?? 0,'route'=>'monitoring.proposalDisetujui'],
        ['title'=>'Proposal Ditolak','count'=>$ditolakCount ?? 0,'route'=>'monitoring.proposalDitolak'],

        // ✅ tampilkan jumlah file revisi kalau ada
        ['title'=>'Proposal Direvisi','count'=>$direvisiDisplay,'route'=>'monitoring.proposalDirevisi'],

        ['title'=>'Hasil Revisi','count'=>$hasilRevisiCount ?? 0,'route'=>'monitoring.hasilRevisi'],
    ];

    if($role==='pengaju'){
        $dashboardItems = array_filter($dashboardItems,function($item){
            return $item['title']!=='Proposal Perlu Direview' && $item['title']!=='Proposal Sedang Direview';
        });
    }

    $currentRoute = Route::currentRouteName();
@endphp

<div class="row g-3 mb-4 {{ $role==='pengaju'?'justify-content-center':'' }}">
    @foreach($dashboardItems as $item)
        <div class="col-6 col-md-3">
            <a href="{{ route($item['route']) }}" class="text-decoration-none dashboard-link {{ $currentRoute === $item['route'] ? 'dashboard-link-active' : '' }}">
                <div class="card text-center p-3 border shadow-sm h-100">
                    <h6 class="mb-2 text-dark">{{ $item['title'] }}</h6>
                    <h4 class="text-dark">{{ $item['count'] }}</h4>
                </div>
            </a>
        </div>
    @endforeach
</div>

{{-- ===================== PROFIL PENGGUNA ==================== --}}
<div class="card p-4">
    <h5 class="mb-3">Profil Pengguna - Anda login sebagai <b>{{ $user->role_label ?? 'Role' }}</b></h5>
    <p><strong>Nama Lengkap:</strong> {{ $user->name ?? '-' }}</p>
    <p><strong>NIDN / NIP:</strong> {{ $user->nidn ?? '-' }}</p>
    <p><strong>Email:</strong> {{ $user->email ?? '-' }}</p>
    <p><strong>Nomor Telepon:</strong> {{ $user->no_telepon ?? '-' }}</p>
    <p><strong>Fakultas:</strong> {{ $user->fakultas ?? '-' }}</p>
    <p><strong>Program Studi:</strong> {{ $user->program_studi ?? '-' }}</p>
    <p><strong>Jabatan / Posisi:</strong> {{ $user->jabatan ?? '-' }}</p>
</div>

</div>

@endsection

@push('styles')
<style>
.dashboard-link .card { transition:box-shadow 0.2s ease, transform 0.15s ease, border-color 0.15s ease, background-color 0.15s ease; }
.dashboard-link:hover .card { transform:translateY(-2px); box-shadow:0 8px 24px rgba(15,23,42,0.12); }
.dashboard-link-active .card { border:2px solid #2563eb; box-shadow:0 0 0 2px rgba(37,99,235,.18); background:#eef2ff; }

/* ===== Stepper ===== */
.stepper{display:flex;align-items:flex-start;justify-content:space-between;gap:10px;flex-wrap:nowrap;overflow-x:auto;padding:10px 2px;}
.step{min-width:120px;display:flex;flex-direction:column;align-items:center;text-align:center;}
.dot{width:38px;height:38px;border-radius:999px;display:flex;align-items:center;justify-content:center;font-weight:700;border:2px solid #cbd5e1;background:#fff;color:#334155;}
.dot.done{background:#0d6efd;border-color:#0d6efd;color:#fff;}
.dot.active{border-color:#0d6efd;color:#0d6efd;box-shadow:0 0 0 3px rgba(13,110,253,.18);}
.dot.todo{opacity:.8;}
.label{margin-top:8px;font-weight:600;font-size:14px;color:#0f172a;}
.sub{font-size:12px;color:#64748b;margin-top:2px;}
.line{flex:1;height:4px;border-radius:999px;background:#e2e8f0;margin-top:17px;}
.line.filled{background:#0d6efd;}
</style>
@endpush

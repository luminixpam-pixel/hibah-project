@extends('layouts.app')

@section('content')

<div class="container mt-4">

{{-- ===================== DASHBOARD CARD ==================== --}}
@php
    $role = Auth::user()->role;

    // ✅ kalau controller belum kirim $direvisiFileCount, fallback ke direvisiCount
    $direvisiDisplay = $direvisiFileCount ?? ($direvisiCount ?? 0);

    $dashboardItems = [
        ['title'=>'Daftar Proposal','count'=>$daftarProposalCount ?? 0,'route'=>'monitoring.proposalDikirim'],
        ['title'=>'Proposal Perlu Direview','count'=>$perluDireviewCount ?? 0,'route'=>'monitoring.proposalPerluDireview'],
        ['title'=>'Proposal Sedang Direview','count'=>$sedangDireviewCount ?? 0,'route'=>'monitoring.proposalSedangDireview'],
        ['title'=>'Review Selesai','count'=>$reviewSelesaiCount ?? 0,'route'=>'monitoring.reviewSelesai'],
        ['title'=>'Proposal Disetujui','count'=>$disetujuiCount ?? 0,'route'=>'monitoring.proposalDisetujui'],
        ['title'=>'Proposal Ditolak','count'=>$ditolakCount ?? 0,'route'=>'monitoring.proposalDitolak'],

        // ✅ INI yang berubah: tampilkan jumlah file revisi kalau ada
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
</style>
@endpush

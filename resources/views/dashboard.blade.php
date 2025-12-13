@extends('layouts.app')

@section('content')

<div class="container mt-4">

{{-- 🔴 TOAST ERROR FORMAT FILE --}}
<div id="toastFileError"
     class="toast align-items-center text-white bg-danger border-0 position-fixed"
     style="top: 70px; right: 20px; z-index: 99999; display:none;"
     role="alert">
    <div class="d-flex">
        <div class="toast-body">
            Format file tidak valid! Hanya PDF atau Word (DOC/DOCX) yang diperbolehkan.
        </div>
    </div>
</div>

{{-- ===================== DASHBOARD CARD ==================== --}}
@php
    $role = Auth::user()->role;
    $dashboardItems = [
        ['title'=>'Daftar Proposal','count'=>$daftarProposalCount ?? 0,'route'=>'monitoring.proposalDikirim'],
        ['title'=>'Proposal Perlu Direview','count'=>$perluDireviewCount ?? 0,'route'=>'monitoring.proposalPerluDireview'],
        ['title'=>'Proposal Sedang Direview','count'=>$sedangDireviewCount ?? 0,'route'=>'monitoring.proposalSedangDireview'],
        ['title'=>'Review Selesai','count'=>$reviewSelesaiCount ?? 0,'route'=>'monitoring.reviewSelesai'],
        ['title'=>'Proposal Disetujui','count'=>$disetujuiCount ?? 0,'route'=>'monitoring.proposalDisetujui'],
        ['title'=>'Proposal Ditolak','count'=>$ditolakCount ?? 0,'route'=>'monitoring.proposalDitolak'],
        ['title'=>'Proposal Direvisi','count'=>$direvisiCount ?? 0,'route'=>'monitoring.proposalDirevisi'],
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

{{-- ===================== POPUP AJUKAN PROPOSAL ==================== --}}
@if(Auth::user()->role==='pengaju')
<div id="proposalPopup" class="popup-overlay">
    <div class="popup-inner">
        <div class="popup-content">
            <span class="close-popup" id="closePopupBtn">&times;</span>
            <form action="{{ route('proposal.store') }}" method="POST" enctype="multipart/form-data">
                @csrf
                <div class="mb-3">
                    <label class="form-label">Nama Ketua</label>
                    <input type="text" name="nama_ketua" class="form-control" required>
                </div>
                <div class="mb-3">
                    <label class="form-label">Anggota</label>
                    <div id="anggota-container">
                        <div class="d-flex gap-2 mb-2 anggota-row">
                            <input type="text" name="anggota[]" class="form-control">
                        </div>
                    </div>
                    <button type="button" class="btn btn-sm btn-primary mt-2" id="addAnggotaBtn">+ Tambah Anggota</button>
                </div>
                <div class="mb-3">
                    <label class="form-label">Biaya</label>
                    <input type="text" name="biaya" class="form-control">
                </div>
                <div class="mb-3">
                    <label class="form-label">Judul Proposal</label>
                    <input type="text" name="judul" class="form-control">
                </div>
                <div class="mb-3">
                    <label class="form-label">File Proposal</label>
                    <input type="file" name="file" class="form-control" accept=".pdf,.doc,.docx">
                </div>
                <button class="btn btn-success w-100" type="submit">Kirim Proposal</button>
            </form>
        </div>
    </div>
</div>
@endif

@endsection

@push('styles')
<style>
.popup-overlay { display:none; position:fixed; top:0; left:0; width:100%; height:100%; background:rgba(0,0,0,0.45); z-index:99999; }
.popup-inner { display:flex; justify-content:center; align-items:center; width:100%; height:100%; }
.popup-content { background:#fff; padding:25px; border-radius:14px; width:430px; position:relative; transform:scale(0.85); opacity:0; transition:0.25s ease; box-shadow:0 8px 30px rgba(0,0,0,0.3); }
.popup-overlay.active .popup-content { transform:scale(1); opacity:1; }
.close-popup { position:absolute; top:12px; right:15px; font-size:25px; cursor:pointer; color:#444; }
.close-popup:hover { color:red; }
.dashboard-link .card { transition:box-shadow 0.2s ease, transform 0.15s ease, border-color 0.15s ease, background-color 0.15s ease; }
.dashboard-link:hover .card { transform:translateY(-2px); box-shadow:0 8px 24px rgba(15,23,42,0.12); }
.dashboard-link-active .card { border:2px solid #2563eb; box-shadow:0 0 0 2px rgba(37,99,235,.18); background:#eef2ff; }
</style>
@endpush

@push('scripts')
<script>
document.addEventListener("DOMContentLoaded", function () {

    const openBtn = document.getElementById("openPopupBtn");
    const closeBtn = document.getElementById("closePopupBtn");
    const popup = document.getElementById("proposalPopup");

    if(openBtn){
        openBtn.addEventListener("click", ()=>{ popup.style.display="flex"; setTimeout(()=>popup.classList.add("active"),10); });
    }

    if(closeBtn){
        closeBtn.addEventListener("click", ()=>{ popup.classList.remove("active"); setTimeout(()=>popup.style.display="none",250); });
    }

    if(popup){
        popup.addEventListener("click", e=>{ if(e.target===popup){ popup.classList.remove("active"); setTimeout(()=>popup.style.display="none",250); } });
    }

    // Tambah anggota dinamis
    const anggotaContainer = document.getElementById("anggota-container");
    const addBtn = document.getElementById("addAnggotaBtn");

    if(addBtn){
        addBtn.addEventListener("click", function () {
            const div = document.createElement("div");
            div.classList.add("d-flex","gap-2","mb-2","anggota-row");
            div.innerHTML = `<input type="text" name="anggota[]" class="form-control">
                             <button type="button" class="btn btn-danger btn-sm remove-anggota">Hapus</button>`;
            anggotaContainer.appendChild(div);
        });
    }

    document.addEventListener("click", function(e){
        if(e.target.classList.contains("remove-anggota")){
            e.target.parentElement.remove();
        }
    });

});
</script>

{{-- Cek tipe file --}}
<script>
document.addEventListener("DOMContentLoaded", function () {
    const fileInput = document.querySelector('#proposalPopup input[name="file"]');
    const toastFileError = document.getElementById("toastFileError");

    if(fileInput && toastFileError){
        fileInput.addEventListener("change", function(){
            if(!this.files.length) return;
            const allowed = ['pdf','doc','docx'];
            const ext = this.files[0].name.toLowerCase().split('.').pop();
            if(!allowed.includes(ext)){
                this.value="";
                toastFileError.style.display="block";
                let toast = new bootstrap.Toast(toastFileError,{ delay:3000 });
                toast.show();
                setTimeout(()=>{ toastFileError.style.display="none"; },3500);
            }
        });
    }
});
</script>
@endpush

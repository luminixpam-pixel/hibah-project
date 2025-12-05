@extends('layouts.app')

@section('content')

<div class="container mt-4">

{{-- =======================
    TOMBOL AJUKAN PROPOSAL
======================== --}}
<div class="d-flex justify-content-between mb-3">

    @if(Auth::user()->role === 'pengaju')
    <button id="openPopupBtn" class="btn btn-success">
        Unggah Proposal
    </button>
    @endif

</div>

{{-- 🔴 TOAST ERROR FORMAT FILE (untuk popup upload di dashboard) --}}
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
{{-- 🔴 TOAST SELESAI --}}

{{-- =======================
    DASHBOARD CARD
======================== --}}
@php
    $role = Auth::user()->role;

    $dashboardItems = [
        // 1. Pengajuan awal
        [
            'title' => 'Daftar Proposal',
            'count' => $daftarProposalCount ?? 0,
            'route' => 'monitoring.proposalDikirim',
        ],

        // 2. Masuk antrian & proses review
        [
            'title' => 'Proposal Perlu Direview',
            'count' => $perluDireviewCount ?? 0,
            'route' => 'monitoring.proposalPerluDireview'
        ],
        [
            'title' => 'Proposal Sedang Direview',
            'count' => $sedangDireviewCount ?? 0,
            'route' => 'monitoring.proposalSedangDireview'
        ],
        [
            'title' => 'Review Selesai',
            'count' => $reviewSelesaiCount ?? 0,
            'route' => 'monitoring.reviewSelesai'
        ],

        // 3. Hasil review
        [
            'title' => 'Proposal Disetujui',
            'count' => $disetujuiCount ?? 0,
            'route' => 'monitoring.proposalDisetujui'
        ],
        [
            'title' => 'Proposal Ditolak',
            'count' => $ditolakCount ?? 0,
            'route' => 'monitoring.proposalDitolak'
        ],

        // 4. Revisi setelah hasil review
        [
            'title' => 'Proposal Direvisi',
            'count' => $direvisiCount ?? 0,
            'route' => 'monitoring.proposalDirevisi'
        ],
        [
            'title' => 'Hasil Revisi',
            'count' => $hasilRevisiCount ?? 0,
            'route' => 'monitoring.hasilRevisi'
        ],
    ];

    // Hapus 2 card khusus pengaju
    if ($role === 'pengaju') {
        $dashboardItems = array_filter($dashboardItems, function ($item) {
            return $item['title'] !== 'Proposal Perlu Direview'
                && $item['title'] !== 'Proposal Sedang Direview';
        });
    }
@endphp

<div class="row g-3 mb-4 {{ $role === 'pengaju' ? 'justify-content-center' : '' }}">
    @foreach ($dashboardItems as $item)
        <div class="col-6 col-md-3">
            <a href="{{ route($item['route']) }}" class="text-decoration-none">
                <div class="card text-center p-3 border shadow-sm h-100">
                    <h6 class="mb-2 text-dark">{{ $item['title'] }}</h6>
                    <h4 class="text-dark">{{ $item['count'] }}</h4>
                </div>
            </a>
        </div>
    @endforeach
</div>


{{-- =======================
    PROFIL PENGGUNA
======================== --}}
<div class="card p-4">
    <h5 class="mb-3">
        Profil Pengguna - Anda login sebagai
        <b>{{ $user->role_label ?? 'Role' }}</b>
    </h5>

    <p><strong>Nama Lengkap:</strong> {{ $user->name ?? '-' }}</p>
    <p><strong>NIDN / NIP:</strong> {{ $user->nidn ?? '-' }}</p>
    <p><strong>Email:</strong> {{ $user->email ?? '-' }}</p>
    <p><strong>Nomor Telepon:</strong> {{ $user->no_telepon ?? '-' }}</p>
    <p><strong>Fakultas:</strong> {{ $user->fakultas ?? '-' }}</p>
    <p><strong>Program Studi:</strong> {{ $user->program_studi ?? '-' }}</p>
    <p><strong>Jabatan / Posisi:</strong> {{ $user->jabatan ?? '-' }}</p>
</div>

</div>

{{-- =======================
    POPUP AJUKAN PROPOSAL
======================== --}}
@if(Auth::user()->role === 'pengaju')
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
                    {{-- batasi pilihan di dialog: hanya pdf/doc/docx --}}
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
.popup-overlay {
    display: none;
    position: fixed;
    top:0; left:0;
    width:100%; height:100%;
    background: rgba(0,0,0,0.45);
    z-index: 99999;
}

.popup-inner {
    display: flex;
    justify-content: center;
    align-items: center;
    width: 100%;
    height: 100%;
}

.popup-content {
    background: #fff;
    padding: 25px;
    border-radius: 14px;
    width: 430px;
    position: relative;
    transform: scale(0.85);
    opacity: 0;
    transition: 0.25s ease;
    box-shadow: 0 8px 30px rgba(0,0,0,0.3);
}

.popup-overlay.active .popup-content {
    transform: scale(1);
    opacity: 1;
}

.close-popup {
    position: absolute;
    top: 12px;
    right: 15px;
    font-size: 25px;
    cursor: pointer;
    color: #444;
}

.close-popup:hover { color: red; }
</style>
@endpush

@push('scripts')
<script>
document.addEventListener("DOMContentLoaded", function () {

    const openBtn = document.getElementById("openPopupBtn");
    const closeBtn = document.getElementById("closePopupBtn");
    const popup = document.getElementById("proposalPopup");

    if(openBtn){
        openBtn.addEventListener("click", () => {
            popup.style.display = "flex";
            setTimeout(() => popup.classList.add("active"), 10);
        });
    }

    if(closeBtn){
        closeBtn.addEventListener("click", () => {
            popup.classList.remove("active");
            setTimeout(() => popup.style.display = "none", 250);
        });
    }

    if(popup){
        popup.addEventListener("click", (e) => {
            if(e.target === popup){
                popup.classList.remove("active");
                setTimeout(() => popup.style.display = "none", 250);
            }
        });
    }

    // Tambah anggota dinamis
    const anggotaContainer = document.getElementById("anggota-container");
    const addBtn = document.getElementById("addAnggotaBtn");

    if(addBtn){
        addBtn.addEventListener("click", function () {
            const div = document.createElement("div");
            div.classList.add("d-flex", "gap-2", "mb-2", "anggota-row");
            div.innerHTML = `
                <input type="text" name="anggota[]" class="form-control">
                <button type="button" class="btn btn-danger btn-sm remove-anggota">Hapus</button>
            `;
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
@endpush

{{-- script tambahan khusus cek tipe file dan munculin toast error --}}
@push('scripts')
<script>
document.addEventListener("DOMContentLoaded", function () {

    const fileInput = document.querySelector('#proposalPopup input[name="file"]');
    const toastFileError = document.getElementById("toastFileError");

    if (fileInput && toastFileError) {
        fileInput.addEventListener("change", function () {
            if (!this.files.length) return;

            const allowed = ['pdf', 'doc', 'docx'];
            const filename = this.files[0].name.toLowerCase();
            const ext = filename.split('.').pop();

            if (!allowed.includes(ext)) {

                // reset input
                this.value = "";

                // tampilkan toast
                toastFileError.style.display = "block";

                let toast = new bootstrap.Toast(toastFileError, { delay: 3000 });
                toast.show();

                // sembunyikan setelah selesai
                setTimeout(() => {
                    toastFileError.style.display = "none";
                }, 3500);
            }
        });
    }

});
</script>
@endpush

@extends('layouts.app')

@section('content')

<div class="container mt-4">

{{-- =======================
    TOMBOL AJUKAN PROPOSAL
======================== --}}
<div class="d-flex justify-content-between mb-3">
    <button id="openPopupBtn" class="btn btn-success">
        Unggah Proposal
    </button>

</div>

{{-- =======================
    DASHBOARD CARD
======================== --}}
<div class="row g-3 mb-4">
    @php
    $dashboardItems = [
        ['title' => 'Proposal Dikirim', 'count' => 25, 'route' => 'monitoring.proposalDikirim'],
        ['title' => 'Proposal Disetujui', 'count' => 25, 'route' => 'monitoring.proposalDisetujui'],
        ['title' => 'Proposal Ditolak', 'count' => 0, 'route' => 'monitoring.proposalDitolak'],
        ['title' => 'Hasil Revisi', 'count' => 0, 'route' => 'monitoring.hasilRevisi'],
        ['title' => 'Proposal Perlu Direview', 'count' => 0, 'route' => 'monitoring.proposalPerluDireview'],
        ['title' => 'Proposal Sedang Direview', 'count' => 0, 'route' => 'monitoring.proposalSedangDireview'],
        ['title' => 'Review Selesai', 'count' => 0, 'route' => 'monitoring.reviewSelesai'],
        ['title' => 'Proposal Direvisi', 'count' => 0, 'route' => 'monitoring.proposalDirevisi'],
    ];
    @endphp

    @foreach ($dashboardItems as $item)
        <div class="col-md-3">
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
    <h5 class="mb-3">Profil Pengguna - Anda login sebagai <b>{{ $user->role ?? 'Role' }}</b></h5>
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
DI LUAR CONTAINER
======================== --}}

<div id="proposalPopup" class="popup-overlay">
    <div class="popup-inner">
        <div class="popup-content">
            <span class="close-popup" id="closePopupBtn">&times;</span>

        <form action="{{ route('proposal.store') }}" method="POST" enctype="multipart/form-data">
            @csrf
            <div class="mb-3">
                <label class="form-label">Nama Ketua</label>
                <input type="text" name="nama_ketua" class="form-control" required placeholder="Masukkan nama ketua">
            </div>

            <div class="mb-3">
                <label class="form-label">Anggota</label>
                <div id="anggota-container">
                    <div class="d-flex gap-2 mb-2 anggota-row">
                        <input type="text" name="anggota[]" class="form-control" placeholder="Masukkan nama anggota">
                    </div>
                </div>
                <button type="button" class="btn btn-sm btn-primary mt-2" id="addAnggotaBtn">+ Tambah Anggota</button>
            </div>

            <div class="mb-3">
                <label class="form-label">Biaya</label>
                <input type="text" name="biaya" class="form-control" placeholder="Masukkan biaya">
            </div>

            <div class="mb-3">
                <label class="form-label">Judul Proposal</label>
                <input type="text" name="judul" class="form-control" placeholder="Masukkan judul proposal">
            </div>

            <div class="mb-3">
                <label class="form-label">File Proposal</label>
                <input type="file" name="file" class="form-control">
            </div>

            <button class="btn btn-success w-100" type="submit">Kirim Proposal</button>
        </form>
    </div>
</div>

</div>

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

.popup-content .close-popup {
    position: absolute;
    top: 12px;
    right: 15px;
    font-size: 25px;
    cursor: pointer;
    color: #444;
}

.popup-content .close-popup:hover { color: red; }
</style>

@endpush

@push('scripts')

<script>
document.addEventListener("DOMContentLoaded", function () {
    const openBtn = document.getElementById("openPopupBtn");
    const closeBtn = document.getElementById("closePopupBtn");
    const popup = document.getElementById("proposalPopup");

    // Buka popup
    openBtn.addEventListener("click", () => {
        popup.style.display = "flex";
        setTimeout(() => popup.classList.add("active"), 10);
    });

    // Tutup popup
    closeBtn.addEventListener("click", () => {
        popup.classList.remove("active");
        setTimeout(() => popup.style.display = "none", 250);
    });

    // Tutup klik luar popup
    popup.addEventListener("click", (e) => {
        if(e.target === popup){
            popup.classList.remove("active");
            setTimeout(() => popup.style.display = "none", 250);
        }
    });

    // Tambah / hapus anggota
    const anggotaContainer = document.getElementById("anggota-container");
    const addBtn = document.getElementById("addAnggotaBtn");

    addBtn.addEventListener("click", function () {
        const div = document.createElement("div");
        div.classList.add("d-flex", "gap-2", "mb-2", "anggota-row");
        div.innerHTML = `
            <input type="text" name="anggota[]" class="form-control" placeholder="Masukkan nama anggota">
            <button type="button" class="btn btn-danger btn-sm remove-anggota">Hapus</button>
        `;
        anggotaContainer.appendChild(div);
    });

    document.addEventListener("click", function(e){
        if(e.target.classList.contains("remove-anggota")){
            e.target.parentElement.remove();
        }
    });
});
</script>

@endpush

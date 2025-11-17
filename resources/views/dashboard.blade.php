@extends('layouts.app')

@section('content')
<div class="container mt-4">

    {{-- =======================
        CSS POPUP
    ======================== --}}
    <style>
        .popup-overlay {
            display: none;
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(0, 0, 0, 0.55);
            backdrop-filter: blur(3px);
            justify-content: center;
            align-items: center;
            z-index: 9999;
        }

        .popup-content {
            background: #fff;
            padding: 25px;
            border-radius: 12px;
            width: 480px;
            position: relative;
            box-shadow: 0 8px 30px rgba(0,0,0,0.2);
            transform: scale(0.8);
            opacity: 0;
            transition: .25s ease;
        }

        .popup-overlay.active .popup-content {
            transform: scale(1);
            opacity: 1;
        }

        .close-popup {
            position: absolute;
            top: 10px;
            right: 15px;
            font-size: 25px;
            font-weight: bold;
            cursor: pointer;
            color: #555;
        }
        .close-popup:hover { color: red; }
    </style>

    {{-- =======================
        TOMBOL AJUKAN PROPOSAL
    ======================== --}}
    <button id="openPopupBtn" class="btn btn-success mb-3">
        Ajukan Proposal
    </button>

    {{-- =======================
            POPUP FORM
    ======================== --}}
    <div id="proposalPopup" class="popup-overlay">
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

                    <button type="button" class="btn btn-sm btn-primary mt-2" id="addAnggotaBtn">
                        + Tambah Anggota
                    </button>
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

                <button class="btn btn-success w-100" type="submit">
                    Kirim Proposal
                </button>
            </form>
        </div>
    </div>

    {{-- =======================
        SCRIPT POPUP + ANGGOTA DINAMIS
    ======================== --}}
    <script>
        document.addEventListener("DOMContentLoaded", function () {

            // === Script Popup ===
            const openBtn = document.getElementById("openPopupBtn");
            const closeBtn = document.getElementById("closePopupBtn");
            const popup = document.getElementById("proposalPopup");

            openBtn.addEventListener("click", () => {
                popup.style.display = "flex";
                setTimeout(() => popup.classList.add("active"), 10);
            });

            closeBtn.addEventListener("click", () => {
                popup.classList.remove("active");
                setTimeout(() => popup.style.display = "none", 250);
            });

            popup.addEventListener("click", (e) => {
                if (e.target === popup) {
                    popup.classList.remove("active");
                    setTimeout(() => popup.style.display = "none", 250);
                }
            });


            // === Script Tambah/Hapus Anggota ===
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

            document.addEventListener("click", function (e) {
                if (e.target.classList.contains("remove-anggota")) {
                    e.target.parentElement.remove();
                }
            });

        });
    </script>

    {{-- =======================
        KONTEN DASBOR
    ======================== --}}
    <div class="row g-3 mb-4">
        <div class="col-md-3">
            <div class="card text-center p-3">
                <h6>Proposal Dikirim</h6>
                <h4>25</h4>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card text-center p-3">
                <h6>Proposal Disetujui</h6>
                <h4>25</h4>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card text-center p-3">
                <h6>Proposal Ditolak</h6>
                <h4>0</h4>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card text-center p-3">
                <h6>Proposal Direvisi</h6>
                <h4>0</h4>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card text-center p-3">
                <h6>Hasil Revisi</h6>
                <h4>0</h4>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card text-center p-3">
                <h6>Proposal perlu Direwiew</h6>
                <h4>0</h4>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card text-center p-3">
                <h6>Proposal sedang Direwiew</h6>
                <h4>0</h4>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card text-center p-3">
                <h6>Review selesai</h6>
                <h4>0</h4>
            </div>
        </div>
    </div>

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
@endsection

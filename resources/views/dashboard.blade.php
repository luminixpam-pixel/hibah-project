@extends('layouts.app')

@section('content')
<div class="container mt-4">

    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <!-- Tombol Ajukan Proposal -->
            <button id="openPopupBtn" class="btn btn-success">
                Ajukan Proposal
            </button>

        </div>
    </div>

    {{-- =======================
        CSS POPUP
    ======================== --}}
    <style>
        /* Overlay */
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

        /* Isi Popup */
        .popup-content {
            background: #fff;
            padding: 25px;
            border-radius: 12px;
            width: 430px;
            position: relative;
            box-shadow: 0 8px 30px rgba(0,0,0,0.2);
            transform: scale(0.8);
            opacity: 0;
            transition: 0.25s ease;
        }

        /* Efek muncul */
        .popup-overlay.active .popup-content {
            transform: scale(1);
            opacity: 1;
        }

        /* Tombol close X */
        .close-popup {
            position: absolute;
            top: 12px;
            right: 15px;
            font-size: 25px;
            cursor: pointer;
            color: #444;
            font-weight: bold;
        }
        .close-popup:hover {
            color: red;
        }

    </style>

    {{-- =======================
        POPUP FORM AJUKAN PROPOSAL
    ======================== --}}
    <div id="proposalPopup" class="popup-overlay">
        <div class="popup-content">
            <span class="close-popup" id="closePopupBtn">&times;</span>

            <form>
                <div class="mb-3">
                    <label class="form-label">Judul Proposal</label>
                    <input type="text" class="form-control" placeholder="Masukkan judul proposal">
                </div>

                <div class="mb-3">
                    <label class="form-label">File Proposal (PDF)</label>
                    <input type="file" class="form-control">
                </div>

                <button type="submit" class="btn btn-success w-100">
                    Unggah Proposal
                </button>
            </form>
        </div>
    </div>

    {{-- =======================
        JAVASCRIPT POPUP
    ======================== --}}
    <script>
        document.addEventListener("DOMContentLoaded", function () {

            const openPopupBtn  = document.getElementById("openPopupBtn");
            const closePopupBtn = document.getElementById("closePopupBtn");
            const proposalPopup = document.getElementById("proposalPopup");

            // Buka Popup
            openPopupBtn.addEventListener("click", function () {
                proposalPopup.style.display = "flex";
                setTimeout(() => proposalPopup.classList.add("active"), 10);
            });

            // Tutup Popup pakai tombol X
            closePopupBtn.addEventListener("click", function () {
                proposalPopup.classList.remove("active");
                setTimeout(() => proposalPopup.style.display = "none", 250);
            });

            // Tutup ketika klik di luar popup
            proposalPopup.addEventListener("click", function (e) {
                if (e.target === proposalPopup) {
                    proposalPopup.classList.remove("active");
                    setTimeout(() => proposalPopup.style.display = "none", 250);
                }
            });
        });
    </script>

    {{-- =======================
        KONTEN LAIN (Tanpa perubahan)
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
        </div> <div class="col-md-3">
            <div class="card text-center p-3">
                <h6>Proposal perlu Direwiew</h6>
                <h4>0</h4>
            </div>
        </div> <div class="col-md-3">
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

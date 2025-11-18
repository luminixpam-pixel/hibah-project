<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', 'Luminix PAM')</title>

    {{-- Bootstrap --}}
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css" rel="stylesheet">

    {{-- Font --}}
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600&display=swap" rel="stylesheet">

    <style>
        body {
            font-family: 'Poppins', sans-serif;
            background: linear-gradient(135deg, #d9fcd4, #f6fef7);
            min-height: 100vh;
        }

        /* Navbar */
        #mainNavbar.navbar-custom {
            background-color: #1fbd4c;
        }
        #mainNavbar .nav-link,
        #mainNavbar .navbar-brand {
            color: #fff !important;
        }

        /* Navbar item kiri */
        #mainNavbar .navbar-left {
            margin-right: auto;
        }

        /* Samakan ukuran semua menu (ikon & teks) */
        #mainNavbar .nav-link {
            font-size: 1rem;
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }
        #mainNavbar .nav-link i {
            font-size: 1.3rem;
        }

        /* Content */
        #mainContent.content-wrapper {
            padding: 20px;
            backdrop-filter: blur(10px);
            background-color: rgba(255,255,255,0.8);
            border-radius: 15px;
            margin-top: 20px;
        }

        /* -------- NOTIFICATION POPUP -------- */
        .notif-popup-overlay {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            display: none;
            justify-content: center;
            align-items: flex-start;
            padding-top: 80px;
            background: rgba(0,0,0,0.15);
            z-index: 3000;
        }

        .notif-popup {
            width: 420px;
            max-height: 70vh;
            background: white;
            border-radius: 12px;
            padding: 20px;
            box-shadow: 0 6px 30px rgba(0,0,0,0.18);
            overflow-y: auto;
            animation: fadeIn 0.25s ease-out;
        }

        @keyframes fadeIn {
            from { opacity: 0; transform: scale(0.94); }
            to { opacity: 1; transform: scale(1); }
        }

        .notif-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 15px;
        }

        .mark-read {
            color: #1fbd4c;
            font-size: 0.85rem;
            cursor: pointer;
        }

        .notif-item {
            margin-bottom: 15px;
            padding-bottom: 10px;
            border-bottom: 1px solid #eee;
        }

        .notif-title {
            font-size: 0.95rem;
            font-weight: 600;
        }

        .notif-time {
            font-size: 0.8rem;
            color: gray;
        }

        .notif-expand {
            cursor: pointer;
            font-size: 1.1rem;
            color: #1fbd4c;
        }

        .notif-popup.large {
            width: 650px !important;
            max-height: 85vh !important;
        }

        /* Background blur */
        .blur-active {
            filter: blur(6px);
            transition: 0.2s ease-in-out;
        }
    </style>

    @stack('styles')
</head>

<body>

    {{-- NAVBAR --}}
    <nav id="mainNavbar" class="navbar navbar-expand-lg navbar-custom shadow-sm">
        <div class="container-fluid px-4">

            <a class="navbar-brand fw-semibold me-4">E-Hilbah</a>

            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarContent">
                <span class="navbar-toggler-icon text-white"></span>
            </button>

            <div class="collapse navbar-collapse" id="navbarContent">

                {{-- Menu kiri --}}
                <ul class="navbar-nav navbar-left">
                    <li class="nav-item">
                        <a class="nav-link" href="{{ route('dashboard') }}">
                            <i class="bi bi-house-door"></i> Dashboard
                        </a>
                    </li>

                    <li class="nav-item dropdown">
                        <a class="nav-link dropdown-toggle" href="#" id="monitoringDropdown" data-bs-toggle="dropdown">
                            <i class="bi bi-bar-chart"></i> Monitoring & Data
                        </a>
                        <ul class="dropdown-menu" aria-labelledby="monitoringDropdown">
                            <li><a class="dropdown-item" href="#">Daftar Proposal Hibah</a></li>
                            <li><a class="dropdown-item" href="#">Monev Internal</a></li>
                            <li><a class="dropdown-item" href="#">Monitoring Pelaksanaan</a></li>
                            <li><a class="dropdown-item" href="#">Laporan Akhir</a></li>
                            <!-- Tambahkan menu Kalender -->
                            <li><a class="dropdown-item" href="{{ route('monitoring.kalender') }}">Kalender</a></li>
                        </ul>
                    </li>
                </ul>

                {{-- Menu kanan --}}
                <ul class="navbar-nav ms-auto">
                    {{-- NOTIFICATION ICON --}}
                    <li class="nav-item">
                        <a id="notifBell" class="nav-link" style="cursor:pointer;">
                            <i class="bi bi-bell"></i>
                        </a>
                    </li>

                    {{-- USER --}}
                    <li class="nav-item dropdown">
                        <a class="nav-link dropdown-toggle" href="#" data-bs-toggle="dropdown">
                            <i class="bi bi-person-circle"></i> {{ Auth::user()->name ?? 'Pengguna' }}
                        </a>
                        <ul class="dropdown-menu">
                            <li>
                                <a class="dropdown-item" href="{{ route('profile.show') }}">
                                    Profil
                                </a>
                            </li>
                            <li>
                                <form action="{{ route('logout') }}" method="POST">
                                    @csrf
                                    <button class="dropdown-item text-danger" type="submit">Logout</button>
                                </form>
                            </li>
                        </ul>
                    </li>
                </ul>

            </div>
        </div>
    </nav>

    {{-- CONTENT --}}
    <div id="mainContent" class="container content-wrapper mt-3">
        @yield('content')
    </div>

    {{-- NOTIFICATION POPUP --}}
    <div id="notifPopup" class="notif-popup-overlay">
        <div id="notifBox" class="notif-popup">
            <div class="notif-header">
                <h5 class="m-0 fw-bold">Notifikasi</h5>
                <div class="d-flex align-items-center gap-3">
                    <span class="notif-expand"><i class="bi bi-arrows-fullscreen"></i></span>
                    <span id="markRead" class="mark-read">Sudah dibaca semua</span>
                </div>
            </div>
            <div id="notifList" class="notif-list">
                <div class="notif-item">
                    <div class="notif-title">Anda telah berhasil mengajukan Pengumpulan Proposal</div>
                    <div class="notif-time">4 hari yang lalu</div>
                </div>
                <div class="notif-item">
                    <div class="notif-title">Proposal Anda sedang direview</div>
                    <div class="notif-time">2 hari yang lalu</div>
                </div>
                <div class="notif-item">
                    <div class="notif-title">Proposal Anda telah disetujui 🎉</div>
                    <div class="notif-time">1 jam yang lalu</div>
                </div>
            </div>
        </div>
    </div>

    {{-- SCRIPT --}}
    <script>
    document.addEventListener("DOMContentLoaded", () => {
        const notifBell = document.getElementById("notifBell");
        const notifPopup = document.getElementById("notifPopup");
        const notifBox = document.getElementById("notifBox");
        const markRead = document.getElementById("markRead");
        const notifList = document.getElementById("notifList");
        const expandBtn = document.querySelector(".notif-expand");

        const navbar = document.getElementById("mainNavbar");
        const content = document.getElementById("mainContent");

        notifBell.addEventListener("click", () => {
            notifPopup.style.display = "flex";
            navbar.classList.add("blur-active");
            content.classList.add("blur-active");
        });

        notifPopup.addEventListener("click", (e) => {
            if (e.target === notifPopup) {
                notifPopup.style.display = "none";
                navbar.classList.remove("blur-active");
                content.classList.remove("blur-active");
            }
        });

        markRead.addEventListener("click", () => {
            notifList.innerHTML = `<div class="text-center text-secondary py-3">Tidak ada notifikasi.</div>`;
            markRead.textContent = "Tidak ada notifikasi";
            markRead.style.color = "gray";
            markRead.style.cursor = "default";
        });

        expandBtn.addEventListener("click", () => {
            notifBox.classList.toggle("large");
        });
    });
    </script>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    @stack('scripts')
</body>
</html>

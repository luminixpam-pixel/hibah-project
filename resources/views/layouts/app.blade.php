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
        .navbar-custom {
            background-color: #1fbd4c;
        }
        .navbar-custom .nav-link,
        .navbar-custom .navbar-brand {
            color: #fff !important;
        }

        /* Content */
        .content-wrapper {
            padding: 20px;
            backdrop-filter: blur(10px);
            background-color: rgba(255,255,255,0.8);
            border-radius: 15px;
            margin-top: 20px;
        }

        /* -------- NOTIFICATION POPUP -------- */

        /* Overlay tanpa meredup */
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
            z-index: 3000;
        }

        /* Box popup */
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

        .notif-footer .see-all {
            color: #1fbd4c;
            font-weight: 600;
            text-decoration: none;
        }

    </style>

    @stack('styles')
</head>

<body>

    {{-- NAVBAR --}}
    <nav class="navbar navbar-expand-lg navbar-custom shadow-sm">
        <div class="container-fluid px-4">
            <a class="navbar-brand fw-semibold">E-Hilbah</a>

            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
                <span class="navbar-toggler-icon text-white"></span>
            </button>

            <div class="collapse navbar-collapse justify-content-end" id="navbarNav">
                <ul class="navbar-nav">

                    <li class="nav-item">
                        <a class="nav-link" href="{{ route('monitoring.data') }}">
                            <i class="bi bi-bar-chart"></i> Monitoring & Data
                        </a>
                    </li>

                    {{-- 🔔 NOTIFICATION ICON --}}
                    <li class="nav-item">
                        <a id="notifBell" class="nav-link" style="cursor:pointer;">
                            <i class="bi bi-bell fs-4"></i>
                        </a>
                    </li>

                    {{-- USER DROPDOWN --}}
                    <li class="nav-item dropdown">
                        <a class="nav-link dropdown-toggle" href="#" id="userDropdown"
                            role="button" data-bs-toggle="dropdown">
                            <i class="bi bi-person-circle"></i> {{ Auth::user()->name ?? 'Pengguna' }}
                        </a>

                        <ul class="dropdown-menu">
                            <li><a class="dropdown-item">Profil</a></li>

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
    <div class="container content-wrapper">
        @yield('content')
    </div>


    {{-- NOTIFICATION POPUP --}}
    <div id="notifPopup" class="notif-popup-overlay">
        <div class="notif-popup">

            <div class="notif-header">
                <h5 class="m-0 fw-bold">Notifikasi</h5>
                <span class="mark-read">Sudah dibaca semua</span>
            </div>

            <div class="notif-list">
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

            <div class="notif-footer text-center mt-2">
                <a class="see-all">Lihat semua notifikasi</a>
            </div>
        </div>
    </div>


    {{-- JS --}}
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>

    <script>
    document.addEventListener("DOMContentLoaded", () => {

        const notifBell = document.getElementById("notifBell");
        const notifPopup = document.getElementById("notifPopup");

        // Buka popup
        notifBell.addEventListener("click", (e) => {
            e.preventDefault();
            notifPopup.style.display = "flex";
        });

        // Tutup popup kalau klik area luar
        notifPopup.addEventListener("click", (e) => {
            if (e.target === notifPopup) {
                notifPopup.style.display = "none";
            }
        });

    });
    </script>

    @stack('scripts')
</body>
</html>

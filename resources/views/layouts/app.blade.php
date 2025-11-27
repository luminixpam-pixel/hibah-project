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

{{-- FullCalendar --}}
<link href='https://cdn.jsdelivr.net/npm/fullcalendar@6.1.8/main.min.css' rel='stylesheet' />

<style>
body {
    font-family: 'Poppins', sans-serif;
    background: linear-gradient(135deg, #d9fcd4, #f6fef7);
    min-height: 100vh;
}

/* Navbar */
#mainNavbar {
    background-color: #1fbd4c;
    position: relative;
    z-index: 3100;
}
#mainNavbar .nav-link, #mainNavbar .navbar-brand { color: #fff !important; }
#mainNavbar .navbar-left { margin-right: auto; }
#mainNavbar .nav-link { font-size: 1rem; display: flex; align-items: center; gap: 0.5rem; }
#mainNavbar .nav-link i { font-size: 1.3rem; }

/* Content */
#mainContent {
    padding: 20px;
    backdrop-filter: blur(10px);
    background-color: rgba(255,255,255,0.8);
    border-radius: 15px;
    margin-top: 20px;
}

/* Popup Proposal */
.popup-overlay {
    display: none;
    position: fixed;
    top:0; left:0; width:100%; height:100%;
    background: rgba(0,0,0,0.55);
    justify-content: center;
    align-items: center;
    z-index: 3000;
}
.popup-content {
    background:#fff;
    padding:25px;
    border-radius:12px;
    width:480px;
    position:relative;
    box-shadow:0 8px 30px rgba(0,0,0,0.2);
    transform:scale(0.8);
    opacity:0;
    transition:.25s ease;
}
.popup-overlay.active .popup-content { transform: scale(1); opacity:1; }
.close-popup { position:absolute; top:10px; right:15px; font-size:25px; font-weight:bold; cursor:pointer; color:#555; }
.close-popup:hover { color:red; }

/* Notification Popup */
.notif-popup-overlay {
    position: fixed; top:0; left:0; width:100%; height:100%;
    display:none; justify-content:center; align-items:flex-start;
    padding-top:80px;
    background: rgba(0,0,0,0.15);
    z-index: 3050;
}
.notif-popup {
    width:420px; max-height:70vh;
    background:white;
    border-radius:12px;
    padding:20px;
    box-shadow:0 6px 30px rgba(0,0,0,0.18);
    overflow-y:auto;
    animation: fadeIn 0.25s ease-out;
}
.notif-popup.large { width:650px !important; max-height:85vh !important; }
.notif-header { display:flex; justify-content:space-between; align-items:center; margin-bottom:15px; }
.mark-read { color:#1fbd4c; font-size:0.85rem; cursor:pointer; }
.notif-item { margin-bottom:15px; padding-bottom:10px; border-bottom:1px solid #eee; }
.notif-title { font-size:0.95rem; font-weight:600; }
.notif-time { font-size:0.8rem; color:gray; }
.notif-expand { cursor:pointer; font-size:1.1rem; color:#1fbd4c; }
.blur-active { filter: blur(6px); transition: 0.2s ease-in-out; }

/* Kalender */
#calendar { max-width: 100%; margin:0 auto; background:#fff; border-radius:12px; }

@keyframes fadeIn { from {opacity:0; transform:scale(0.94);} to {opacity:1; transform:scale(1);} }
</style>

@stack('styles')

</head>
<body>

{{-- Navbar --}}

<nav id="mainNavbar" class="navbar navbar-expand-lg shadow-sm">
<div class="container-fluid px-4">
    <a class="navbar-brand fw-semibold">E-Hibah</a>
    <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarContent">
        <span class="navbar-toggler-icon text-white"></span>
    </button>

<div class="collapse navbar-collapse" id="navbarContent">
    <ul class="navbar-nav navbar-left">

        {{-- Dashboard --}}
        <li class="nav-item">
            <a class="nav-link" href="{{ route('dashboard') }}">
                <i class="bi bi-house-door"></i> Dashboard
            </a>
        </li>

        {{-- Monitoring & Data --}}
        <li class="nav-item dropdown">
            <a class="nav-link dropdown-toggle" href="#" id="monitoringDropdown" data-bs-toggle="dropdown">
                <i class="bi bi-bar-chart"></i> Monitoring & Data
            </a>
            <ul class="dropdown-menu" aria-labelledby="monitoringDropdown">

                {{-- SEMUA ROLE --}}
                <li><a class="dropdown-item" href="{{ route('monitoring.proposalDikirim') }}">Proposal Dikirim</a></li>
                <li><a class="dropdown-item" href="{{ route('monitoring.proposalDisetujui') }}">Proposal Disetujui</a></li>
                <li><a class="dropdown-item" href="{{ route('monitoring.proposalDitolak') }}">Proposal Ditolak</a></li>

                {{-- ADMIN & REVIEWER --}}
                @if(Auth::user()->role !== 'pengaju')
                    <li><a class="dropdown-item" href="{{ route('monitoring.proposalPerluDireview') }}">Proposal Perlu Direview</a></li>
                    <li><a class="dropdown-item" href="{{ route('monitoring.proposalSedangDireview') }}">Proposal Sedang Direview</a></li>
                @endif

                {{-- SEMUA ROLE --}}
                <li><a class="dropdown-item" href="{{ route('monitoring.reviewSelesai') }}">Review Selesai</a></li>
                <li><a class="dropdown-item" href="{{ route('monitoring.hasilRevisi') }}">Hasil Revisi</a></li>
                <li><a class="dropdown-item" href="{{ route('monitoring.proposalDirevisi') }}">Proposal Direvisi</a></li>

            </ul>
        </li>

        {{-- Kalender DIPINDAH KELUAR --}}
        <li class="nav-item">
            <a class="nav-link" href="{{ route('monitoring.kalender') }}">
                <i class="bi bi-calendar3"></i> Kalender
            </a>
        </li>

    </ul>

    <ul class="navbar-nav ms-auto">
        <li class="nav-item">
            <a id="notifBell" class="nav-link" style="cursor:pointer;"><i class="bi bi-bell"></i></a>
        </li>
        <li class="nav-item dropdown">
            <a class="nav-link dropdown-toggle" href="#" data-bs-toggle="dropdown">
                <i class="bi bi-person-circle"></i> {{ Auth::user()->name ?? 'Pengguna' }}
            </a>
            <ul class="dropdown-menu">
                <li><a class="dropdown-item" href="{{ route('profile.show') }}">Profil</a></li>
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

{{-- Content --}}
<div id="mainContent" class="container content-wrapper mt-3">
    @yield('content')
</div>

{{-- Popup Proposal --}}
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
                <button type="button" class="btn btn-sm btn-primary mt-2" id="addAnggotaBtn">+ Tambah Anggota</button>
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

{{-- Notification Popup --}}
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

{{-- Scripts --}}
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script src='https://cdn.jsdelivr.net/npm/fullcalendar@6.1.8/main.min.js'></script>

<script>
document.addEventListener("DOMContentLoaded", () => {
    const openBtn = document.getElementById("openPopupBtn");
    const closeBtn = document.getElementById("closePopupBtn");
    const popup = document.getElementById("proposalPopup");
    if(openBtn){
        openBtn.addEventListener("click", () => { popup.style.display="flex"; setTimeout(()=>popup.classList.add("active"),10); });
    }
    closeBtn.addEventListener("click", () => { popup.classList.remove("active"); setTimeout(()=>popup.style.display="none",250); });
    popup.addEventListener("click", (e) => { if(e.target===popup){ popup.classList.remove("active"); setTimeout(()=>popup.style.display="none",250); } });

    const anggotaContainer = document.getElementById("anggota-container");
    const addBtn = document.getElementById("addAnggotaBtn");
    if(addBtn){
        addBtn.addEventListener("click", () => {
            const div = document.createElement("div");
            div.classList.add("d-flex","gap-2","mb-2","anggota-row");
            div.innerHTML = `<input type="text" name="anggota[]" class="form-control" placeholder="Masukkan nama anggota">
                             <button type="button" class="btn btn-danger btn-sm remove-anggota">Hapus</button>`;
            anggotaContainer.appendChild(div);
        });
    }
    document.addEventListener("click", (e) => { if(e.target.classList.contains("remove-anggota")) e.target.parentElement.remove(); });

    const notifBell = document.getElementById("notifBell");
    const notifPopup = document.getElementById("notifPopup");
    const notifBox = document.getElementById("notifBox");
    const markRead = document.getElementById("markRead");
    const notifList = document.getElementById("notifList");
    const expandBtn = document.querySelector(".notif-expand");
    const content = document.getElementById("mainContent");

    notifBell.addEventListener("click", () => {
        notifPopup.style.display = "flex";
        content.classList.add("blur-active");
    });
    notifPopup.addEventListener("click", (e) => { if(e.target===notifPopup){ notifPopup.style.display="none"; content.classList.remove("blur-active"); }});
    markRead.addEventListener("click", () => {
        notifList.innerHTML = `<div class="text-center text-secondary py-3">Tidak ada notifikasi.</div>`;
        markRead.textContent = "Tidak ada notifikasi";
        markRead.style.color="gray"; markRead.style.cursor="default";
    });
    expandBtn.addEventListener("click", () => { notifBox.classList.toggle("large"); });

    const calendarEl = document.getElementById('calendar');
    if(calendarEl){
        const calendar = new FullCalendar.Calendar(calendarEl, {
            initialView:'dayGridMonth',
            headerToolbar:{ left:'prev,next today', center:'title', right:'dayGridMonth,timeGridWeek,listWeek' },
            navLinks:true,
            editable:false,
            selectable:true,
            dayMaxEvents:true,
            events:[
                @foreach(\App\Models\Proposal::all() as $proposal)
                {
                    title:'{{ $proposal->judul }}',
                    start:'{{ $proposal->created_at->format("Y-m-d") }}',
                    url:'{{ route("proposal.download",$proposal->id) }}',
                    color:'{{ $proposal->status=="Disetujui"?"green":($proposal->status=="Ditolak"?"red":"blue") }}'
                },
                @endforeach
            ],
            eventClick:function(info){ info.jsEvent.preventDefault(); if(info.event.url) window.open(info.event.url,"_blank"); }
        });
        calendar.render();
    }
});
</script>

@stack('scripts')

</body>
</html>

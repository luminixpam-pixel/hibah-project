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
#mainNavbar { background-color: #1fbd4c; position: relative; z-index: 3100; }
#mainNavbar .nav-link, #mainNavbar .navbar-brand { color: #fff !important; }
#mainNavbar .navbar-left { margin-right: auto; }
#mainNavbar .nav-link { font-size: 1rem; display: flex; align-items: center; gap: 0.5rem; }
#mainNavbar .nav-link i { font-size: 1.3rem; }

/* ====== NAVBAR ACTIVE STATE (indikator halaman aktif) ====== */
#mainNavbar .nav-link.main-nav-link {
    border-radius: 999px;
    padding: 6px 14px;
    font-weight: 500;
    transition: background .15s ease, box-shadow .15s ease, transform .12s ease;
}

#mainNavbar .nav-link.main-nav-link:hover {
    background: rgba(255,255,255,0.18);
    transform: translateY(-1px);
}

#mainNavbar .nav-link.main-nav-link-active {
    background: rgba(15,23,42,0.25);
    box-shadow: 0 0 0 2px rgba(255,255,255,0.45);
}

#mainNavbar .nav-link.main-nav-link-active:hover {
    background: rgba(15,23,42,0.32);
}

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
    top:0; left:0;
    width: 100%; height: 100%;
    background: rgba(0,0,0,0.5);
    z-index: 5000;
    justify-content: center;
    align-items: center;
}
.popup-overlay.active { display: flex; }

.popup-content {
    background: #fff;
    padding: 20px;
    border-radius: 12px;
    max-width: 500px;
    width: 90%;
    box-shadow: 0 5px 15px rgba(0,0,0,0.3);
    position: relative;
}

.close-popup {
    position: absolute;
    top: 10px; right: 10px;
    font-size: 1.5rem;
    cursor: pointer;
}

/* Notifikasi */
.notif-popup-overlay {
    display:none; position:fixed; top:0; left:0; width:100%; height:100%;
    background:rgba(0,0,0,0.3); justify-content:center; align-items:flex-start;
    z-index:4000; padding-top:60px;
}
.notif-popup {
    background:#fff; border-radius:12px; width:350px; max-height:80vh;
    overflow:hidden; display:flex; flex-direction:column; box-shadow:0 5px 15px rgba(0,0,0,0.3);
    transition: all 0.3s ease;
}
.notif-popup.large { width:600px; max-height:90vh; }
.notif-header { padding:10px 15px; border-bottom:1px solid #ddd; display:flex; justify-content:space-between; align-items:center; }
.notif-list { overflow-y:auto; flex:1; }
.notif-item { padding:10px 15px; border-bottom:1px solid #eee; }
.notif-item.read { background:#f8f8f8; color:#999; }
.mark-read { cursor:pointer; color:#0d6efd; font-size:0.9rem; }
.mark-read.disabled { color:gray; cursor:default; }

/* Kalender */
#calendar { max-width: 100%; margin:0 auto; background:#fff; border-radius:12px; }

@keyframes fadeIn { from {opacity:0; transform:scale(0.94);} to {opacity:1; transform:scale(1);} }
</style>

@stack('styles')
</head>
<body>
    {{-- helper route untuk tandai menu aktif --}}
    @php
        $routeName = \Illuminate\Support\Facades\Route::currentRouteName();

        // Monitoring & Data dianggap aktif hanya untuk route monitoring.* selain kalender
        $isMonitoringMenuActive = in_array($routeName, [
            'monitoring.proposalDikirim',
            'monitoring.proposalPerluDireview',
            'monitoring.proposalSedangDireview',
            'monitoring.reviewSelesai',
            'monitoring.proposalDisetujui',
            'monitoring.proposalDitolak',
            'monitoring.proposalDirevisi',
            'monitoring.hasilRevisi',
        ]);
    @endphp

    <!-- Alert placeholder -->
    <div id="alertPlaceholder" class="position-fixed top-0 end-0 p-3" style="z-index: 9999;"></div>

    {{-- Navbar --}}
    <nav id="mainNavbar" class="navbar navbar-expand-lg shadow-sm">
        <div class="container-fluid px-4">
            <a class="navbar-brand fw-semibold">E-Hibah</a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarContent">
                <span class="navbar-toggler-icon text-white"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarContent">
                <ul class="navbar-nav navbar-left">
                    <li class="nav-item">
                        <a class="nav-link main-nav-link {{ $routeName === 'dashboard' ? 'main-nav-link-active' : '' }}"
                           href="{{ route('dashboard') }}">
                            <i class="bi bi-house-door"></i> Dashboard
                        </a>
                    </li>
                    <li class="nav-item dropdown">
                        <a class="nav-link dropdown-toggle main-nav-link {{ $isMonitoringMenuActive ? 'main-nav-link-active' : '' }}"
                           href="#"
                           id="monitoringDropdown"
                           data-bs-toggle="dropdown">
                            <i class="bi bi-bar-chart"></i> Monitoring & Data
                        </a>
                        <ul class="dropdown-menu" aria-labelledby="monitoringDropdown">
                            <li><a class="dropdown-item" href="{{ route('monitoring.proposalDikirim') }}">Daftar Proposal</a></li>
                            @if(Auth::user()->role !== 'pengaju')
                                <li><a class="dropdown-item" href="{{ route('monitoring.proposalPerluDireview') }}">Proposal Perlu Direview</a></li>
                                <li><a class="dropdown-item" href="{{ route('monitoring.proposalSedangDireview') }}">Proposal Sedang Direview</a></li>
                            @endif
                            <li><a class="dropdown-item" href="{{ route('monitoring.reviewSelesai') }}">Review Selesai</a></li>
                            <li><a class="dropdown-item" href="{{ route('monitoring.proposalDisetujui') }}">Proposal Disetujui</a></li>
                            <li><a class="dropdown-item" href="{{ route('monitoring.proposalDitolak') }}">Proposal Ditolak</a></li>
                            <li><a class="dropdown-item" href="{{ route('monitoring.proposalDirevisi') }}">Proposal Direvisi</a></li>
                            <li><a class="dropdown-item" href="{{ route('monitoring.hasilRevisi') }}">Hasil Revisi</a></li>
                        </ul>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link main-nav-link {{ $routeName === 'monitoring.kalender' ? 'main-nav-link-active' : '' }}"
                           href="{{ route('monitoring.kalender') }}">
                            <i class="bi bi-calendar3"></i> Kalender
                        </a>
                    </li>
                </ul>
                <ul class="navbar-nav ms-auto">
                    <li class="nav-item">
                        <a id="notifBell" class="nav-link" style="cursor:pointer;">
                            <i class="bi bi-bell"></i>
                        </a>
                    </li>
                    <li class="nav-item dropdown">
                        <a class="nav-link dropdown-toggle" href="#" data-bs-toggle="dropdown">
                            <i class="bi bi-person-circle"></i> {{ Auth::user()->name ?? 'Pengguna' }}
                        </a>
                        <ul class="dropdown-menu">
                            <li><a class="dropdown-item" href="{{ route('profile.show') }}">Profil</a></li>
                            <li>
                                <form action="{{ route('logout') }}" method="POST">@csrf
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
                <div class="text-center text-secondary py-3">Memuat notifikasi...</div>
            </div>
        </div>
    </div>

    {{-- Scripts --}}
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src='https://cdn.jsdelivr.net/npm/fullcalendar@6.1.8/main.min.js'></script>

    <script>
    document.addEventListener("DOMContentLoaded", () => {
        const popup = document.getElementById("proposalPopup");
        const closeBtn = document.getElementById("closePopupBtn");
        const addBtn = document.getElementById("addAnggotaBtn");
        const anggotaContainer = document.getElementById("anggota-container");
        const notifBell = document.getElementById("notifBell");
        const notifPopup = document.getElementById("notifPopup");
        const content = document.getElementById("mainContent");
        const markRead = document.getElementById("markRead");
        const expandBtn = document.querySelector(".notif-expand");
        const notifList = document.getElementById("notifList");

        // Flash message alert
        const alertPlaceholder = document.getElementById('alertPlaceholder');
        const success = @json(session('success'));
        const error   = @json(session('error'));
        function showAlert(message, type='success'){
            const wrapper = document.createElement('div');
            wrapper.innerHTML = `<div class="alert alert-${type} alert-dismissible fade show" role="alert">${message}<button type="button" class="btn-close" data-bs-dismiss="alert"></button></div>`;
            alertPlaceholder.appendChild(wrapper);
            setTimeout(() => {
                const alert = bootstrap.Alert.getOrCreateInstance(wrapper.querySelector('.alert'));
                alert.close();
            },5000);
        }
        if(success) showAlert(success,'success');
        if(error) showAlert(error,'danger');

        // Popup Proposal
        closeBtn.addEventListener("click", ()=>{ popup.classList.remove("active"); setTimeout(()=>popup.style.display="none",250); });
        popup.addEventListener("click", e => { if(e.target===popup){ popup.classList.remove("active"); setTimeout(()=>popup.style.display="none",250); }});
        addBtn.addEventListener("click", () => {
            const div = document.createElement("div");
            div.classList.add("d-flex","gap-2","mb-2","anggota-row");
            div.innerHTML = `<input type="text" name="anggota[]" class="form-control" placeholder="Masukkan nama anggota">
                             <button type="button" class="btn btn-danger btn-sm remove-anggota">Hapus</button>`;
            anggotaContainer.appendChild(div);
        });
        document.addEventListener("click", e => { if(e.target.classList.contains("remove-anggota")) e.target.parentElement.remove(); });

        // Notification
        async function loadNotifications(){
            try{
                const res = await fetch("{{ route('notifications.fetch') }}");
                const data = await res.json();
                notifList.innerHTML = '';
                if(data.length === 0){
                    notifList.innerHTML = `<div class="text-center text-secondary py-3">Tidak ada notifikasi.</div>`;
                    return;
                }
                data.forEach(notif => {
                    const item = document.createElement("div");
                    item.classList.add("notif-item");
                    if(notif.read) item.classList.add("read");
                    item.innerHTML = `<div class="notif-title">${notif.title}</div>
                                      <div class="notif-time">${new Date(notif.created_at).toLocaleString()}</div>`;
                    notifList.appendChild(item);
                });
            }catch(err){ console.error(err); }
        }

        notifBell.addEventListener("click", () => { notifPopup.style.display="flex"; content.classList.add("blur-active"); loadNotifications(); });
        notifPopup.addEventListener("click", e => { if(e.target===notifPopup){ notifPopup.style.display="none"; content.classList.remove("blur-active"); } });
        markRead.addEventListener("click", () => { notifList.innerHTML=`<div class="text-center text-secondary py-3">Tidak ada notifikasi.</div>`; markRead.textContent="Tidak ada notifikasi"; markRead.style.color="gray"; markRead.style.cursor="default"; });
        expandBtn.addEventListener("click", ()=>{ document.getElementById("notifBox").classList.toggle("large"); });

        setInterval(loadNotifications,10000);

        // FullCalendar
        const calendarEl = document.getElementById('calendar');
        if(calendarEl){
            const calendar = new FullCalendar.Calendar(calendarEl,{
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

        // Load notif pertama kali
        loadNotifications();
    });
    </script>

    @stack('scripts')
</body>
</html>

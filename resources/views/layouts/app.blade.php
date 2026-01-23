<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    {{-- ✅ TAMBAH biar POST fetch aman --}}
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>@yield('title', 'E-Hibah')</title>

    {{-- Bootstrap & Icons --}}
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css" rel="stylesheet">

    {{-- Font --}}
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600&display=swap" rel="stylesheet">

    {{-- FullCalendar --}}
    <link href='https://cdn.jsdelivr.net/npm/fullcalendar@6.1.8/main.min.css' rel='stylesheet' />

    <style>
        body { font-family:'Poppins',sans-serif; background: linear-gradient(135deg,#d9fcd4,#f6fef7); min-height:100vh; }
        /* Navbar */
        #mainNavbar{background-color:#1fbd4c; z-index:3100;}
        #mainNavbar .nav-link,#mainNavbar .navbar-brand{color:#fff !important;}
        #mainNavbar .nav-link i{font-size:1.3rem;}
        #mainNavbar .nav-link.main-nav-link{border-radius:999px;padding:6px 14px;font-weight:500;transition:.15s;}
        #mainNavbar .nav-link.main-nav-link:hover{background:rgba(255,255,255,0.18);}
        #mainNavbar .nav-link.main-nav-link-active{background:rgba(15,23,42,0.25);box-shadow:0 0 0 2px rgba(255,255,255,.45);}
        #mainNavbar .nav-link.main-nav-link-active:hover{background:rgba(15,23,42,0.32);}

        /* Content */
        #mainContent{padding:20px; backdrop-filter: blur(10px); background-color: rgba(255,255,255,0.8); border-radius:15px; margin-top:20px;}

        /* Popup Proposal */
        .popup-overlay{display:none;position:fixed;top:0;left:0;width:100%;height:100%;background:rgba(0,0,0,0.5); z-index:5000; justify-content:center; align-items:center;}
        .popup-overlay.active{display:flex;}
        .popup-content{background:#fff;padding:20px;border-radius:12px; max-width:500px;width:90%; box-shadow:0 5px 15px rgba(0,0,0,0.3); position:relative;}
        .close-popup{position:absolute;top:10px;right:10px;font-size:1.5rem;cursor:pointer;}

        /* Notifikasi */
        .notif-popup-overlay{display:none;position:fixed;top:0;left:0;width:100%;height:100%;background:rgba(0,0,0,0.3);justify-content:center;align-items:flex-start;z-index:4000;padding-top:60px;}
        .notif-popup{background:#fff;border-radius:12px;width:350px;max-height:80vh;overflow:hidden;display:flex;flex-direction:column;box-shadow:0 5px 15px rgba(0,0,0,0.3);}
        .notif-popup.large{width:600px;max-height:90vh;}
        .notif-header{padding:10px 15px;border-bottom:1px solid #ddd; display:flex; justify-content:space-between; align-items:center;}
        .notif-list{overflow-y:auto; flex:1;}
        .notif-item{padding:10px 15px;border-bottom:1px solid #eee;}

        .notif-item.unread{ background:#eef7ff; border-left:4px solid #0d6efd; }
        .notif-item.unread .notif-title{ font-weight:700; color:#111827; }
        .notif-item.read{ background:#f8f8f8; color:#999; }
        .notif-item.read .notif-title{ font-weight:500; }

        .mark-read{cursor:pointer;color:#0d6efd;font-size:0.9rem;}
        .mark-read.disabled{color:gray; cursor:default;}

        /* Kalender */
        #calendar{max-width:100%;margin:0 auto;background:#fff;border-radius:12px;}

        /* Toast file error */
        #toastFileError{position:fixed;top:70px;right:20px;z-index:99999; display:none;}
    </style>

    @stack('styles')
</head>
<body>

@php
    $routeName = \Illuminate\Support\Facades\Route::currentRouteName();
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

    $isReviewerMenuActive = ($routeName === 'admin.reviewer.index');
    $isRiwayatMenuActive = ($routeName === 'admin.riwayatDosen' || $routeName === 'admin.dosen.detail');
@endphp

{{-- Alert placeholder --}}
<div id="alertPlaceholder" class="position-fixed top-0 end-0 p-3" style="z-index:9999;"></div>

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
                    <a class="nav-link main-nav-link {{ $routeName==='dashboard'?'main-nav-link-active':'' }}" href="{{ route('dashboard') }}">
                        <i class="bi bi-house-door"></i> Dashboard
                    </a>
                </li>

               {{--UNGGAH--}}
                <li class="nav-item dropdown">
                    <a class="nav-link dropdown-toggle" href="#" data-bs-toggle="dropdown">
                        <i class="bi bi-upload"></i> Unggah
                    </a>
                   <ul class="dropdown-menu dropdown-menu-end">
                        @if(in_array(Auth::user()->role, ['pengaju', 'reviewer']))
                            <li><a class="dropdown-item" href="#" id="openPopupBtn">Unggah Proposal</a></li>
                            <li><a class="dropdown-item" href="{{ route('laporan.kemajuan.index') }}">Unggah Laporan Kemajuan</a></li>
                            {{-- Menu Baru untuk Pengguna --}}
                            <li><a class="dropdown-item" href="{{ route('laporan.akhir.index') }}">Unggah Laporan Akhir</a></li>
                        @endif

                        @if(Auth::user()->role === 'admin')
                            {{-- ✅ Admin: Monitoring Laporan --}}
                            <li><a class="dropdown-item" href="{{ route('laporan.kemajuan.index') }}">Monitoring Laporan Kemajuan</a></li>
                            {{-- Menu Baru untuk Admin --}}
                            <li><a class="dropdown-item" href="{{ route('laporan.akhir.index') }}">Monitoring Laporan Akhir</a></li>

                            <hr class="dropdown-divider">
                            <li><a class="dropdown-item" href="{{ route('admin.dokumen.index') }}">Upload Dokumen/Template Dokumen</a></li>
                        @endif
                    </ul>
                </li>

                <li class="nav-item dropdown">
                    <a class="nav-link dropdown-toggle main-nav-link {{ $isMonitoringMenuActive?'main-nav-link-active':'' }}" href="#" data-bs-toggle="dropdown">
                        <i class="bi bi-bar-chart"></i> Monitoring & Data
                    </a>
                    <ul class="dropdown-menu">
                        <li><a class="dropdown-item" href="{{ route('proposal.index') }}">Daftar Monitoring Proposal</a></li>
                        @if(Auth::user()->role!=='pengaju')
                            <li><a class="dropdown-item" href="{{ route('monitoring.proposalPerluDireview') }}">Daftar Review Proposal</a></li>
                            <li><a class="dropdown-item" href="{{ route('monitoring.proposalSedangDireview') }}">Daftar Proposal Sedang Direview</a></li>
                        @endif
                        <li><a class="dropdown-item" href="{{ route('monitoring.reviewSelesai') }}">Daftar Review Selesai</a></li>
                        <li><a class="dropdown-item" href="{{ route('monitoring.proposalDisetujui') }}">Daftar Proposal Disetujui</a></li>
                        <li><a class="dropdown-item" href="{{ route('monitoring.proposalDitolak') }}">Daftar Proposal Ditolak</a></li>
                        <li><a class="dropdown-item" href="{{ route('monitoring.proposalDirevisi') }}">Daftar Proposal Direvisi</a></li>
                        <li><a class="dropdown-item" href="{{ route('monitoring.hasilRevisi') }}">Hasil Revisi Proposal</a></li>
                    </ul>
                </li>

                <li class="nav-item">
                    <a class="nav-link main-nav-link {{ $routeName==='monitoring.kalender'?'main-nav-link-active':'' }}" href="{{ route('monitoring.kalender') }}">
                        <i class="bi bi-calendar3"></i> Kalender
                    </a>
                </li>

                @if(Auth::user()->role === 'admin')
                <li class="nav-item">
                    <a class="nav-link main-nav-link {{ $isReviewerMenuActive ? 'main-nav-link-active' : '' }}" href="{{ route('admin.reviewer.index') }}">
                        <i class="bi bi-person-check"></i> Pilih Reviewer
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link main-nav-link {{ $isRiwayatMenuActive ? 'main-nav-link-active' : '' }}" href="{{ route('admin.riwayatDosen') }}">
                        <i class="bi bi-person-vcard"></i> Riwayat Dosen
                    </a>
                </li>
                @endif
            </ul>

            <ul class="navbar-nav ms-auto">
                <li class="nav-item">
                    {{-- ✅ Bell + Badge (DIPEPETIN) --}}
                    <a id="notifBell" class="nav-link position-relative" style="cursor:pointer;">
                        <i class="bi bi-bell"></i>

                        <span id="notifCount"
                              class="position-absolute badge rounded-pill bg-danger"
                              style="display:none;font-size:10px;padding:4px 6px;top:2px;right:2px;">
                            0
                        </span>
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
@if(Auth::check() && in_array(Auth::user()->role, ['pengaju', 'reviewer']))
<div id="proposalPopup" class="popup-overlay">
    <div class="popup-content">
        <span class="close-popup" id="closePopupBtn">&times;</span>
        <h5 class="fw-bold mb-3">Form Pengajuan Proposal</h5>

        {{-- HANYA SATU TAG FORM DI SINI --}}
        <form action="{{ route('proposal.store') }}" method="POST" enctype="multipart/form-data" id="mainProposalForm">
            @csrf

            <div class="mb-3">
                <label class="form-label small fw-bold">Nama Ketua</label>
                <input type="text" name="nama_ketua" class="form-control" list="dosenList" required>
            </div>

            <div class="mb-3">
    <label class="form-label small fw-bold">Anggota Peneliti</label>
    <div id="anggota-container">
        <div class="input-group mb-2">
            <input type="text" name="anggota[]" class="form-control" list="dosenList" placeholder="Ketik Nama Anggota...">
            <button type="button" class="btn btn-outline-secondary disabled" style="width: 45px; opacity: 0;">
                <i class="bi bi-trash"></i>
            </button>
        </div>
    </div>

    <button type="button" class="btn btn-sm btn-outline-primary" id="addAnggotaBtn">
        <i class="bi bi-plus"></i> Tambah Anggota
    </button>
</div>

            <div class="mb-3">
                <label class="form-label small fw-bold">Fakultas / Prodi</label>
               <select name="fakultas_prodi" class="form-select" required>
                    <option value="" selected disabled>-- Pilih Fakultas --</option>
                    @isset($list_fakultas)
                        @foreach($list_fakultas as $fakultas)
                            {{-- Ganti $fakultas->nama menjadi $fakultas->nama_fakultas --}}
                            <option value="{{ $fakultas->id }}">{{ $fakultas->nama_fakultas }}</option>
                        @endforeach
                    @endisset
                </select>
            </div>

            <div class="mb-3">
                <label class="form-label small fw-bold text-secondary">Estimasi Biaya</label>
                <div class="input-group shadow-sm">
                    <span class="input-group-text bg-light fw-bold text-success">Rp</span>
                    <input type="text" id="biaya_display" class="form-control" placeholder="0" required>
                    <input type="hidden" name="biaya" id="biaya_hidden">
                </div>
            </div>

            <div class="mb-3">
                <label class="form-label small fw-bold">Judul Proposal</label>
                <input type="text" name="judul" class="form-control" required>
            </div>

            <div class="mb-3">
                <label class="form-label small fw-bold">File Proposal (PDF/DOCX)</label>
                <input type="file" name="file" class="form-control" accept=".pdf,.doc,.docx" required>
            </div>

            <button class="btn btn-success w-100 fw-bold py-2" type="submit" id="submitBtn">Kirim Proposal</button>
        </form>
    </div>
</div>
@endif
<datalist id="dosenList">
    @isset($all_dosen)
        @foreach($all_dosen as $dosen)
            <option value="{{ $dosen->name }}">
        @endforeach
    @endisset
</datalist>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // 1. LOGIKA FORMAT RUPIAH (HANYA SATU SCRIPT)
 // Gunakan versi ini saja untuk Biaya:
const biayaDisplay = document.getElementById('biaya_display');
const biayaHidden = document.getElementById('biaya_hidden');

if (biayaDisplay) {
    biayaDisplay.addEventListener('input', function(e) {
        let value = this.value.replace(/[^0-9]/g, '');
        if (biayaHidden) biayaHidden.value = value;

        if (value) {
            const formatted = new Intl.NumberFormat('id-ID').format(value);
            this.value = 'Rp ' + formatted;
        } else {
            this.value = '';
        }
    });
}

    // 2. LOGIKA TAMBAH/HAPUS ANGGOTA
    const addBtn = document.getElementById('addAnggotaBtn');
    const container = document.getElementById('anggota-container');

        if (addBtn && container) {
            addBtn.addEventListener('click', function() {
                const count = container.querySelectorAll('input').length + 1;
                const div = document.createElement('div');
                div.className = 'd-flex gap-2 mb-2';
                div.innerHTML = `
                    <input type="text" name="anggota[]" class="form-control"
                        list="dosenList" placeholder="Nama Anggota ${count}">
                    <button type="button" class="btn btn-danger btn-sm remove-anggota">
                        <i class="bi bi-trash"></i>
                    </button>
                `;
                container.appendChild(div);
            });
        }
});

// Script untuk mendeteksi pilihan dari datalist
document.querySelector('input[name="nama_ketua"]').addEventListener('input', function(e) {
    const val = e.target.value;
    const options = document.getElementById('dosenList').childNodes;

    for (let i = 0; i < options.length; i++) {
        if (options[i].value === val) {
            console.log("Dosen terpilih: " + val);
            // Anda bisa menambahkan logika di sini untuk auto-fill
            // jika datalist menyimpan atribut tambahan seperti NIDN
            break;
        }
    }
});
</script>


<script>
    const biayaDisplay = document.getElementById('biaya_display');
    const biayaHidden = document.getElementById('biaya_hidden');

    biayaDisplay.addEventListener('keyup', function(e) {
        // Ambil value dan hapus semua karakter selain angka
        let numberString = this.value.replace(/[^,\d]/g, '').toString();
        let split = numberString.split(',');
        let sisa = split[0].length % 3;
        let rupiah = split[0].substr(0, sisa);
        let ribuan = split[0].substr(sisa).match(/\d{3}/gi);

        if (ribuan) {
            let separator = sisa ? '.' : '';
            rupiah += separator + ribuan.join('.');
        }

        rupiah = split[1] != undefined ? rupiah + ',' + split[1] : rupiah;

        // Tampilkan format rupiah di input display
        this.value = 'Rp ' + rupiah;

        // Simpan angka murni ke input hidden untuk dikirim ke Controller
        biayaHidden.value = numberString.replace(/\./g, '');

    });
</script>

{{-- Notifikasi --}}
<div id="notifPopup" class="notif-popup-overlay">
    <div id="notifBox" class="notif-popup">
        <div class="notif-header">
            <h5 class="m-0 fw-bold">Notifikasi</h5>
            <div class="d-flex align-items-center gap-3">
                <span class="notif-expand" style="cursor:pointer;"><i class="bi bi-arrows-fullscreen"></i></span>
                <span id="markRead" class="mark-read">Sudah dibaca semua</span>
            </div>
        </div>
        <div id="notifList" class="notif-list">
            <div class="text-center text-secondary py-3">Memuat notifikasi...</div>
        </div>
    </div>
</div>



{{-- Toast File Error --}}
<div id="toastFileError" class="toast align-items-center text-white bg-danger border-0 position-fixed" style="top:70px;right:20px;z-index:99999;" role="alert">
    <div class="d-flex">
        <div class="toast-body">Format file tidak valid! Hanya PDF/DOC/DOCX yang diperbolehkan.</div>
    </div>
</div>

{{-- Scripts --}}
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script src='https://cdn.jsdelivr.net/npm/fullcalendar@6.1.8/main.min.js'></script>

<script>
document.addEventListener("DOMContentLoaded",()=>{

    // FLASH MESSAGE
    const alertPlaceholder=document.getElementById('alertPlaceholder');
    const success=@json(session('success'));
    const error=@json(session('error'));
    function showAlert(message,type='success'){
        const wrapper=document.createElement('div');
        wrapper.innerHTML=`<div class="alert alert-${type} alert-dismissible fade show" role="alert">${message}<button type="button" class="btn-close" data-bs-dismiss="alert"></button></div>`;
        alertPlaceholder.appendChild(wrapper);
        setTimeout(()=>{const alert=bootstrap.Alert.getOrCreateInstance(wrapper.querySelector('.alert'));alert.close();},5000);
    }
    if(success) showAlert(success,'success');
    if(error) showAlert(error,'danger');

    // POPUP PROPOSAL
    const popup=document.getElementById("proposalPopup");
    const openBtn = document.getElementById("openPopupBtn");
    const closeBtn=document.getElementById("closePopupBtn");
    if(openBtn && popup){
        openBtn.addEventListener("click",(e)=>{
            e.preventDefault();
            popup.classList.add("active");
        });
    }
    if(closeBtn && popup) closeBtn.addEventListener("click",()=>popup.classList.remove("active"));

   // ✅ LOGIKA TAMBAH/HAPUS ANGGOTA (VERSI KONSISTEN)
        const addBtn = document.getElementById("addAnggotaBtn");
        const anggotaContainer = document.getElementById("anggota-container");

        if (addBtn && anggotaContainer) {
            addBtn.onclick = function() { // Menggunakan .onclick memastikan hanya ada 1 event
                const div = document.createElement("div");
                div.className = "input-group mb-2";

                div.innerHTML = `
                    <input type="text" name="anggota[]" class="form-control" list="dosenList" placeholder="Nama anggota">
                    <button type="button" class="btn btn-danger remove-anggota" style="width: 45px;">
                        <i class="bi bi-trash"></i>
                    </button>
                `;
                anggotaContainer.appendChild(div);
            };
        }

// Delegasi hapus yang benar
document.addEventListener("click", function(e) {
    if (e.target.closest(".remove-anggota")) {
        e.target.closest(".input-group").remove();
    }
});

// Delegasi klik untuk tombol hapus
document.addEventListener("click", e => {
    // Cari element terdekat yang punya class remove-anggota (biar ikon di dlm button jg bisa diklik)
    const btn = e.target.closest(".remove-anggota");
    if (btn) {
        btn.closest(".input-group").remove();
    }
});

    // =========================
    // NOTIFIKASI (BELL + COUNT)
    // =========================
    const notifBell=document.getElementById("notifBell");
    const notifPopup=document.getElementById("notifPopup");
    const notifList=document.getElementById("notifList");
    const notifCount=document.getElementById("notifCount");
    const markReadBtn=document.getElementById("markRead");

    const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');

    const setBadge = (count) => {
        if (!notifCount) return;
        if (!count || count <= 0) {
            notifCount.style.display = "none";
            notifCount.textContent = "0";
            return;
        }
        notifCount.style.display = "inline-block";
        notifCount.textContent = count > 99 ? "99+" : String(count);
    };

    const updateNotifCount = async () => {
        try {
            const res = await fetch("{{ route('notifications.count') }}", {
                cache: "no-store",
                credentials: "include",
                headers: { "Accept": "application/json" }
            });
            const data = await res.json();
            setBadge(parseInt(data.count || 0));
        } catch(e) {}
    };

    const loadNotifications = async () => {
        try {
            const res = await fetch("{{ route('notifications.fetch') }}", {
                cache: "no-store",
                credentials: "include",
                headers: { "Accept": "application/json" }
            });
            const data = await res.json();

            notifList.innerHTML = data.length ? '' : '<div class="text-center py-3">Tidak ada notifikasi.</div>';

            data.forEach(notif => {
                const item = document.createElement("div");
                item.className = `notif-item ${notif.is_read ? 'read' : 'unread'}`;
                item.innerHTML = `<div class="notif-title">${notif.title}</div><div class="small text-muted">${notif.message || ''}</div>`;
                notifList.appendChild(item);
            });

            updateNotifCount();
        } catch(e) {}
    };

    updateNotifCount();
    setInterval(updateNotifCount, 30000);

    if(notifBell) notifBell.addEventListener("click", () => {
        notifPopup.style.display="flex";
        loadNotifications();
    });

    if(notifPopup) notifPopup.addEventListener("click", e => {
        if(e.target===notifPopup) notifPopup.style.display="none";
    });

    if(markReadBtn){
        markReadBtn.addEventListener("click", async () => {
            try {
                await fetch("{{ route('notifications.markAllAsRead') }}", {
                    method: "POST",
                    credentials: "include",
                    headers: {
                        "Content-Type": "application/json",
                        "Accept": "application/json",
                        "X-CSRF-TOKEN": csrfToken
                    },
                    body: JSON.stringify({})
                });

                loadNotifications();
                updateNotifCount();
            } catch(e) {}
        });
    }

    // ==========================================
    // ✅ NOTIF TENGGAT MUNCUL SAAT LOGIN
    // ==========================================
    const showDeadlinePopup = (title, message) => {
        if (!alertPlaceholder) return;

        const wrapper = document.createElement('div');
        wrapper.innerHTML = `
            <div class="alert alert-warning alert-dismissible fade show shadow-sm" role="alert" style="min-width:320px;">
                <div class="fw-bold">${title}</div>
                <div class="small">${message || ''}</div>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        `;
        alertPlaceholder.appendChild(wrapper);

        setTimeout(() => {
            try {
                const alert = bootstrap.Alert.getOrCreateInstance(wrapper.querySelector('.alert'));
                alert.close();
            } catch(e) {}
        }, 8000);
    };

    const runDeadlineCheckOnce = async () => {
        try {
            if (sessionStorage.getItem('deadline_check_done') === '1') return;
            sessionStorage.setItem('deadline_check_done', '1');

            const res = await fetch("{{ route('notifications.deadlineCheck') }}", {
                cache: "no-store",
                credentials: "include",
                headers: { "Accept": "application/json" }
            });

            const data = await res.json();
            const popups = data.popups || [];

            if (popups.length > 0) {
                popups.forEach(p => showDeadlinePopup(p.title, p.message));
                updateNotifCount();
            }
        } catch(e) {}
    };

    runDeadlineCheckOnce();

    // CALENDAR
    const calendarEl=document.getElementById('calendar');
    if(calendarEl){
        new FullCalendar.Calendar(calendarEl,{
            initialView:'dayGridMonth',
            events: [
                @foreach(\App\Models\Proposal::all() as $p)
                { title:'{{ $p->judul }}', start:'{{ $p->created_at->format("Y-m-d") }}', color:'{{ $p->status=="Disetujui"?"green":"blue" }}' },
                @endforeach
            ]
        }).render();
    }
});
</script>

@stack('scripts')
</body>
</html>

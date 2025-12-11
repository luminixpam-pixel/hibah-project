@extends('layouts.app')

@section('content')

@php
    $isAdmin = auth()->check() && auth()->user()->role === 'admin';

    // ambil periode dari DB (kalau ada)
    $periodeStart = $hibahPeriod?->start_date?->format('Y-m-d');
    $periodeEnd   = $hibahPeriod?->end_date?->format('Y-m-d');
@endphp

<div class="container py-4">

    <h3 class="fw-bold mb-4">KALENDER</h3>

    {{-- Notifikasi sukses simpan periode --}}
    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            {{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    <!-- TIMELINE -->
    <div class="card-custom mb-4">
        <h5 class="fw-bold mb-3">Kalender Lemlit</h5>

        <div class="row">
            <div class="col-md-6">
                <p><span>Februari – April</span> : Penerimaan Proposal</p>
                <p><span>Mei</span> : Pengumpulan Proposal ke Universitas</p>
                <p><span>Juni – Juli</span> : Review &amp; Perbaikan</p>
                <p><span>Juli</span> : Pengumuman Proposal Didanai</p>
                <p><span>Agustus</span> : Tanda Tangan Kontrak</p>
            </div>
            <div class="col-md-6">
                <p><span>Agustus</span> : Pencairan Dana Penelitian</p>
                <p><span>Agustus – Juli</span> : Pelaksanaan Penelitian</p>
                <p><span>Maret</span> : Monitoring Evaluasi</p>
                <p><span>Juli</span> : Laporan Akhir</p>
                <p><span>Agustus – Desember</span> : Publikasi &amp; Seminar</p>
            </div>
        </div>
    </div>

    <!-- "FILTER" TAPI DIISI OTOMATIS DARI KALENDER + PENGATUR PERIODE -->
    <div class="filter-box d-flex justify-content-between mb-4 flex-wrap gap-3">

        <div class="w-25 min-w-200">
            <label class="fw-semibold">Tahun Usulan</label>
            <input id="tahun-usulan" class="form-control" type="text" readonly>
        </div>

        <div class="w-25 min-w-200">
            <label class="fw-semibold">Tahun Ajaran Pelaksanaan</label>
            <input id="tahun-ajaran" class="form-control" type="text" readonly>
        </div>

        {{-- HANYA ADMIN BOLEH ATUR PERIODE PENGAJUAN HIBAH --}}
        @if($isAdmin)
            <div class="flex-grow-1">
                <form id="form-periode"
                      action="{{ route('monitoring.kalender.periode') }}"
                      method="POST"
                      class="d-flex gap-3 align-items-end flex-wrap">
                    @csrf

                    <div class="flex-grow-1 min-w-200">
                        <label class="fw-semibold">Mulai Pengajuan Hibah</label>
                        <input type="date"
                               id="periode-mulai"
                               name="start_date"
                               class="form-control"
                               value="{{ $periodeStart }}">
                    </div>
                    <div class="flex-grow-1 min-w-200">
                        <label class="fw-semibold">Berakhir Pengajuan Hibah</label>
                        <input type="date"
                               id="periode-akhir"
                               name="end_date"
                               class="form-control"
                               value="{{ $periodeEnd }}">
                    </div>

                    <button type="submit" class="btn btn-success">
                        Simpan Periode
                    </button>
                </form>
            </div>
        @endif
    </div>

    <!-- KALENDER -->
    <div id="calendar"></div>

</div>

<!-- POPUP AJUKAN PROPOSAL -->
<div id="proposalPopup" class="popup-overlay">
    <div class="popup-content">
        <span id="closePopupBtn" class="close-popup">&times;</span>

        <h5 class="fw-bold mb-3">Ajukan Proposal Baru</h5>

        <form>
            <div class="mb-3">
                <label class="form-label">Judul Proposal</label>
                <input type="text" class="form-control" placeholder="Masukkan judul proposal">
            </div>

            <div class="mb-3">
                <label class="form-label">File Proposal (PDF)</label>
                <input type="file" class="form-control">
            </div>

            <button type="submit" class="btn btn-success w-100">Unggah Proposal</button>
        </form>
    </div>
</div>

<!-- MODAL EVENT -->
<div class="modal fade" id="eventModal" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content p-3" style="border-radius: 18px;">
            <div class="modal-header border-0">
                <h5 class="modal-title fw-bold" id="eventTitle"></h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <p class="text-success fw-semibold mb-2" id="eventDate"></p>
                <div class="d-flex gap-2">
                    <button class="btn btn-success w-50" id="openPopupBtnCalendar">Ajukan Proposal</button>
                    <button class="btn btn-outline-success w-50">Edit Proposal</button>
                </div>
            </div>
        </div>
    </div>
</div>

@endsection

@push('styles')
<link href="https://cdn.jsdelivr.net/npm/fullcalendar@6.1.8/main.min.css" rel="stylesheet">
<style>
    .card-custom {
        border-radius: 18px;
        padding: 25px;
        background: #fff;
        box-shadow: 0 8px 30px rgba(0,0,0,0.08);
    }

    #calendar {
        background: #fff;
        padding: 20px;
        border-radius: 18px;
        box-shadow: 0 8px 30px rgba(0,0,0,0.08);
    }

    .filter-box {
        background: #f8f9fa;
        padding: 18px;
        border-radius: 14px;
        margin-bottom: 20px;
        box-shadow: 0 4px 20px rgba(0,0,0,0.05);
    }

    .min-w-200 {
        min-width: 200px;
    }

    .popup-overlay {
        display: none;
        position: fixed;
        top: 0;
        left: 0;
        width: 100%;
        height: 100%;
        background: rgba(0,0,0,0.45);
        backdrop-filter: blur(1px);
        justify-content: center;
        align-items: center;
        z-index: 99999;
    }

    .popup-content {
        background: #fff;
        padding: 25px;
        border-radius: 14px;
        width: 430px;
        position: relative;
        transform: scale(0.85);
        opacity: 0;
        transition: .25s ease;
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

    .close-popup:hover {
        color: red;
    }

    /* ====== CUSTOM WARNA TOMBOL VIEW FULLCALENDAR ====== */

    .fc .fc-button-group .fc-button {
        border: none;
        font-weight: 500;
        color: #fff;
    }

    /* Jadwal Bulan ini */
    .fc .fc-dayGridMonth-button {
        background: #0f172a;
    }
    .fc .fc-dayGridMonth-button:hover {
        background: #111827;
    }

    /* Jadwal Minggu ini */
    .fc .fc-timeGridWeek-button {
        background: #1f2937;
    }
    .fc .fc-timeGridWeek-button:hover {
        background: #232f3e;
    }

    /* Jadwal Hari ini */
    .fc .fc-timeGridDay-button {
        background: #263447;
    }
    .fc .fc-timeGridDay-button:hover {
        background: #2c3e50;
    }

    /* state tombol aktif */
    .fc .fc-button-primary.fc-button-active {
        background: #020617;
    }
</style>
@endpush

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/fullcalendar@6.1.8/index.global.min.js"></script>

<script>
document.addEventListener("DOMContentLoaded", function () {

    const popup = document.getElementById("proposalPopup");
    const closePopup = document.getElementById("closePopupBtn");
    const openPopupBtnCalendar = document.getElementById("openPopupBtnCalendar");

    const tahunUsulanInput  = document.getElementById("tahun-usulan");
    const tahunAjaranInput  = document.getElementById("tahun-ajaran");

    const periodeMulaiInput = document.getElementById("periode-mulai");
    const periodeAkhirInput = document.getElementById("periode-akhir");

    // periode dari DB yang dipakai untuk CEK BOLEH AJUKAN PROPOSAL
    const periodStartFromDb = @json($periodeStart);
    const periodEndFromDb   = @json($periodeEnd);

    let canSubmitNow = false;
    if (periodStartFromDb && periodEndFromDb) {
        const todayStr = new Date().toISOString().slice(0, 10);
        canSubmitNow = (todayStr >= periodStartFromDb && todayStr <= periodEndFromDb);
    }

    // buka popup dari kalender hanya kalau boleh
    openPopupBtnCalendar.addEventListener("click", () => {

        if (!canSubmitNow) {
            alert("Periode pengajuan hibah belum dibuka atau sudah berakhir.");
            return;
        }

        const modal = bootstrap.Modal.getInstance(document.getElementById("eventModal"));
        if (modal) modal.hide();

        popup.style.display = "flex";
        setTimeout(() => popup.classList.add("active"), 10);
    });

    // kalau tidak boleh submit, disable tombolnya
    if (!canSubmitNow) {
        openPopupBtnCalendar.classList.add('disabled');
        openPopupBtnCalendar.setAttribute('disabled', 'disabled');
        openPopupBtnCalendar.title = "Periode pengajuan hibah belum dibuka atau sudah berakhir.";
    }

    // tombol X popup
    closePopup.addEventListener("click", () => {
        popup.classList.remove("active");
        setTimeout(() => popup.style.display = "none", 250);
    });

    // klik luar popup
    popup.addEventListener("click", (e) => {
        if (e.target === popup) {
            popup.classList.remove("active");
            setTimeout(() => popup.style.display = "none", 250);
        }
    });

    // set teks tahun di box atas
    function setYearLabels(usulanYear, ajaranStartYear) {
        if (tahunUsulanInput) {
            tahunUsulanInput.value = usulanYear;
        }
        if (tahunAjaranInput) {
            tahunAjaranInput.value = ajaranStartYear + '/' + (ajaranStartYear + 1);
        }
    }

    // generate event berdasarkan tahun, tapi tanggal pengajuan dari DB kalau ada
    function buildEvents(usulanYear, ajaranStartYear) {
        const pelaksanaanStart = ajaranStartYear;
        const the_pelaksanaanEnd   = ajaranStartYear + 1;

        // default kalau belum ada di DB
        const defaultMulai = `${usulanYear}-02-01`;
        const defaultAkhir = `${usulanYear}-04-30`;

        const mulaiPengajuan = periodStartFromDb || defaultMulai;
       const akhirPengajuan = periodEndFromDb   || defaultAkhir;

        return [
            {
                title: "📥 Penerimaan Proposal",
                start: mulaiPengajuan,
                end:   akhirPengajuan,
                color: "#2e7d32"
            },
            { title: "📤 Pengumpulan ke Universitas",
              start: `${usulanYear}-05-01`,
              end:   `${usulanYear}-05-31`,
              color: "#1565c0"
            },
            { title: "📝 Review & Perbaikan",
              start: `${usulanYear}-06-01`,
              end:   `${usulanYear}-07-31`,
              color: "#f9a825"
            },
            { title: "📢 Pengumuman Proposal Didanai",
              start: `${usulanYear}-07-15`,
              color: "#8e24aa"
            },
            { title: "✒️ Tanda Tangan Kontrak",
              start: `${usulanYear}-08-01`,
              end:   `${usulanYear}-08-31`,
              color: "#5d4037"
            },
            { title: "💰 Pencairan Dana Penelitian",
              start: `${usulanYear}-08-10`,
              color: "#00897b"
            },

            {
                title: "🔬 Pelaksanaan Penelitian",
                start: `${pelaksanaanStart}-08-15`,
                end:   `${the_pelaksanaanEnd}-07-31`,
                color: "#388e3c"
            },
            {
                title: "📊 Monitoring Evaluasi",
                start: `${the_pelaksanaanEnd}-03-10`,
                end:   `${the_pelaksanaanEnd}-03-20`,
                color: "#ff9800"
            },
            {
                title: "📚 Laporan Akhir",
                start: `${the_pelaksanaanEnd}-07-10`,
                end:   `${the_pelaksanaanEnd}-07-31`,
                color: "#3f51b5"
            },
            {
                title: "🎤 Publikasi & Seminar",
                start: `${the_pelaksanaanEnd}-08-01`,
                end:   `${the_pelaksanaanEnd}-12-31`,
                color: "#6d4c41"
            }
        ];
    }

    // tahun awal: dari hari ini
    const today = new Date();
    let currentUsulanYear      = today.getFullYear();
    let currentAjaranStartYear = currentUsulanYear;

    setYearLabels(currentUsulanYear, currentAjaranStartYear);

    const calendarEl = document.getElementById("calendar");

    let calendar = new FullCalendar.Calendar(calendarEl, {
        initialView: "dayGridMonth",
        height: "750px",

        // bahasa Indonesia + label tombol kustom
        locale: 'id',
        headerToolbar: {
            left: "prev,next today",
            center: "title",
            right: "dayGridMonth,timeGridWeek,timeGridDay"
        },
        buttonText: {
            today:       'Hari ini',
            dayGridMonth:'Jadwal Bulan ini',
            timeGridWeek:'Jadwal Minggu ini',
            timeGridDay: 'Jadwal Hari ini'
        },

        events: buildEvents(currentUsulanYear, currentAjaranStartYear),

        // setiap view diubah (prev/next/today), sesuaikan tahun + event
        datesSet: function(info) {
            const viewYear = info.view.currentStart.getFullYear();
            currentUsulanYear      = viewYear;
            currentAjaranStartYear = viewYear;

            setYearLabels(currentUsulanYear, currentAjaranStartYear);

            calendar.removeAllEvents();
            const evs = buildEvents(currentUsulanYear, currentAjaranStartYear);
            evs.forEach(e => calendar.addEvent(e));
        },

        eventClick: function(info) {
            document.getElementById("eventTitle").innerText = info.event.title;

            let dateText = "";
            const start = info.event.start;
            const end   = info.event.end;

            if (end && end.getTime() !== start.getTime()) {
                const opt = { day: "numeric", month: "long", year: "numeric" };
                const endMinusOne = new Date(end.getTime() - 1 * 24 * 60 * 60 * 1000);
                dateText = start.toLocaleDateString("id-ID", opt) +
                           " – " +
                           endMinusOne.toLocaleDateString("id-ID", opt);
            } else {
                dateText = start.toLocaleDateString("id-ID", {
                    weekday: "long",
                    day: "numeric",
                    month: "long",
                    year: "numeric"
                });
            }

            document.getElementById("eventDate").innerText = dateText;

            new bootstrap.Modal(document.getElementById("eventModal")).show();
        }
    });

    calendar.render();

    // opsional: auto-submit form kalau admin ganti tanggal
    if (periodeMulaiInput && periodeAkhirInput) {
        const formPeriode = document.getElementById('form-periode');
        const autoSubmit = () => {
            if (formPeriode) {
                formPeriode.submit();
            }
        };

        // kalau mau otomatis, tinggal aktifkan:
        // periodeMulaiInput.addEventListener("change", autoSubmit);
        // periodeAkhirInput.addEventListener("change", autoSubmit);
    }

});
</script>
@endpush

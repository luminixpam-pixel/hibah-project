@extends('layouts.app')

@section('content')
<link href="https://cdn.jsdelivr.net/npm/fullcalendar@6.1.8/main.min.css" rel="stylesheet">
<script src="https://cdn.jsdelivr.net/npm/fullcalendar@6.1.8/index.global.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>

<style>

.card-custom {
    border-radius: 18px;
    padding: 25px;
    background: #ffffff;
    box-shadow: 0 8px 30px rgba(0,0,0,0.08);
}

#calendar {
    background: white;
    padding: 20px;
    border-radius: 18px;
    box-shadow: 0 8px 30px rgba(0,0,0,0.08);
}

/* Filter card */
.filter-box {
    background: #f8f9fa;
    padding: 18px;
    border-radius: 14px;
    margin-bottom: 20px;
    box-shadow: 0 4px 20px rgba(0,0,0,0.05);
}

/* Custom popup */
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

<div class="container py-4">

    <h3 class="fw-bold mb-4">📊 Monitoring Data Penelitian</h3>

    <!-- TIMELINE -->
    <div class="card-custom mb-4">
        <h5 class="fw-bold mb-3">📅 Kalender Timeline</h5>

        <div class="row">
            <div class="col-md-6">
                <p><span>Februari – April</span> : Penerimaan Proposal</p>
                <p><span>Mei</span> : Pengumpulan Proposal ke Universitas</p>
                <p><span>Juni – Juli</span> : Review & Perbaikan</p>
                <p><span>Juli</span> : Pengumuman Proposal Didanai</p>
                <p><span>Agustus</span> : Tanda Tangan Kontrak</p>
            </div>
            <div class="col-md-6">
                <p><span>Agustus</span> : Pencairan Dana Penelitian</p>
                <p><span>Agustus – Juli</span> : Pelaksanaan Penelitian</p>
                <p><span>Maret</span> : Monitoring Evaluasi</p>
                <p><span>Juli</span> : Laporan Akhir</p>
                <p><span>Agustus – Desember</span> : Publikasi & Seminar</p>
            </div>
        </div>
    </div>

    <!-- FILTER -->
        <div class="filter-box d-flex justify-content-between mb-4">
            <div class="w-25">
                <label class="fw-semibold">Tahun Usulan</label>
                <select class="form-select">
                    <option>2025</option>
                    <option>2026</option>
                    <option>2027</option>
                </select>
            </div>

            <div class="w-25">
                <label class="fw-semibold">Tahun Ajaran Pelaksanaan</label>
                <select class="form-select">
                    <option>2025/2026</option>
                    <option>2026/2027</option>
                    <option>2027/2028</option>
                </select>
            </div>
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



<script>
document.addEventListener("DOMContentLoaded", function () {

    const popup = document.getElementById("proposalPopup");
    const closePopup = document.getElementById("closePopupBtn");

    // buka popup dari kalender
    document.getElementById("openPopupBtnCalendar").addEventListener("click", () => {
        new bootstrap.Modal(document.getElementById("eventModal")).hide();

        popup.style.display = "flex";
        setTimeout(() => popup.classList.add("active"), 10);
    });

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




    /* ---------- FULL CALENDAR ----------- */

    let calendar = new FullCalendar.Calendar(document.getElementById("calendar"), {

        initialView: "dayGridMonth",
        height: "750px",

        headerToolbar: {
            left: "prev,next today",
            center: "title",
            right: "dayGridMonth,timeGridWeek,timeGridDay"
        },

        events: [
            { title: "📥 Pengajuan Proposal Dibuka", start: "2025-02-01", color: "#4CAF50" },
            { title: "📤 Pengajuan Proposal Ditutup", start: "2025-04-30", color: "#E53935" },
            { title: "📝 Review Proposal", start: "2025-05-01", end: "2025-05-15", color: "#FFC107" },
            { title: "📢 Pengumuman Proposal Didanai", start: "2025-07-05", color: "#8E24AA" },
            { title: "✒️ Tanda Tangan Kontrak", start: "2025-08-01", color: "#1976D2" },
            { title: "💰 Pencairan Dana", start: "2025-08-15", color: "#009688" },
            { title: "🔬 Pelaksanaan Penelitian", start: "2025-08-20", end: "2026-07-30", color: "#43A047" },
            { title: "📊 Monitoring Evaluasi", start: "2026-03-10", color: "#FF9800" },
            { title: "📚 Laporan Akhir", start: "2026-07-25", color: "#3F51B5" },
            { title: "🎤 Publikasi & Seminar", start: "2026-08-01", end: "2026-12-30", color: "#6D4C41" }
        ],

        eventClick: function(info) {
            // isi modal
            document.getElementById("eventTitle").innerText = info.event.title;

            let date = info.event.start.toLocaleDateString("id-ID", {
                weekday: "long",
                day: "numeric",
                month: "long",
                year: "numeric"
            });

            document.getElementById("eventDate").innerText = date;

            new bootstrap.Modal(document.getElementById("eventModal")).show();
        }

    });

    calendar.render();

});
</script>

@endsection

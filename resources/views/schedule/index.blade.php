@extends('layouts.app')

@section('content')
<div class="text-center mb-4">
    <h2>E-Hibah Kalender</h2>
</div>

{{-- 🔍 Pencarian --}}
<div class="d-flex justify-content-center mb-4">
    <div class="input-group w-50">
        <input type="text" id="searchInput" class="form-control" placeholder="Cari acara...">
        <button id="searchButton" class="btn btn-primary">Cari</button>
        <button id="resetButton" class="btn btn-secondary">Reset</button>
    </div>
</div>

<div class="card shadow-sm">
    <div class="card-body">
        <div id="calendar"></div>
    </div>
</div>

{{-- 🗓️ Modal Tambah/Edit --}}
<div class="modal fade" id="eventModal" tabindex="-1" aria-labelledby="eventModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow-lg">
            <div class="modal-header bg-primary text-white">
                <h5 class="modal-title" id="eventModalLabel">Tambah / Edit Acara</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body p-4">
                <form id="eventForm">
                    <input type="hidden" id="eventId">

                    <div class="mb-3">
                        <label class="form-label fw-semibold">Judul Acara</label>
                        <input type="text" class="form-control" id="eventTitle" required placeholder="Masukkan judul acara">
                    </div>

                    <div class="row g-3 mb-3">
                        <div class="col-md-6">
                            <label class="form-label fw-semibold">Tanggal Mulai</label>
                            <input type="date" class="form-control" id="eventStartDate" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-semibold">Tanggal Selesai</label>
                            <input type="date" class="form-control" id="eventEndDate" required>
                        </div>
                    </div>

                    <div class="row g-3 mb-4">
                        <div class="col-md-6">
                            <label class="form-label fw-semibold">Jam Mulai</label>
                            <input type="time" class="form-control" id="eventStartTime" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-semibold">Jam Selesai</label>
                            <input type="time" class="form-control" id="eventEndTime" required>
                        </div>
                    </div>

                    <div class="d-flex justify-content-between">
                        <button type="button" id="deleteButton" class="btn btn-danger">Hapus</button>
                        <button type="submit" class="btn btn-primary w-50 fw-semibold">Simpan</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<link href="https://cdn.jsdelivr.net/npm/fullcalendar@6.1.11/index.global.min.css" rel="stylesheet">
<script src="https://cdn.jsdelivr.net/npm/fullcalendar@6.1.11/index.global.min.js"></script>

<style>
    /* 🖤 Pastikan semua font event hitam */
    .fc-event,
    .fc-daygrid-event,
    .fc-timegrid-event,
    .fc-event-title,
    .fc-event-main,
    .fc-event-time,
    .fc-event a {
        color: #000 !important;
        text-decoration: none !important;
        font-weight: 500;
    }

    /* 🎨 Box event */
    .fc-daygrid-event, .fc-timegrid-event {
        background-color: #f8f9fa !important;
        border: 1px solid #d1d5db !important;
        border-radius: 6px !important;
        padding: 4px 6px !important;
    }

    /* 🌈 Hover event */
    .fc-daygrid-event:hover, .fc-timegrid-event:hover {
        background-color: #e2e6ea !important;
        border-color: #adb5bd !important;
    }

    /* 📅 Title & Toolbar */
    .fc .fc-toolbar-title {
        font-size: 1.3rem;
        font-weight: 600;
        color: #222;
    }

    /* 🕒 Tinggi slot mingguan */
    .fc-timegrid-slot {
        height: 2.3em !important;
    }
</style>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const calendarEl = document.getElementById('calendar');
    const calendar = new FullCalendar.Calendar(calendarEl, {
        initialView: 'dayGridMonth',
        locale: 'id',
        height: 650,
        selectable: true,
        editable: true,
        expandRows: true,
        slotMinTime: "07:00:00",
        slotMaxTime: "21:00:00",
        eventDisplay: 'block',
        headerToolbar: {
            left: 'prev,next today',
            center: 'title',
            right: 'dayGridMonth,timeGridWeek,timeGridDay'
        },
        buttonText: {
            today: 'Hari Ini',
            month: 'Bulan Ini',
            week: 'Minggu Ini',
            day: 'Hari Ini'
        },
        eventTimeFormat: { hour: '2-digit', minute: '2-digit', hour12: false },

        events: [
            { id: '1', title: 'Deadline Proposal', start: '2025-11-12T08:00:00', end: '2025-11-15T17:00:00' },
            { id: '2', title: 'Rapat Evaluasi', start: '2025-11-20T10:00:00', end: '2025-11-20T12:00:00' }
        ],

        dateClick: function(info) {
            const today = new Date().toISOString().split('T')[0];
            const clicked = info.dateStr.split('T')[0];
            if (clicked < today) {
                alert("Tidak bisa membuat acara di tanggal yang sudah lewat!");
                return;
            }

            document.getElementById('eventForm').reset();
            document.getElementById('eventId').value = '';
            document.getElementById('deleteButton').disabled = true;
            document.getElementById('eventStartDate').value = clicked;
            document.getElementById('eventEndDate').value = clicked;

            new bootstrap.Modal(document.getElementById('eventModal')).show();
        },

        eventClick: function(info) {
            const event = info.event;
            document.getElementById('eventId').value = event.id;
            document.getElementById('eventTitle').value = event.title;

            const start = event.start;
            const end = event.end || start;

            document.getElementById('eventStartDate').value = start.toISOString().substring(0, 10);
            document.getElementById('eventEndDate').value = end.toISOString().substring(0, 10);
            document.getElementById('eventStartTime').value = start.toISOString().substring(11, 16);
            document.getElementById('eventEndTime').value = end.toISOString().substring(11, 16);

            document.getElementById('deleteButton').disabled = false;
            new bootstrap.Modal(document.getElementById('eventModal')).show();
        }
    });

    calendar.render();

    // ✅ Simpan acara
    document.getElementById('eventForm').addEventListener('submit', function(e) {
        e.preventDefault();
        const id = document.getElementById('eventId').value;
        const title = document.getElementById('eventTitle').value.trim();
        const startDate = document.getElementById('eventStartDate').value;
        const endDate = document.getElementById('eventEndDate').value;
        const startTime = document.getElementById('eventStartTime').value;
        const endTime = document.getElementById('eventEndTime').value;

        const today = new Date().toISOString().split('T')[0];
        if (startDate < today || endDate < today) {
            alert("Tanggal mulai/selesai tidak boleh di masa lalu!");
            return;
        }
        if (endDate < startDate) {
            alert("Tanggal selesai tidak boleh sebelum tanggal mulai!");
            return;
        }

        const startDateTime = startDate + 'T' + startTime;
        const endDateTime = endDate + 'T' + endTime;

        if (id) {
            let event = calendar.getEventById(id);
            event.setProp('title', title);
            event.setStart(startDateTime);
            event.setEnd(endDateTime);
        } else {
            const newId = String(Date.now());
            calendar.addEvent({
                id: newId,
                title: title,
                start: startDateTime,
                end: endDateTime,
                allDay: false
            });
        }

        bootstrap.Modal.getInstance(document.getElementById('eventModal')).hide();
    });

    // 🗑️ Hapus acara
    document.getElementById('deleteButton').addEventListener('click', function() {
        const id = document.getElementById('eventId').value;
        if (id) {
            const event = calendar.getEventById(id);
            if (event && confirm('Yakin ingin menghapus acara ini?')) {
                event.remove();
                bootstrap.Modal.getInstance(document.getElementById('eventModal')).hide();
            }
        }
    });

    // 🔍 Pencarian
    document.getElementById('searchButton').addEventListener('click', function() {
        let keyword = document.getElementById('searchInput').value.toLowerCase();
        calendar.getEvents().forEach(event => {
            const match = event.title.toLowerCase().includes(keyword);
            event.setProp('display', match ? 'auto' : 'none');
        });
    });

    // 🔁 Reset
    document.getElementById('resetButton').addEventListener('click', function() {
        document.getElementById('searchInput').value = '';
        calendar.getEvents().forEach(event => event.setProp('display', 'auto'));
    });
});
</script>
@endpush

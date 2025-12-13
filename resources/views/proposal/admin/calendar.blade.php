@extends('layouts.app')

@section('content')
<div class="container mt-5">
    <h2 class="mb-4">Kalender Admin</h2>
    <div id='calendar'></div>
</div>

<!-- Modal Popup Event -->
<div class="modal fade" id="eventModal" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title">Detail Event</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
      </div>
      <div class="modal-body">
        <p id="eventTitle"></p>
        <p id="eventDate"></p>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Tutup</button>
      </div>
    </div>
  </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    var calendarEl = document.getElementById('calendar');
    var calendar = new FullCalendar.Calendar(calendarEl, {
        initialView: 'dayGridMonth',
        selectable: true,
        headerToolbar: {
            left: 'prev,next today',
            center: 'title',
            right: 'dayGridMonth,timeGridWeek,timeGridDay'
        },
        events: @json($events), // ambil dari controller
        dateClick: function(info) {
            alert('Tanggal diklik: ' + info.dateStr);
        },
        eventClick: function(info) {
            // tampilkan modal
            document.getElementById('eventTitle').innerText = 'Judul: ' + info.event.title;
            document.getElementById('eventDate').innerText = 'Tanggal: ' + info.event.start.toISOString().split('T')[0];
            var myModal = new bootstrap.Modal(document.getElementById('eventModal'));
            myModal.show();
        }
    });
    calendar.render();
});
</script>
@endsection

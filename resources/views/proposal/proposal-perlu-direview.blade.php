@extends('layouts.app')

@section('content')

<style>
    .page-title { font-weight:700; font-size:22px; color: #1e293b; }
    .page-subtitle { color:#64748b; font-size:14px; }

    /* Table Styling */
    .table thead th {
        background:#f8fafc !important;
        font-weight:600;
        font-size: 11px;
        text-transform: none; /* ✅ dulu uppercase, sekarang normal */
        letter-spacing: 0.5px;
        color: #475569;
        padding: 12px 15px !important;
        border-bottom: 2px solid #e2e8f0;
    }
    .table td { vertical-align: middle; padding: 15px !important; }

    /* Status Badges */
    .status-badge { padding: 4px 10px; border-radius: 50px; font-size: 10px; font-weight: 700; text-transform: none; } /* ✅ dulu uppercase, sekarang normal */
    .bg-ongoing { background: #e0f2fe; color: #0369a1; border: 1px solid #bae6fd; }
    .bg-waiting { background: #fef3c7; color: #92400e; border: 1px solid #fde68a; }
    .bg-finish { background: #dcfce7; color: #15803d; border: 1px solid #bbf7d0; }

    /* Action Button */
    .btn-action { font-size: 11px; font-weight: 600; padding: 6px 12px; border-radius: 6px; transition: all 0.2s; }

    /* Reviewer Assigned Box */
    .reviewer-assigned-box {
        background: #f8fafc;
        border: 1px solid #e2e8f0;
        border-radius: 10px;
        padding: 10px;
    }

    /* Form Plotting */
    .reviewer-form { display:flex; flex-direction:column; gap:8px; }
    .deadline-group label { font-size:11px; font-weight: 700; color:#64748b; margin-bottom: 3px; display: block; }

    /* Autocomplete */
    .autocomplete-box {
        border:1px solid #e2e8f0;
        max-height:200px;
        overflow-y:auto;
        position:absolute;
        z-index:1050;
        background:#fff;
        width:100%;
        border-radius:8px;
        box-shadow:0 10px 15px -3px rgba(0,0,0,0.1);
        margin-top:2px;
    }
    .autocomplete-item { padding:10px 12px; cursor:pointer; border-bottom: 1px solid #f1f5f9; }
    .autocomplete-item:hover { background:#f8fafc; }

    /* Deadline Wrapper */
    .deadline-wrapper { padding: 8px; border-radius: 8px; border: 1px solid #e2e8f0; text-align: center; min-width: 120px; }
    .deadline-overdue { background-color: #fef2f2; border-color: #fee2e2; }
    .deadline-urgent { background-color: #fffbeb; border-color: #fef3c7; }
</style>

<div class="container mt-4 mb-5">
    <div class="d-flex justify-content-between align-items-end mb-4">
        <div>
            <h4 class="page-title mb-1">Daftar Review Proposal</h4>
            <p class="page-subtitle mb-0">
                @if(auth()->user()->role === 'admin')
                    Manajemen pemilihan reviewer dan pemantauan masa penilaian.
                @else
                    Daftar penugasan review proposal yang harus Anda nilai.
                @endif
            </p>
        </div>
        <div class="text-end">
            <span class="badge bg-white text-dark border shadow-sm p-2">
                Total: {{ $proposals->count() }} Proposal
            </span>
        </div>
    </div>

    <div class="card border-0 shadow-sm overflow-hidden" style="border-radius: 12px;">
        <div class="table-responsive">
            <table class="table table-hover mb-0">
                <thead>
                    <tr>
                        <th class="text-center" width="50">No</th>
                        <th style="min-width: 250px;">{{ auth()->user()->role === 'admin' ? 'Setting Reviewer' : 'Status Review' }}</th>
                        <th style="min-width: 180px;">Detail Pengusul</th>
                        <th style="min-width: 280px;">Judul & Dokumen</th>
                        <th class="text-center" style="min-width: 150px;">Tenggat</th>
                        <th class="text-center" width="130">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($proposals as $index => $proposal)
                        @php
                            $isAssigned = $proposal->reviewers->count() >= 2;
                            $dbDeadline = $proposal->review_deadline ? \Carbon\Carbon::parse($proposal->review_deadline) : null;
                            $isOverdue = $dbDeadline ? now()->gt($dbDeadline) : false;
                            $isUrgent = $dbDeadline ? (now()->diffInHours($dbDeadline) <= 24 && !$isOverdue) : false;

                            // Cek apakah user saat ini sudah memberikan review (untuk Reviewer)
                            $hasReviewed = $proposal->reviews->where('reviewer_id', auth()->id())->first();
                        @endphp
                        <tr>
                            <td class="text-center fw-bold text-muted">{{ $index + 1 }}</td>

                            {{-- COLUMN 1: SETTING / STATUS --}}
                            <td>
                                @if(auth()->user()->role === 'admin')
                                    @if($isAssigned)
                                        <div class="reviewer-assigned-box mb-2">
                                            <div class="d-flex justify-content-between align-items-center mb-2">
                                                <span class="status-badge {{ $proposal->status === 'Review Selesai' ? 'bg-finish' : 'bg-ongoing' }}">
                                                    {{ $proposal->status }}
                                                </span>
                                                <button class="btn btn-sm btn-link p-0 text-decoration-none small fw-bold"
                                                        onclick="document.getElementById('form-{{ $proposal->id }}').classList.toggle('d-none')">
                                                    Ubah Plotting
                                                </button>
                                            </div>
                                            @foreach($proposal->reviewers as $rev)
                                                <div class="small mb-1 text-dark"><i class="bi bi-person-check-fill me-2 text-primary"></i>{{ $rev->name }}</div>
                                            @endforeach
                                        </div>
                                    @else
                                        <div class="mb-2"><span class="status-badge bg-waiting">Menunggu Pemilihan</span></div>
                                    @endif

                                    <form id="form-{{ $proposal->id }}" action="{{ route('proposal.assignReviewer', $proposal->id) }}"
                                          method="POST" class="reviewer-form {{ $isAssigned ? 'd-none border-top pt-2 mt-2' : '' }}">
                                        @csrf
                                        <div class="position-relative">
                                            <input type="hidden" name="reviewer_1" id="rev1_id_{{ $proposal->id }}" value="{{ $proposal->reviewers->get(0)?->id }}">
                                            <input type="text" class="form-control form-control-sm reviewer-search" placeholder="Cari Reviewer 1..." data-target="rev1" data-proposal="{{ $proposal->id }}" autocomplete="off" value="{{ $proposal->reviewers->get(0)?->name }}">
                                            <div class="autocomplete-box d-none" id="rev1_box_{{ $proposal->id }}"></div>
                                        </div>
                                        <div class="position-relative">
                                            <input type="hidden" name="reviewer_2" id="rev2_id_{{ $proposal->id }}" value="{{ $proposal->reviewers->get(1)?->id }}">
                                            <input type="text" class="form-control form-control-sm reviewer-search" placeholder="Cari Reviewer 2..." data-target="rev2" data-proposal="{{ $proposal->id }}" autocomplete="off" value="{{ $proposal->reviewers->get(1)?->name }}">
                                            <div class="autocomplete-box d-none" id="rev2_box_{{ $proposal->id }}"></div>
                                        </div>
                                        <div class="deadline-group">
                                            <label>Tenggat Penilaian</label>
                                            <input type="datetime-local" name="review_deadline" class="form-control form-control-sm"
                                                   value="{{ $dbDeadline ? $dbDeadline->format('Y-m-d\TH:i') : '' }}" required>
                                        </div>
                                        <button type="submit" class="btn btn-primary btn-sm w-100 shadow-sm fw-bold">Simpan</button>
                                    </form>
                                @else
                                    {{-- Info untuk Reviewer --}}
                                    <span class="status-badge {{ $hasReviewed ? 'bg-finish' : 'bg-ongoing' }}">
                                        {{ $hasReviewed ? 'Sudah Dinilai' : 'Menunggu Penilaian' }}
                                    </span>
                                    <div class="mt-2 small text-muted">
                                        <i class="bi bi-info-circle me-1"></i> Status Proposal: <strong>{{ $proposal->status }}</strong>
                                    </div>
                                @endif
                            </td>

                            {{-- COLUMN 2: PENGUSUL --}}
                            <td>
                                <div class="fw-bold text-dark" style="font-size: 14px;">{{ $proposal->nama_ketua }}</div>
                                <div class="text-muted" style="font-size: 12px;">
                                    <i class="bi bi-building me-1"></i>{{ $proposal->user->fakultas ?? 'Fakultas' }}
                                </div>
                            </td>

                            {{-- COLUMN 3: JUDUL & FILE --}}
                            <td>
                                <div class="fw-semibold text-dark mb-2" style="line-height: 1.4; font-size: 13px;">{{ $proposal->judul }}</div>
                                <a href="{{ route('proposal.download', $proposal->id) }}" class="btn btn-sm btn-outline-danger py-0 px-2" style="font-size: 11px;">
                                    <i class="bi bi-file-earmark-pdf-fill me-1"></i> Unduh Proposal
                                </a>
                            </td>

                            {{-- COLUMN 4: DEADLINE --}}
                            <td class="text-center">
                                @if($dbDeadline)
                                    <div class="deadline-wrapper {{ $isOverdue ? 'deadline-overdue' : ($isUrgent ? 'deadline-urgent' : '') }}">
                                        <div class="fw-bold text-dark" style="font-size: 13px;">
                                            {{ $dbDeadline->translatedFormat('d M Y') }}
                                        </div>
                                        <div class="text-muted small mb-1">
                                            {{ $dbDeadline->format('H:i') }} WIB
                                        </div>
                                        @if($isOverdue)
                                            <span class="badge bg-danger w-100" style="font-size: 9px;">WAKTU HABIS</span>
                                        @else
                                            @php
                                                $diffText = now()->diffForHumans($dbDeadline, true);
                                                $diffText = str_replace(
                                                    [' seconds', ' second', ' minutes', ' minute', ' hours', ' hour', ' days', ' day', ' weeks', ' week', ' months', ' month', ' years', ' year'],
                                                    [' detik',   ' detik',  ' menit',   ' menit',  ' jam',   ' jam',  ' hari', ' hari',' minggu',' minggu',' bulan',  ' bulan', ' tahun',' tahun'],
                                                    $diffText
                                                );
                                            @endphp
                                            <span class="badge bg-success w-100" style="font-size: 9px;">
                                                Tenggat {{ $diffText }} lagi
                                            </span>
                                        @endif
                                    </div>
                                @else
                                    <span class="text-muted small italic">Belum diset</span>
                                @endif
                            </td>

                            {{-- COLUMN 5: AKSI --}}
                            <td class="text-center">
                                @if(auth()->user()->role === 'reviewer')
                                    <a href="{{ route('reviewer.isi-review', $proposal->id) }}"
                                       class="btn btn-action {{ $hasReviewed ? 'btn-outline-secondary' : 'btn-primary shadow-sm' }} w-100">
                                        <i class="bi {{ $hasReviewed ? 'bi-eye' : 'bi-pencil-square' }} me-1"></i>
                                        {{ $hasReviewed ? 'Lihat Nilai' : 'Beri Nilai' }}
                                    </a>
                                @else
                                    <a href="{{ route('proposal.tinjau', $proposal->id) }}" class="btn btn-action btn-light border w-100">
                                        <i class="bi bi-search me-1"></i> Tinjau
                                    </a>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="6" class="text-center py-5 text-muted">Data proposal tidak ditemukan.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>

@endsection

@push('scripts')
<script>
    // Logic for Autocomplete (Tetap sama dengan kode awal Anda)
    document.querySelectorAll('.reviewer-search').forEach(input => {
        input.addEventListener('keyup', function () {
            const query = this.value.trim();
            const proposalId = this.dataset.proposal;
            const target = this.dataset.target;
            const box = document.getElementById(`${target}_box_${proposalId}`);

            if (query.length < 2) {
                box.classList.add('d-none');
                return;
            }

            fetch(`/admin/search-reviewer?q=${query}`)
                .then(res => res.json())
                .then(data => {
                    box.innerHTML = '';
                    box.classList.remove('d-none');
                    if (data.length === 0) {
                        box.innerHTML = `<div class="autocomplete-item text-muted small text-center">Tidak ditemukan</div>`;
                        return;
                    }
                    data.forEach(user => {
                        const div = document.createElement('div');
                        div.className = 'autocomplete-item';
                        div.innerHTML = `
                            <div class="fw-bold" style="font-size: 12px;">${user.name}</div>
                            <div class="text-muted" style="font-size: 10px;">${user.penempatan ?? 'Reviewer'}</div>
                        `;
                        div.onclick = () => {
                            input.value = user.name;
                            document.getElementById(`${target}_id_${proposalId}`).value = user.id;
                            box.classList.add('d-none');
                        };
                        box.appendChild(div);
                    });
                });
        });
    });

    document.addEventListener('click', function(e) {
        if (!e.target.classList.contains('reviewer-search')) {
            document.querySelectorAll('.autocomplete-box').forEach(box => box.classList.add('d-none'));
        }
    });
</script>
@endpush

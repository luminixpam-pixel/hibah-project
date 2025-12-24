@extends('layouts.app')

@section('content')

<style>
.page-title { font-weight:700; font-size:22px; }
.page-subtitle { color:#6c757d; font-size:14px; }

/* rapihin tabel */
.table thead th{
    background:#f8f9fa !important;
    font-weight:600;
    white-space:nowrap;
}
.table td{
    vertical-align:top;
}
.col-reviewer{ min-width:320px; }
.col-pengusul{ min-width:160px; }
.col-judul{ min-width:260px; }
.col-deadline{ min-width:170px; }
.col-aksi{ min-width:140px; }

/* form reviewer jadi rapi */
.reviewer-form{
    display:flex;
    flex-direction:column;
    gap:8px;
    margin:0;
}
.reviewer-form .form-control{ width:100%; }
.reviewer-inputs{
    display:flex;
    flex-direction:column;
    gap:6px;
}
.deadline-group label{
    font-size:12px;
    color:#6c757d;
    margin:0 0 2px 0;
}
.deadline-text{ line-height:1.15; }
.deadline-badge{
    display:inline-flex;
    align-items:center;
    gap:6px;
    margin-top:6px;
    font-weight:600;
    font-size:12px;
}
.deadline-badge.danger{ color:#dc3545; }
.deadline-badge.warning{ color:#ffc107; }

/* autocomplete */
.autocomplete-box{
    border:1px solid #ddd;
    max-height:180px;
    overflow-y:auto;
    position:absolute;
    z-index:1050;
    background:#fff;
    width:100%;
    border-radius:8px;
    box-shadow:0 8px 18px rgba(0,0,0,.08);
    margin-top:2px;
}
.autocomplete-item{ padding:8px 10px; cursor:pointer; }
.autocomplete-item:hover{ background:#f1f1f1; }

/* kecilin padding tabel biar ga “meledak” */
.table-sm td, .table-sm th{ padding:10px 12px; }
</style>

<div class="container mt-4">

    <h4 class="page-title mb-1">
        Daftar Proposal Yang Perlu Direview — Universitas YARSI
    </h4>
    <p class="page-subtitle mb-4">
        Admin menetapkan 2 reviewer, reviewer yang ditugaskan dapat mengisi penilaian.
    </p>

    <div class="table-responsive">
        <table class="table table-striped table-hover table-sm align-middle shadow-sm">
            <thead>
                <tr>
                    <th width="50">No</th>
                    <th class="col-reviewer">Reviewer</th>
                    <th class="col-pengusul">Pengusul</th>
                    <th class="col-judul">Judul Proposal</th>
                    <th class="col-deadline">Tenggat Review</th>
                    <th class="col-aksi">Aksi</th>
                </tr>
            </thead>

            <tbody>
            @forelse ($proposals as $index => $proposal)
                <tr>
                    <td class="fw-semibold">{{ $index + 1 }}</td>

                    {{-- ================= REVIEWER ================= --}}
                    <td class="col-reviewer">
                        @if(auth()->user()->role === 'admin')

                        <form action="{{ route('proposal.assignReviewer', $proposal->id) }}"
                              method="POST"
                              class="position-relative reviewer-form">
                            @csrf

                            <div class="reviewer-inputs position-relative">

                                {{-- Reviewer 1 --}}
                                <input type="hidden"
                                       name="reviewer_1"
                                       id="rev1_id_{{ $proposal->id }}"
                                       value="{{ $proposal->reviewers->get(0)?->id }}">

                                <div class="position-relative">
                                    <input type="text"
                                           class="form-control form-control-sm reviewer-search"
                                           placeholder="Cari Reviewer 1"
                                           data-target="rev1"
                                           data-proposal="{{ $proposal->id }}"
                                           autocomplete="off"
                                           value="{{ $proposal->reviewers->get(0)?->name }}">
                                    <div class="autocomplete-box d-none"
                                         id="rev1_box_{{ $proposal->id }}"></div>
                                </div>

                                {{-- Reviewer 2 --}}
                                <input type="hidden"
                                       name="reviewer_2"
                                       id="rev2_id_{{ $proposal->id }}"
                                       value="{{ $proposal->reviewers->get(1)?->id }}">

                                <div class="position-relative">
                                    <input type="text"
                                           class="form-control form-control-sm reviewer-search"
                                           placeholder="Cari Reviewer 2"
                                           data-target="rev2"
                                           data-proposal="{{ $proposal->id }}"
                                           autocomplete="off"
                                           value="{{ $proposal->reviewers->get(1)?->name }}">
                                    <div class="autocomplete-box d-none"
                                         id="rev2_box_{{ $proposal->id }}"></div>
                                </div>

                                {{-- Tenggat Review --}}
                                <div class="deadline-group">
                                    <label class="form-label small text-muted">Tenggat Waktu Review</label>
                                    <input type="datetime-local"
                                        name="review_deadline"
                                        class="form-control form-control-sm"
                                        value="{{ $proposal->review_deadline ? \Carbon\Carbon::parse($proposal->review_deadline)->format('Y-m-d\TH:i') : '' }}"
                                        required>
                                </div>

                            </div>

                            <button type="submit"
                                    class="btn btn-outline-primary btn-sm w-100">
                                Kirim ke Reviewer
                            </button>
                        </form>

                        @else
                            @forelse ($proposal->reviewers as $rev)
                                <div>• {{ $rev->name }}</div>
                            @empty
                                <span class="text-muted">Belum ditetapkan</span>
                            @endforelse
                        @endif
                    </td>

                    <td class="col-pengusul">{{ $proposal->nama_ketua }}</td>

                    <td class="col-judul">
                        <div class="fw-semibold">{{ $proposal->judul }}</div>
                    </td>

                    {{-- ================= TENGGAT ================= --}}
                    <td class="col-deadline">
                        @if($proposal->review_deadline)
                            @php $deadline = \Carbon\Carbon::parse($proposal->review_deadline); @endphp

                            <div class="deadline-text">
                                <div class="fw-semibold">{{ $deadline->format('d M Y') }}</div>
                                <div class="text-muted small">{{ $deadline->format('H:i') }} WIB</div>

                                @if(now()->gt($deadline))
                                    <div class="deadline-badge danger">Lewat Tenggat</div>
                                @elseif(now()->diffInHours($deadline) <= 24)
                                    <div class="deadline-badge warning">Kurang dari 24 jam</div>
                                @endif
                            </div>
                        @else
                            <span class="text-muted">Belum ditentukan</span>
                        @endif
                    </td>

                    {{-- ================= AKSI ================= --}}
                    <td class="col-aksi">
                        @if(auth()->user()->role === 'reviewer')
                            @if($proposal->reviewers->pluck('id')->contains(auth()->id()))
                                @if($proposal->review_deadline && now()->gt($proposal->review_deadline))
                                    <button class="btn btn-secondary btn-sm w-100" disabled>
                                        Tenggat Berakhir
                                    </button>
                                @else
                                    <a href="{{ route('reviewer.isi-review', $proposal->id) }}"
                                       class="btn btn-success btn-sm w-100">
                                        Beri Review
                                    </a>
                                @endif
                            @else
                                <span class="text-muted">—</span>
                            @endif
                        @else
                            <span class="text-muted">—</span>
                        @endif
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="6" class="text-center text-muted">
                        Belum ada proposal.
                    </td>
                </tr>
            @endforelse
            </tbody>

        </table>
    </div>
</div>
@endsection

@push('scripts')
<script>
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
                    box.innerHTML = `
                        <div class="autocomplete-item text-muted">
                            Reviewer tidak ditemukan
                        </div>`;
                    return;
                }

                data.forEach(user => {
                    const div = document.createElement('div');
                    div.className = 'autocomplete-item';
                    div.innerHTML = `
                        <strong>${user.name}</strong><br>
                        <small class="text-muted">${user.penempatan ?? ''}</small>
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

// klik di luar nutup box
document.addEventListener('click', function(e) {
    if (!e.target.classList.contains('reviewer-search')) {
        document.querySelectorAll('.autocomplete-box').forEach(box => {
            box.classList.add('d-none');
        });
    }
});
</script>
@endpush

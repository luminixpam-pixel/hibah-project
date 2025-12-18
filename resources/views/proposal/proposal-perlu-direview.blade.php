@extends('layouts.app')

@section('content')

<style>
.page-title {
    font-weight: 700;
    font-size: 22px;
}
.page-subtitle {
    color: #6c757d;
    font-size: 14px;
}
.autocomplete-box {
    border: 1px solid #ddd;
    max-height: 180px;
    overflow-y: auto;
    position: absolute;
    z-index: 1050;
    background: white;
    width: 100%;
    border-radius: 6px;
    box-shadow: 0 6px 12px rgba(0,0,0,.08);
}
.autocomplete-item {
    padding: 8px 10px;
    cursor: pointer;
}
.autocomplete-item:hover {
    background: #f1f1f1;
}
</style>

<div class="container mt-4">

    <h4 class="page-title mb-1">
        Daftar Proposal Yang Perlu Direview — Universitas YARSI
    </h4>
    <p class="page-subtitle mb-4">
        Admin menetapkan 2 reviewer, reviewer yang ditugaskan dapat mengisi penilaian.
    </p>

    <div class="table-responsive">
        <table class="table table-striped align-middle shadow-sm">
            <thead>
                <tr>
                    <th width="40">No</th>
                    <th width="260">Reviewer</th>
                    <th>Pengusul</th>
                    <th>Judul Proposal</th>
                    <th width="160">Tenggat Review</th>
                    <th width="140">Aksi</th>
                </tr>
            </thead>


            <tbody>
            @forelse ($proposals as $index => $proposal)
                <tr>
                    <td>{{ $index + 1 }}</td>

                    {{-- ================= REVIEWER ================= --}}
                    <td>
                        @if(auth()->user()->role === 'admin')

                        <form action="{{ route('proposal.assignReviewer', $proposal->id) }}"
                              method="POST"
                              class="position-relative reviewer-form">
                            @csrf

                            {{-- Reviewer 1 --}}
                            <input type="hidden"
                                   name="reviewer_1"
                                   id="rev1_id_{{ $proposal->id }}"
                                   value="{{ $proposal->reviewers->get(0)?->id }}">

                            <input type="text"
                                   class="form-control form-control-sm mb-1 reviewer-search"
                                   placeholder="Cari Reviewer 1"
                                   data-target="rev1"
                                   data-proposal="{{ $proposal->id }}"
                                   autocomplete="off"
                                   value="{{ $proposal->reviewers->get(0)?->name }}">

                            <div class="autocomplete-box d-none"
                                 id="rev1_box_{{ $proposal->id }}"></div>

                            {{-- Reviewer 2 --}}
                            <input type="hidden"
                                   name="reviewer_2"
                                   id="rev2_id_{{ $proposal->id }}"
                                   value="{{ $proposal->reviewers->get(1)?->id }}">

                            <input type="text"
                                   class="form-control form-control-sm mb-1 reviewer-search"
                                   placeholder="Cari Reviewer 2"
                                   data-target="rev2"
                                   data-proposal="{{ $proposal->id }}"
                                   autocomplete="off"
                                   value="{{ $proposal->reviewers->get(1)?->name }}">

                            <div class="autocomplete-box d-none"
                                 id="rev2_box_{{ $proposal->id }}"></div>

                            {{-- ================= TENGGAT REVIEW (KHUSUS ADMIN) ================= --}}
                                <div class="mb-1">
                                    <label class="form-label small text-muted mb-0">
                                        Tenggat Review
                                    </label>
                                    <input type="datetime-local"
                                        name="review_deadline"
                                        class="form-control form-control-sm"
                                        value="{{ $proposal->review_deadline ? \Carbon\Carbon::parse($proposal->review_deadline)->format('Y-m-d\TH:i') : '' }}"
                                        required>
                                </div>


                            <button type="submit"
                                    class="btn btn-outline-primary btn-sm w-100 mt-1">
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

                    <td>{{ $proposal->nama_ketua }}</td>
                    <td>{{ $proposal->judul }}</td>
                    <td>
                        @if($proposal->review_deadline)
                            @php
                                $deadline = \Carbon\Carbon::parse($proposal->review_deadline);
                            @endphp

                            <div class="small">
                                <strong>{{ $deadline->format('d M Y') }}</strong><br>
                                <span class="text-muted">
                                    {{ $deadline->format('H:i') }} WIB
                                </span>

                                {{-- WARNING JIKA MELEWATI DEADLINE --}}
                                @if(now()->gt($deadline))
                                    <div class="text-danger fw-semibold">
                                        ⛔ Lewat Tenggat
                                    </div>
                                @elseif(now()->diffInHours($deadline) <= 24)
                                    <div class="text-warning fw-semibold">
                                        ⚠️ Kurang dari 24 jam
                                    </div>
                                @endif
                            </div>
                        @else
                            <span class="text-muted">Belum ditentukan</span>
                        @endif
                    </td>


                    {{-- ================= AKSI ================= --}}
                    <td>
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
                    <td colspan="5" class="text-center text-muted">
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

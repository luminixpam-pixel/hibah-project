@extends('layouts.app')

@section('content')

<style>
    .page-title {
        font-weight: 700;
        font-size: 22px;
        color: #2d2d2d;
    }

    .table thead th {
        background: #f8f9fa !important;
        font-weight: 600;
    }

    .btn-action {
        padding: 6px 14px;
        border-radius: 6px;
        font-size: 14px;
    }

    .page-subtitle {
        font-size: 15px;
        color: #6c757d;
    }
</style>

<div class="container mt-4">

    {{-- TITLE --}}
    <h4 class="page-title mb-1"> Daftar Proposal Sedang Direview — Universitas YARSI</h4>
    <p class="page-subtitle mb-4">Berikut daftar seluruh proposal yang sedang direview.</p>

    {{-- 🔍 SEARCH --}}
    <div class="d-flex justify-content-end mb-3">
        <div class="input-group" style="max-width: 320px;">
            <span class="input-group-text"><i class="bi bi-search"></i></span>
            <input type="text" id="table-search" class="form-control"
                   placeholder="Cari Judul Proposal atau Nama Dosen">
        </div>
    </div>

    {{-- TABLE --}}
    <div class="table-responsive">
        <table class="table table-striped table-hover align-middle shadow-sm">
            <thead>
                <tr>
                    <th>No</th>
                    <th>Reviewer</th>

                    {{-- ✅ OPSIONAL: Progress --}}
                    <th>Progress</th>

                    <th>Pengusul</th>
                    <th>Judul Proposal</th>
                    <th>Status</th>
                    <th>Tenggat Review</th>
                    <th>Aksi</th>
                </tr>
            </thead>

            <tbody>
                @forelse($proposals as $index => $proposal)
                    @php
                        // ✅ ambil reviewer dari relasi pivot reviewers()
                        $reviewerNames = ($proposal->reviewers ?? collect())->pluck('name')->implode(', ');
                        $isAssignedReviewer = ($proposal->reviewers ?? collect())->pluck('id')->contains(auth()->id());

                        // ✅ OPSIONAL: hitung progress review masuk
                        $totalReviewer = ($proposal->reviewers ?? collect())->count();
                        $doneReviewer = ($proposal->reviews ?? collect())->pluck('reviewer_id')->unique()->count();
                    @endphp

                    <tr>
                        <td>{{ $index + 1 }}</td>

                        {{-- ✅ tampilkan reviewer dari pivot --}}
                        <td>{{ $reviewerNames ?: '-' }}</td>

                        {{-- ✅ OPSIONAL: Progress --}}
                        <td>
                            <span class="badge bg-info">
                                {{ $doneReviewer }}/{{ $totalReviewer }}
                            </span>
                        </td>

                        <td>{{ $proposal->nama_ketua }}</td>
                        <td>{{ $proposal->judul }}</td>
                        <td>
                            <span class="badge bg-success">Sedang Direview</span>
                        </td>
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

                                    {{-- indikator --}}
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


                        <td>
                            @if(auth()->user()->role === 'reviewer')
                                @if($isAssignedReviewer)
                                    {{-- tombol khusus reviewer yang ditugaskan --}}
                                    <a href="{{ route('reviewer.isi-review', $proposal->id) }}"
                                       class="btn btn-success btn-sm">
                                        Beri Review
                                    </a>
                                @else
                                    <span class="text-muted">Bukan reviewer proposal ini</span>
                                @endif
                            @else
                                {{-- misalnya admin, kalau mau bisa dikasih tombol Detail di sini --}}
                                -
                            @endif
                        </td>
                    </tr>
                @empty
                    <tr>
                       <td colspan="8" class="text-center text-muted py-3">
                            Belum ada proposal yang sedang direview.
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    {{-- 🔁 PREV / NEXT --}}
    <div class="d-flex justify-content-between mt-3">
        <a href="{{ route('monitoring.proposalPerluDireview') }}"
           class="btn btn-outline-success btn-sm">
            &laquo; Proposal Perlu Direview
        </a>

        <a href="{{ route('monitoring.reviewSelesai') }}"
           class="btn btn-outline-success btn-sm">
            Review Selesai &raquo;
        </a>
    </div>

</div>

@endsection

@push('scripts')
<script>
document.addEventListener("DOMContentLoaded", function () {
    const searchInput = document.getElementById('table-search');
    if (!searchInput) return;

    const rows = document.querySelectorAll('table tbody tr');

    searchInput.addEventListener('keyup', function () {
        const term = this.value.toLowerCase();

        rows.forEach(row => {
            row.style.display = row.innerText.toLowerCase().includes(term) ? '' : 'none';
        });
    });
});
</script>
@endpush

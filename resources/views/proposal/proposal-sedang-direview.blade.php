@extends('layouts.app')

@section('content')

<style>
    .page-title { font-weight: 700; font-size: 22px; color: #2d2d2d; }
    .table thead th { background: #f8f9fa !important; font-weight: 600; }
    .page-subtitle { font-size: 15px; color: #6c757d; }
    .badge-reviewer { background-color: #e9ecef; color: #495057; border: 1px solid #ced4da; margin-bottom: 2px; display: block; text-align: left; }
</style>

<div class="container mt-4">
    <h4 class="page-title mb-1"> Daftar Proposal Sedang Direview — Universitas YARSI</h4>
    <p class="page-subtitle mb-4">Berikut daftar seluruh proposal yang sedang proses penilaian.</p>

    <div class="d-flex justify-content-end mb-3">
        <div class="input-group" style="max-width: 320px;">
            <span class="input-group-text"><i class="bi bi-search"></i></span>
            <input type="text" id="table-search" class="form-control" placeholder="Cari Judul atau Nama...">
        </div>
    </div>

    <div class="table-responsive">
        <table class="table table-striped table-hover align-middle shadow-sm">
            <thead>
                <tr>
                    <th>No</th>
                    <th>Reviewer</th>
                    <th>Progress</th>
                    <th>Pengusul</th>
                    <th>Judul Proposal</th>
                    <th>Tenggat</th>
                    <th>Aksi</th>
                </tr>
            </thead>
            <tbody>
                @forelse($proposals as $index => $proposal)
                    @php
                        // PERBAIKAN: Mengambil ID asli (integer) dari database, bukan identifier username
                        $user = auth()->user();
                        $currentUserId = $user->getAttributes()['id'] ?? $user->id;

                        $reviewers = $proposal->reviewers ?? collect();

                        // Konversi semua ID ke string untuk perbandingan "Loose" yang lebih aman
                        $assignedIds = $reviewers->pluck('id')->map(fn($id) => (string)$id)->toArray();
                        $isAssignedReviewer = in_array((string)$currentUserId, $assignedIds);

                        $totalReviewer = $reviewers->count();
                        $doneReviewer = ($proposal->reviews ?? collect())->pluck('reviewer_id')->unique()->count();
                        $deadline = $proposal->review_deadline ? \Carbon\Carbon::parse($proposal->review_deadline) : null;
                    @endphp

                    <tr>
                        <td>{{ $index + 1 }}</td>
                        <td>
                            @foreach($reviewers as $rev)
                                <span class="badge badge-reviewer small">
                                    <i class="bi bi-person"></i> {{ $rev->name }}
                                </span>
                            @endforeach
                        </td>
                        <td>
                            <span class="badge bg-info">{{ $doneReviewer }}/{{ $totalReviewer }}</span>
                        </td>
                        <td>{{ $proposal->nama_ketua }}</td>
                        <td>{{ $proposal->judul }}</td>
                        <td>
                            @if($deadline)
                                <div class="small">
                                    <strong>{{ $deadline->format('d M Y') }}</strong><br>
                                    <span class="text-muted">{{ $deadline->format('H:i') }}</span>
                                </div>
                            @else
                                -
                            @endif
                        </td>
                        <td class="text-center">
                            @if(auth()->user()->role === 'reviewer')
                                @if($isAssignedReviewer)
                                    <a href="{{ route('reviewer.isi-review', $proposal->id) }}"
                                       class="btn btn-success btn-sm w-100 shadow-sm">
                                        <i class="bi bi-pencil-square"></i> Beri Review
                                    </a>
                                @else
                                    <span class="text-muted x-small italic d-block">Bukan tugas Anda</span>
                                    {{-- Baris debug ini bisa dihapus jika tombol sudah muncul --}}
                                    <div style="font-size: 8px; color: red;">
                                        ID: {{ $currentUserId }} | List: {{ implode(',', $assignedIds) }}
                                    </div>
                                @endif
                            @else
                                <span class="badge bg-light text-dark border">Admin</span>
                            @endif
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="7" class="text-center py-4">Tidak ada data proposal sedang direview.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>

@endsection

@push('scripts')
<script>
    document.getElementById('table-search')?.addEventListener('keyup', function() {
        let term = this.value.toLowerCase();
        document.querySelectorAll('tbody tr').forEach(row => {
            row.style.display = row.innerText.toLowerCase().includes(term) ? '' : 'none';
        });
    });
</script>
@endpush

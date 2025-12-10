@extends('layouts.app')
@php
    $role = Auth::user()->role ?? null;
@endphp


@section('content')
<div class="container mt-4">

    {{-- NOTIFIKASI ERROR VALIDASI --}}
    @if($errors->any())
        <div class="alert alert-danger alert-dismissible fade show mt-3" role="alert">
            <ul class="mb-0">
                @foreach($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    {{-- 🔍 SEARCH DI KANAN ATAS --}}
    <div class="d-flex justify-content-end mb-3">
        <div class="input-group" style="max-width: 320px;">
            <span class="input-group-text">
                <i class="bi bi-search"></i>
            </span>
            <input type="text" id="table-search" class="form-control"
                   placeholder="Cari Judul Proposal atau Nama Dosen">
        </div>
    </div>

    {{-- TABEL DAFTAR PROPOSAL --}}
    <div class="table-responsive mt-4">
        <table class="table table-bordered align-middle">
            <thead class="table-light">
                <tr>
                    <th>No</th>
                    <th>Judul Proposal</th>
                    <th>Pengusul</th>
                    <th>Aksi</th>
                </tr>
            </thead>

            <tbody>
                @forelse ($proposals as $index => $proposal)
                <tr>
                    <td>{{ $index + 1 }}</td>
                    <td>{{ $proposal->judul }}</td>
                    <td>{{ $proposal->nama_ketua }}</td>

                    <td>
                        <div class="d-flex gap-2">

                            {{-- Download file (kode LAMA, tidak diubah) --}}
                            @if ($proposal->file_path)
                                <a href="{{ route('proposal.download', $proposal->id) }}"
                                   class="btn btn-sm btn-outline-primary"
                                   download>
                                    Download
                                </a>
                            @else
                                <span class="text-muted">No File</span>
                            @endif

                            {{-- ➕ TINJAU PROPOSAL (halaman detail) --}}
                            <a href="{{ url('/proposal/'.$proposal->id.'/tinjau') }}"
                               class="btn btn-sm btn-secondary">
                                Tinjau
                            </a>

                            {{-- ➕ EDIT PROPOSAL --}}
                            @if (Auth::id() === $proposal->user_id && $proposal->status === 'Dikirim')
                                <a href="{{ url('/proposal/'.$proposal->id.'/edit') }}"
                                   class="btn btn-sm btn-warning">
                                    Edit
                                </a>
                            @endif

                            {{-- Tombol kirim ke Perlu Direview (ADMIN + REVIEWER saja – kode LAMA) --}}
                            @if (in_array($role, ['admin', 'reviewer']) && $proposal->status === 'Dikirim')
                                <form action="{{ route('proposal.moveToPerluDireview', $proposal->id) }}"
                                      method="POST"
                                      onsubmit="return confirm('Pindahkan proposal ini ke status \"Perlu Direview\"?');">
                                    @csrf
                                    @method('PATCH')
                                    <button type="submit" class="btn btn-sm btn-success">
                                        Kirim ke Perlu direview
                                    </button>
                                </form>
                            @endif

                        </div>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="4" class="text-center text-muted">Belum ada proposal yang dikirim.</td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    {{-- 🔁 NEXT DI BAWAH TABEL (KANAN SAJA) --}}
    <div class="d-flex justify-content-end mt-3">
        @if($role === 'pengaju')
            {{-- Pengaju: lompat langsung ke Review Selesai --}}
            <a href="{{ route('monitoring.reviewSelesai') }}"
               class="btn btn-outline-success btn-sm">
                Review Selesai &raquo;
            </a>
        @else
            {{-- Admin / Reviewer: tetap ke Proposal Perlu Direview --}}
            <a href="{{ route('monitoring.proposalPerluDireview') }}"
               class="btn btn-outline-success btn-sm">
                Proposal Perlu Direview &raquo;
            </a>
        @endif
    </div>

</div>

{{-- Script Auto-Close Alert 2,5 detik --}}
<script>
document.addEventListener("DOMContentLoaded", function () {
    const alerts = document.querySelectorAll('.alert');
    alerts.forEach(alert => {
        setTimeout(() => {
            const bsAlert = new bootstrap.Alert(alert);
            bsAlert.close();
        }, 2500);
    });
});
</script>
@endsection

@push('scripts')
<script>
document.addEventListener("DOMContentLoaded", function () {
    const toastSuccess = document.getElementById("toastSuccess");

    if (toastSuccess) {
        let toast = new bootstrap.Toast(toastSuccess, { delay: 3000 });
        toast.show();
    }
});
</script>
@endpush

@push('scripts')
{{-- 🔍 SCRIPT FILTER TABEL --}}
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

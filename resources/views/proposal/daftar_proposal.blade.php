@extends('layouts.app')

@php
    $role = Auth::user()->role ?? null;
@endphp

@section('content')
<div class="container mt-4">

    {{-- ================= NOTIFIKASI ERROR ================= --}}
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

    {{-- ================= JUDUL ================= --}}
    <h4 class="mb-3">Monitoring Proposal Hibah Internal</h4>

    {{-- ================= SEARCH ================= --}}
    <div class="d-flex justify-content-end mb-3">
        <div class="input-group" style="max-width: 350px;">
            <span class="input-group-text">
                <i class="bi bi-search"></i>
            </span>
            <input type="text" id="table-search" class="form-control"
                   placeholder="Cari judul, pengusul, atau status">
        </div>
    </div>

    {{-- ================= TABEL ================= --}}
    <div class="table-responsive">
        <table class="table table-bordered table-hover align-middle">
            <thead class="table-light">
                <tr class="text-center">
                    <th width="5%">No</th>
                    <th>Judul Proposal</th>
                    <th>Pengusul</th>
                    <th>Status</th>
                    <th width="25%">Aksi</th>
                </tr>
            </thead>

            <tbody>
            @forelse ($proposals as $index => $proposal)
                <tr>
                    <td class="text-center">{{ $index + 1 }}</td>
                    <td>{{ $proposal->judul }}</td>
                    <td>{{ $proposal->nama_ketua }}</td>

                    {{-- ===== STATUS ===== --}}
                    <td class="text-center">
                        @php
                            $statusClass = match($proposal->status) {
                                'Dikirim' => 'secondary',
                                'Perlu Direview' => 'warning',
                                'Direview' => 'info',
                                'Review Selesai' => 'success',
                                'Ditolak' => 'danger',
                                default => 'dark'
                            };
                        @endphp
                        <span class="badge bg-{{ $statusClass }}">
                            {{ $proposal->status }}
                        </span>
                    </td>

                    {{-- ===== AKSI ===== --}}
                    <td>
                        <div class="d-flex flex-wrap gap-2 justify-content-center">

                            {{-- Download --}}
                            @if ($proposal->file_path)
                                <a href="{{ route('proposal.download', $proposal->id) }}"
                                   class="btn btn-sm btn-outline-primary">
                                    Download
                                </a>
                            @else
                                <span class="text-muted small">No File</span>
                            @endif

                            {{-- Tinjau --}}
                            <a href="{{ url('/proposal/'.$proposal->id.'/tinjau') }}"
                               class="btn btn-sm btn-secondary">
                                Tinjau
                            </a>

                            {{-- Edit (hanya pengaju & status dikirim) --}}
                            @if (Auth::id() === $proposal->user_id && $proposal->status === 'Dikirim')
                                <a href="{{ url('/proposal/'.$proposal->id.'/edit') }}"
                                   class="btn btn-sm btn-warning">
                                    Edit
                                </a>
                            @endif

                            {{-- Kirim ke Perlu Direview (Admin / Reviewer) --}}
                            @if (in_array($role, ['admin','reviewer']) && $proposal->status === 'Dikirim')
                                <form action="{{ route('proposal.moveToPerluDireview', $proposal->id) }}"
                                      method="POST"
                                      onsubmit="return confirm('Pindahkan proposal ke status Perlu Direview?');">
                                    @csrf
                                    @method('PATCH')
                                    <button class="btn btn-sm btn-success">
                                        Kirim Review
                                    </button>
                                </form>
                            @endif

                        </div>
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="5" class="text-center text-muted">
                        Belum ada proposal
                    </td>
                </tr>
            @endforelse
            </tbody>
        </table>
    </div>

</div>
@endsection

{{-- ================= SCRIPT ================= --}}
@push('scripts')

{{-- Auto close alert --}}
<script>
document.addEventListener("DOMContentLoaded", function () {
    document.querySelectorAll('.alert').forEach(alert => {
        setTimeout(() => {
            new bootstrap.Alert(alert).close();
        }, 2500);
    });
});
</script>

{{-- Search filter --}}
<script>
document.addEventListener("DOMContentLoaded", function () {
    const searchInput = document.getElementById('table-search');
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

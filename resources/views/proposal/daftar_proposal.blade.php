@extends('layouts.app')

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

    {{-- TOAST SUKSES — kecil & auto hide 3 detik --}}
    @if (session('success'))
        <div id="toastSuccess"
             class="toast align-items-center text-white bg-success border-0 position-fixed"
             style="top: 20px; right: 20px; z-index: 9999;"
             role="alert">

            <div class="d-flex">
                <div class="toast-body">
                    {{ session('success') }}
                </div>
            </div>
        </div>
    @endif

    @php
        // role user login
        $role = Auth::user()->role ?? null;
    @endphp

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

                            {{-- Download file --}}
                            @if ($proposal->file_path)
                                <a href="{{ route('proposal.download', $proposal->id) }}"
                                   class="btn btn-sm btn-outline-primary"
                                   download>
                                    Download
                                </a>
                            @else
                                <span class="text-muted">No File</span>
                            @endif

                            {{-- Tombol kirim ke Perlu Direview (ADMIN + REVIEWER saja) --}}
                            @if (in_array($role, ['admin', 'reviewer']) && $proposal->status === 'Dikirim')
                                <form action="{{ route('proposal.moveToPerluDireview', $proposal->id) }}"
                                      method="POST"
                                      onsubmit="return confirm('Pindahkan proposal ini ke status \"Perlu Direview\"?');">
                                    @csrf
                                    @method('PATCH')
                                    <button type="submit" class="btn btn-sm btn-success">
                                        Kirim ke Perlu Direview
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

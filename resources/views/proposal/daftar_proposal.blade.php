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

    {{-- Form Upload Proposal --}}
    <div class="card mt-4">
        <div class="card-body">
            <form action="{{ route('proposal.store') }}" method="POST" enctype="multipart/form-data">
                @csrf
                <div class="mb-3">
                    <label for="judul" class="form-label">Judul Proposal</label>
                    <input type="text" class="form-control" id="judul" name="judul" value="{{ old('judul') }}" required>
                </div>
                <div class="mb-3">
                    <label for="nama_ketua" class="form-label">Nama Ketua</label>
                    <input type="text" class="form-control" id="nama_ketua" name="nama_ketua" value="{{ old('nama_ketua') }}" required>
                </div>
                <div class="mb-3">
                    <label for="file" class="form-label">Upload File (PDF/DOC/DOCX)</label>
                    <input type="file" class="form-control" id="file" name="file" required>
                </div>
                <button type="submit" class="btn btn-success">Kirim Proposal</button>
            </form>
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

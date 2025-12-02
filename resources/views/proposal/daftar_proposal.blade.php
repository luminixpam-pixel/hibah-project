@extends('layouts.app')

@section('content')

<div class="container mt-4">

    <h4 class="fw-bold">Daftar Proposal Hibah Internal Universitas YARSI</h4>

    <div class="card mt-4">
        <div class="card-body">
            <div class="row">

                <div class="col-md-6">
                    <p class="fw-semibold text-success">Filter Berdasarkan:</p>

                    <label>Periode Hibah</label>
                    <select class="form-select mb-3">
                        <option>Semester Genap 2025 / Tahun 2025</option>
                        <option>Semester Ganjil 2025</option>
                    </select>
                </div>

                <div class="col-md-6">
                    <p class="fw-semibold text-success">Filter Berdasarkan:</p>

                    <div class="form-check">
                        <input class="form-check-input" type="radio" name="filter" checked>
                        <label class="form-check-label">Fakultas</label>
                    </div>

                    <div class="form-check">
                        <input class="form-check-input" type="radio" name="filter">
                        <label class="form-check-label">Prodi</label>
                    </div>

                    <button class="btn btn-success mt-3 px-4">Cari</button>
                </div>

            </div>
        </div>
    </div>

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
        $role = Auth::user()->role ?? null;
    @endphp

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
                                   class="btn btn-sm btn-outline-primary">
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
                    <td colspan="6" class="text-center text-muted">Belum ada proposal yang dikirim.</td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>

</div>

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

{{-- Baris ini harus menjadi baris pertama di file --}}
@extends(isset($is_pdf) && $is_pdf ? 'layouts.blank' : 'layouts.app')

@section('content')
<style>
    /* Tambahkan style ini agar tabel terlihat bagus di PDF */
    .table { width: 100%; border-collapse: collapse; margin-top: 20px; font-family: sans-serif; }
    .table th, .table td { border: 1px solid #333; padding: 10px; font-size: 12px; }
    .table-success { background-color: #d1e7dd; }
    .text-center { text-align: center; }
    .kop-surat { text-align: center; margin-bottom: 30px; border-bottom: 2px solid #000; padding-bottom: 10px; }
    .badge { padding: 4px 8px; border-radius: 4px; color: white; font-size: 10px; }
    .bg-success { background-color: #198754; }
    .bg-primary { background-color: #0d6efd; }
    .bg-warning { background-color: #ffc107; color: black; }
</style>

<div class="container">
    @if(isset($is_pdf) && $is_pdf)
        <div class="kop-surat">
            <h2 style="margin:0;">UNIVERSITAS YARSI</h2>
            <p style="margin:5px 0;">Lembaga Penelitian dan Pengabdian Kepada Masyarakat (LPPM)</p>
            <h3 style="margin:0;">HASIL PENILAIAN REVIEW PROPOSAL</h3>
        </div>
    @else
        <h4 class="fw-bold mb-3">Hasil Review Proposal</h4>
    @endif

    <table class="table table-bordered">
        <thead class="table-success text-center">
            <tr>
                <th>Reviewer</th>
                <th>Judul Proposal</th>
                <th>Total Score</th>
                <th>Kategori</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($reviews as $r)
            <tr>
                <td>{{ $r->reviewer->name }}</td>
                <td>{{ $r->proposal->judul }}</td>
                <td class="text-center" style="font-weight:bold;">{{ $r->total_score }}</td>
                <td class="text-center">
                    @php
                        $score = $r->total_score;
                        if($score >= 401) { $kat = 'Baik Sekali'; $cls = 'bg-success'; }
                        elseif($score >= 301) { $kat = 'Baik'; $cls = 'bg-primary'; }
                        else { $kat = 'Cukup'; $cls = 'bg-warning'; }
                    @endphp
                    <span class="badge {{ $cls }}">{{ $kat }}</span>
                </td>
            </tr>
            @endforeach
        </tbody>
    </table>
</div>
@endsection

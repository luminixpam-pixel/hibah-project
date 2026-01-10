@extends(isset($is_pdf) && $is_pdf ? 'layouts.blank' : 'layouts.app')

@section('content')
<style>
    .table { width: 100%; border-collapse: collapse; margin-top: 15px; font-family: sans-serif; }
    .table th, .table td { border: 1px solid #333; padding: 8px; font-size: 11px; }
    .table-success { background-color: #f2f2f2; }
    .text-center { text-align: center; }
    .text-start { text-align: left; }
    .kop-surat { text-align: center; margin-bottom: 20px; border-bottom: 2px solid #000; padding-bottom: 10px; }
    .badge { padding: 4px 8px; border-radius: 4px; color: white; font-size: 10px; text-transform: uppercase; border: none; }
    .bg-success { background-color: #198754; }
    .bg-primary { background-color: #0d6efd; }
    .bg-warning { background-color: #ffc107; color: black; }
</style>

<div class="container">
    @if(isset($is_pdf) && $is_pdf)
        <div class="kop-surat">
            <h2 style="margin:0; font-size: 18px;">UNIVERSITAS YARSI</h2>
            <p style="margin:5px 0;">Lembaga Penelitian dan Pengabdian Kepada Masyarakat (LPPM)</p>
            <h3 style="margin:0; font-size: 14px;">LEMBAR HASIL PENILAIAN PROPOSAL</h3>
        </div>
    @endif

    @foreach ($reviews as $r)
        <div style="margin-bottom: 10px; font-size: 12px;">
            <strong>Judul Proposal:</strong> {{ $r->proposal->judul }} <br>
            <strong>Nama Pengusul:</strong> {{ $r->proposal->nama_ketua }} <br>
            <strong>Reviewer:</strong> {{ $r->reviewer->name }}
        </div>

        <table class="table">
            <thead class="table-success text-center">
                <tr>
                    <th width="5%">No</th>
                    <th width="50%">Komponen Penilaian</th>
                    <th width="10%">Bobot</th>
                    <th width="15%">Skor (0-5)</th>
                    <th width="20%">Nilai (B x S)</th>
                </tr>
            </thead>
            <tbody>
                @php
                    $components = ['Kesesuaian Judul', 'Tingkat Plagiasi', 'Pendahuluan', 'Rumusan Masalah', 'Target Luaran', 'Kebaruan Ide', 'Kelayakan Metode'];
                    $bobots = [5, 5, 5, 3, 5, 5, 10];
                @endphp
                @foreach($components as $index => $comp)
                    @php
                        $field = 'nilai_' . ($index + 1);
                        $skor = $r->$field;
                        $nilai = $skor * $bobots[$index];
                    @endphp
                    <tr>
                        <td class="text-center">{{ $index + 1 }}</td>
                        <td class="text-start">{{ $comp }}</td>
                        <td class="text-center">{{ $bobots[$index] }}</td>
                        <td class="text-center">{{ $skor }}</td>
                        <td class="text-center">{{ $nilai }}</td>
                    </tr>
                @endforeach
                <tr style="background-color: #f9f9f9; font-weight: bold;">
                    <td colspan="4" class="text-start">TOTAL NILAI AKHIR (NORMALISASI 0-500)</td>
                    <td class="text-center" style="font-size: 14px;">{{ $r->total_score }}</td>
                </tr>
            </tbody>
        </table>

        <div style="margin-top: 10px; font-size: 12px;">
            <strong>Hasil Klasifikasi:</strong>
            @php
                $score = $r->total_score;
                if($score >= 401) { $kat = 'Baik Sekali'; $cls = 'bg-success'; }
                elseif($score >= 301) { $kat = 'Baik'; $cls = 'bg-primary'; }
                elseif($score >= 201) { $kat = 'Sedang'; $cls = 'bg-warning'; }
                else { $kat = 'Kurang'; $cls = 'bg-warning'; }
            @endphp
            <span class="badge {{ $cls }}">{{ $kat }}</span>
        </div>

        @if($r->catatan)
            <div style="margin-top: 15px; border: 1px solid #ddd; padding: 10px; font-size: 11px;">
                <strong>Catatan Reviewer:</strong><br>
                <em>{{ $r->catatan }}</em>
            </div>
        @endif

        @if(isset($is_pdf) && $is_pdf)
            <div style="margin-top: 30px; text-align: right; font-size: 12px;">
                Jakarta, {{ date('d F Y') }} <br>
                Reviewer, <br><br><br><br>
                <strong>({{ $r->reviewer->name }})</strong>
            </div>
        @endif
    @endforeach
</div>
@endsection

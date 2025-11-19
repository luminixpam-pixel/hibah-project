@extends('layouts.app')

@section('content')
<div class="container mt-4">

    {{-- Breadcrumb / Judul --}}
    <h4 class="fw-bold mb-1">Isi Review dan Penilaian</h4>
    <p class="text-muted" style="margin-top:-6px;">
        Reviewer Peneliti: Prof.dr. Pratiwi Pujilestari Sudarmono, Ph.D, Sp.M.K (K)
    </p>

    {{-- Tombol Download --}}
    <div class="text-end mb-3">
        <a class="btn btn-success btn-sm">
            <i class="bi bi-download"></i> Download Proposal
        </a>
    </div>

    {{-- TABEL PENILAIAN --}}
    <div class="table-responsive">
        <table class="table table-bordered align-middle text-center">
            <thead class="table-success">
                <tr>
                    <th>KOMPONEN</th>
                    <th>BOBOT</th>
                    <th>NILAI</th>
                    <th>SCORE</th>
                </tr>
            </thead>

            <tbody>
                <tr>
                    <td class="text-start">
                        Pengaruh Faktor Demografi, Locus Of Control, Need For Achievement, Literasi Keuangan, Dan Inklusi Keuangan Terhadap Kinerja Keuangan UMkm Di Kota Malang
                    </td>
                    <td>5</td>
                    <td>-</td>
                    <td>-</td>
                </tr>

                <tr>
                    <td class="text-start">
                        Belum ada penjelasan tentang Kenapa memilih rumusan masalah tersebut? karena rumusan masalah tidak fokus maka dalam pendahuluan tampak tidak fokus.
                    </td>
                    <td>5</td>
                    <td>-</td>
                    <td>-</td>
                </tr>

                <tr>
                    <td class="text-start">
                        Belum ada penjelasan tentang Kenapa memilih rumusan masalah tersebut? karena rumusan masalah tidak fokus maka dalam pendahuluan tampak tidak fokus.
                    </td>
                    <td>5</td>
                    <td>-</td>
                    <td>-</td>
                </tr>

                <tr>
                    <td class="text-start">
                        Rumusan masalah tidak fokus, hemat saya cukup nomer satu saja. Dan variabelnya juga independennya (demografi) juga belum jelas, diantara sekian variabel yang disebutkan.
                    </td>
                    <td>3</td>
                    <td>-</td>
                    <td>-</td>
                </tr>

                <tr>
                    <td class="text-start">sesuaikan dengan rumusan masalah</td>
                    <td>5</td>
                    <td>-</td>
                    <td>-</td>
                </tr>

                <tr>
                    <td class="text-start">
                        Kebaruan itu terlihat ditelaah pustaka. Dan belum ditemukan disini. kerangka teori masih berupa penjelasan konsep. Belum jelas apakah riset ini mau meneguhkan...
                    </td>
                    <td>5</td>
                    <td>-</td>
                    <td>-</td>
                </tr>

                <tr>
                    <td class="text-start">
                        1. teknis sampling nya belum ada.<br>
                        2. peta konsep riset juga belum tampak
                    </td>
                    <td>10</td>
                    <td>-</td>
                    <td>-</td>
                </tr>
            </tbody>
        </table>
    </div>

    {{-- CATATAN --}}
    <div class="mt-3 p-3 rounded" style="background:#f2fff1; border:1px solid #cfeccc;">
        <strong>Catatan:</strong><br>
        Skor: 0, 1, 2, 3, 4, 5<br>
        Nilai = Bobot × Skor
        <br><br>
        <strong>Rentang Nilai:</strong><br>
        • Baik Sekali: 401 - 500<br>
        • Baik: 301 - 400<br>
        • Sedang: 201 - 300<br>
        • Kurang: 101 - 200<br>
        • Sangat Kurang: 0 - 90
    </div>

    {{-- TOMBOL DOWNLOAD --}}
    <div class="text-end mt-3">
        <button class="btn btn-success">
            <i class="bi bi-download"></i> Download
        </button>
    </div>

    {{-- TOMBOL NAVIGASI --}}
    <div class="d-flex justify-content-between my-4">
        <a href="#" class="btn btn-light border">
            <i class="bi bi-chevron-left"></i> Hasil Review
        </a>

        <a href="#" class="btn btn-light border">
            <i class="bi bi-chevron-left"></i> Sedang Direview
        </a>
    </div>

</div>
@endsection

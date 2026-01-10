<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>asil Penilaian Proposal</title>

    <style>
        body {
            font-family: DejaVu Sans, sans-serif;
            font-size: 12px;
            color: #000;
            line-height: 1.5;
        }

        .header {
            text-align: center;
            border-bottom: 2px solid #000;
            padding-bottom: 10px;
            margin-bottom: 20px;
        }

        .header h2 {
            margin: 0;
            font-size: 18px;
        }

        .header p {
            margin: 4px 0;
            font-size: 12px;
        }

        .section {
            margin-bottom: 18px;
        }

        .section-title {
            font-weight: bold;
            margin-bottom: 6px;
            text-transform: uppercase;
            font-size: 13px;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 8px;
        }

        table th, table td {
            border: 1px solid #000;
            padding: 6px;
            font-size: 11px;
        }

        table th {
            background-color: #f2f2f2;
            text-align: center;
        }

        .text-center {
            text-align: center;
        }

        .signature {
            margin-top: 40px;
            width: 100%;
        }

        .signature td {
            border: none;
            padding-top: 30px;
            text-align: center;
        }

        .footer {
            position: fixed;
            bottom: 20px;
            width: 100%;
            text-align: center;
            font-size: 10px;
            color: #555;
        }
    </style>
</head>
<body>

    {{-- HEADER --}}
    <div class="header">
        <h2>HASIL PENILAIAN PROPOSAL PENELITIAN</h2>
        <p>Universitas YARSI</p>
    </div>

    {{-- DATA PROPOSAL --}}
    <div class="section">
        <div class="section-title">Data Proposal</div>
        <table>
            <tr>
                <td width="30%">Judul Proposal</td>
                <td>{{ $proposal->judul ?? '-' }}</td>
            </tr>
            <tr>
                <td>Nama Pengusul</td>
                <td>{{ $proposal->nama_ketua ?? '-' }}</td>
            </tr>
            <tr>
                <td>Status Proposal</td>
                <td>{{ $proposal->status ?? '-' }}</td>
            </tr>
            <tr>
                <td>Reviewer</td>
                <td>{{ $review->reviewer_nama ?? '-' }}</td>
            </tr>
            <tr>
                <td>Tanggal Review</td>
                <td>{{ \Carbon\Carbon::parse($review->created_at)->format('d F Y') }}</td>
            </tr>
        </table>
    </div>

    {{-- PENILAIAN --}}
    <div class="section">
        <div class="section-title">Penilaian Proposal</div>

        <table>
            <thead>
                <tr>
                    <th width="5%">No</th>
                    <th>Kriteria Penilaian</th>
                    <th width="15%">Skor</th>
                </tr>
            </thead>
            <tbody>
                @foreach($penilaian as $i => $item)
                <tr>
                    <td class="text-center">{{ $i + 1 }}</td>
                    <td>{{ $item['kriteria'] }}</td>
                    <td class="text-center">{{ $item['nilai'] }}</td>
                </tr>
                @endforeach

                <tr>
                    <th colspan="2" class="text-center">Total Skor</th>
                    <th class="text-center">{{ $review->total_score ?? '-' }}</th>
                </tr>
            </tbody>
        </table>
    </div>

    {{-- CATATAN --}}
    <div class="section">
        <div class="section-title">Catatan Reviewer</div>
        <table>
            <tr>
                <td style="min-height: 80px;">
                    {{ $review->catatan ?? 'Tidak ada catatan.' }}
                </td>
            </tr>
        </table>
    </div>

    {{-- TANDA TANGAN --}}
    <table class="signature">
        <tr>
            <td width="50%">
                Reviewer,
                <br><br><br><br>
                <strong>{{ $review->reviewer_nama ?? '-' }}</strong>
            </td>
            <td width="50%">
                Mengetahui,
                <br>Admin LPPM
                <br><br><br><br>
                <strong>Universitas YARSI</strong>
            </td>
        </tr>
    </table>

    {{-- FOOTER --}}
    <div class="footer">
        Dokumen ini dihasilkan secara otomatis oleh Sistem Hibah Universitas YARSI
    </div>

</body>
</html>

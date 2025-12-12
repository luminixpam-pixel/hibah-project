@extends('layouts.app')

@section('content')
<div class="container mt-4">

    {{-- Breadcrumb / Judul --}}
    <h4 class="fw-bold mb-1">Isi Review dan Penilaian</h4>
    <p class="text-muted" style="margin-top:-6px;">
        Reviewer: {{ auth()->user()->name }} <br>
        Pengusul: <strong>{{ $proposal->nama_ketua }}</strong> <br>
        Judul Proposal: <strong>{{ $proposal->judul }}</strong>
    </p>

    {{-- Tombol Download --}}
    <div class="text-end mb-3">
        <a href="{{ route('proposal.download', $proposal->id) }}" class="btn btn-success btn-sm">
            <i class="bi bi-download"></i> Download Proposal
        </a>
    </div>

    {{-- FORM REVIEW --}}
    <form action="{{ route('review.simpan', $proposal->id) }}" method="POST">
        @csrf

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
                    @php
                        $components = [
                            'Judul',
                            'Plagiasi',
                            'Pendahuluan',
                            'Rumusan Masalah',
                            'Outcome',
                            'Kebaruan Ide',
                            'Metode'
                        ];
                        $bobot = [5, 5, 5, 3, 5, 5, 10];
                    @endphp

                    @foreach($components as $index => $comp)
                    <tr>
                        <td class="text-start">{{ $comp }}</td>
                        <td>{{ $bobot[$index] }}</td>
                        <td>
    <input type="number" name="nilai_{{ $index + 1 }}"
           class="form-control form-control-sm"
           min="0" max="5" step="1"
           oninput="if(this.value > 5) this.value=5; if(this.value < 0) this.value=0;">
</td>

                        <td id="score_{{ $index + 1 }}">-</td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

        {{-- KOMENTAR --}}
        <div class="mt-3">
            <label for="komentar" class="form-label"><strong>Komentar Reviewer:</strong></label>
            <textarea name="komentar" id="komentar" rows="4" class="form-control" placeholder="Tulis komentar..."></textarea>
        </div>

        {{-- TOTAL NILAI --}}
        <div class="mt-3 p-3 rounded" style="background:#e6ffe6; border:1px solid #c8e8c8;">
            <h5 class="fw-bold">Total Nilai: <span id="total_nilai">0</span></h5>
        </div>

        {{-- CATATAN UMUM --}}
        <div class="mt-3 p-3 rounded" style="background:#f2fff1; border:1px solid #cfeccc;">
            <strong>Catatan:</strong><br>
            Skor: 0–5<br>
            Nilai = Bobot × Skor
            <br><br>
            <strong>Rentang Nilai:</strong><br>
            • Baik Sekali: 401 - 500 <br>
            • Baik: 301 - 400 <br>
            • Sedang: 201 - 300 <br>
            • Kurang: 101 - 200 <br>
            • Sangat Kurang: 0 - 90
        </div>

        {{-- TOMBOL SUBMIT --}}
        <div class="text-end mt-3">
            <button type="submit" class="btn btn-primary">
                <i class="bi bi-save"></i> Simpan Review
            </button>
        </div>
    </form>

    {{-- TOMBOL NAVIGASI --}}
    <div class="d-flex justify-content-between my-4">
        <a href="#" class="btn btn-light border">
            <i class="bi bi-chevron-left"></i> Hasil Review
        </a>

        <a href="#" class="btn btn-light border">
            <i class="bi bi-chevron-left"></i> Sedang Direview
        </a>
    </div>

    {{-- SCRIPT PERHITUNGAN --}}
    <script>
        const bobot = @json($bobot);

        function hitung() {
            let total = 0;

            for (let i = 1; i <= bobot.length; i++) {
                let nilai = parseInt(document.querySelector(`input[name=nilai_${i}]`).value) || 0;
                let score = nilai * bobot[i - 1];

                document.getElementById("score_" + i).innerText = score;
                total += score;
            }

            document.getElementById("total_nilai").innerText = total;
        }

        document.querySelectorAll("input[type=number]").forEach(el => {
            el.addEventListener("input", hitung);
        });
    </script>

</div>
@endsection

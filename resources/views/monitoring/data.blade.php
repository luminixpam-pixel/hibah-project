@extends('layouts.app')

@section('title', 'Monitoring & Data')

@section('content')

<h4 class="fw-bold mb-4">Kalender Timeline</h4>

{{-- Timeline Deskripsi --}}
<div class="p-4 rounded shadow-sm mb-4" style="background: #f0f8f4">
    <div class="row">
        <div class="col-md-6">
            <p>Februari - April : Pengumuman Penerimaan Proposal</p>
            <p>Mei : Pengumpulan Proposal ke Universitas</p>
            <p>Juni - Juli : Proses Review dan Perbaikan</p>
            <p>Juli : Pengumuman Proposal yang Didanai</p>
            <p>Agustus : Tanda Tangan Kontrak Penelitian</p>
        </div>
        <div class="col-md-6">
            <p>Agustus : Pencairan Dana Penelitian</p>
            <p>Agustus – Juli (tahun berikutnya) : Pelaksanaan Penelitian</p>
            <p>Maret (tahun berikutnya) : Monitoring Evaluasi</p>
            <p>Juli (tahun berikutnya) : Laporan Akhir</p>
            <p>Agustus – Desember (tahun berikutnya) : Publikasi dan Seminar Hasil</p>
        </div>
    </div>
</div>

{{-- Dropdown Tahun --}}
<div class="row mb-3">
    <div class="col-md-6">
        <label class="fw-semibold">Tahun Usulan</label>
        <select class="form-select">
            <option>2025</option>
            <option>2024</option>
            <option>2023</option>
        </select>
    </div>

    <div class="col-md-6">
        <label class="fw-semibold">Tahun Ajaran Pelaksanaan</label>
        <select class="form-select">
            <option>2025/2026</option>
            <option>2024/2025</option>
        </select>
    </div>
</div>

{{-- Header Bulan --}}
<div class="d-flex justify-content-center align-items-center my-3">
    <button class="btn btn-sm btn-outline-secondary me-3">&lt;</button>
    <strong>Oktober 2025</strong>
    <button class="btn btn-sm btn-outline-secondary ms-3">&gt;</button>
</div>

{{-- Kalender --}}
<div class="table-responsive">
    <table class="table table-bordered text-center align-middle">
        <thead class="table-light">
            <tr>
                <th>Senin</th>
                <th>Selasa</th>
                <th>Rabu</th>
                <th>Kamis</th>
                <th>Jumat</th>
                <th>Sabtu</th>
                <th>Minggu</th>
            </tr>
        </thead>
        <tbody>
            <tr>
                <td></td>
                <td></td>
                <td>1</td>
                <td>2</td>
                <td>3</td>
                <td>4</td>
                <td></td>
            </tr>

            <tr>
                <td></td>
                <td>6</td>
                <td>7</td>
                <td>8</td>
                <td>
                    <div class="p-1 rounded" style="background:#f7b3e6; font-size:12px;">
                        📄 Pengumpulan proposal dibuka
                    </div>
                </td>
                <td>10</td>
                <td>11</td>
            </tr>

            <tr>
                <td>12</td>
                <td>13</td>
                <td>14</td>
                <td>15</td>
                <td>16</td>
                <td>17</td>
                <td>18</td>
            </tr>

            <tr>
                <td>
                    <div class="p-1 rounded" style="background:#b3d4ff; font-size:12px;">
                        📄 Pengumpulan proposal ditutup
                    </div>
                </td>
                <td>20</td>
                <td>21</td>
                <td>22</td>
                <td>23</td>
                <td>24</td>
                <td></td>
            </tr>
        </tbody>
    </table>
</div>

@endsection

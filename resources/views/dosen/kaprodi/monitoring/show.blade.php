@extends('layouts.app')

@section('title', 'Detail Monitoring Kelas')

@section('content')
    <div class="d-flex align-items-center justify-content-between mb-4">
        <h4 class="fw-bold py-3 mb-0">
            <a href="{{ route('dosen.monitoring-kaprodi.index') }}" class="text-muted fw-light">Monitoring /</a> Detail
            Kelas
        </h4>
        <div class="text-end">
            <h6 class="mb-0 fw-bold">{{ $kelas->mataKuliah->nama_mk }}</h6>
            <small class="text-muted">{{ $kelas->nama_kelas_kuliah }} | {{ $kelas->semester->nama_semester }}</small>
        </div>
    </div>

    <!-- Class Info Header -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="card border-top border-primary border-3">
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6 border-end">
                            <h5 class="mb-3">Informasi Mata Kuliah</h5>
                            <ul class="list-unstyled mb-0">
                                <li class="mb-2"><span class="fw-bold me-2">Kode MK:</span>
                                    {{ $kelas->mataKuliah->kode_mk }}</li>
                                <li class="mb-2"><span class="fw-bold me-2">SKS:</span> {{ $kelas->sks_mk }}</li>
                                <li class="mb-2"><span class="fw-bold me-2">Dosen:</span>
                                    {{ $kelas->dosenPengajar->map(fn($d) => $d->dosen->nama_tampilan)->implode(', ') }}</li>
                            </ul>
                        </div>
                        <div class="col-md-6 ps-md-4">
                            <h5 class="mb-3">Statistik Perkuliahan</h5>
                            <div class="d-flex align-items-center mb-2">
                                <span class="fw-bold me-2">Total Pertemuan:</span>
                                <span class="badge bg-label-primary fs-6">{{ $jurnal->count() }} / 14</span>
                            </div>
                            <div class="progress mb-2" style="height: 12px;">
                                @php
                                    $percent = round(($jurnal->count() / 14) * 100, 1);
                                    $barClass = $percent >= 100 ? 'bg-success' : ($percent >= 75 ? 'bg-info' : 'bg-primary');
                                @endphp
                                <div class="progress-bar {{ $barClass }}" role="progressbar" style="width: {{ $percent }}%"
                                    aria-valuenow="{{ $percent }}" aria-valuemin="0" aria-valuemax="100"></div>
                            </div>
                            <small class="text-muted">Progres realisasi: {{ $percent }}%</small>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="row">
        <!-- Jurnal / Berita Acara -->
        <div class="col-xl-7 col-lg-7">
            <div class="card mb-4">
                <div class="card-header border-bottom d-flex justify-content-between align-items-center bg-label-primary">
                    <h5 class="card-title mb-0 text-primary"><i class="ri-book-read-line me-2"></i>Jurnal Perkuliahan
                        (Dosen)</h5>
                </div>
                <div class="table-responsive">
                    <table class="table table-hover">
                        <thead>
                            <tr>
                                <th width="80">Ke-</th>
                                <th>Tanggal & Waktu</th>
                                <th>Materi / Bahasan</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($jurnal as $j)
                                <tr>
                                    <td class="text-center"><span
                                            class="badge rounded-pill bg-label-secondary">{{ $j->pertemuan_ke }}</span></td>
                                    <td>
                                        <div class="d-flex flex-column">
                                            <span class="fw-bold">{{ $j->tanggal->format('d/m/Y') }}</span>
                                            <small class="text-muted">{{ substr($j->jam_mulai, 0, 5) }} -
                                                {{ substr($j->jam_selesai, 0, 5) }}</small>
                                        </div>
                                    </td>
                                    <td>
                                        <div class="d-flex flex-column">
                                            <span>{{ $j->materi ?: '-' }}</span>
                                            <small class="text-info italic">{{ $j->metode_pembelajaran }}</small>
                                        </div>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="3" class="text-center py-5">
                                        <i class="ri-file-search-line ri-3x text-muted mb-2 d-block"></i>
                                        <p class="mb-0 text-muted">Belum ada jurnal perkuliahan yang diisi.</p>
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <!-- Student Attendance Summary -->
        <div class="col-xl-5 col-lg-5">
            <div class="card border-start border-success border-3">
                <div class="card-header border-bottom">
                    <h5 class="card-title mb-0">Presensi Mahasiswa</h5>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive" style="max-height: 500px;">
                        <table class="table table-sm table-hover">
                            <thead class="table-light">
                                <tr>
                                    <th>Mahasiswa</th>
                                    <th class="text-center">Hadir</th>
                                    <th class="text-end">%</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($rekapAbsensi as $r)
                                    <tr>
                                        <td>
                                            <div class="d-flex flex-column">
                                                <small class="fw-bold text-nowrap truncate"
                                                    style="max-width: 180px;">{{ $r['nama'] }}</small>
                                                <small class="text-muted">{{ $r['nim'] }}</small>
                                            </div>
                                        </td>
                                        <td class="text-center">
                                            <span
                                                class="badge {{ $r['percent'] < 75 ? 'bg-label-danger' : 'bg-label-success' }} badge-sm">
                                                {{ $r['hadir'] }} / {{ $r['total'] }}
                                            </span>
                                        </td>
                                        <td class="text-end fw-bold">{{ $r['percent'] }}%</td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
                <div class="card-footer bg-label-light">
                    <small class="text-muted fst-italic">* Mahasiswa dengan kehadiran < 75% ditandai merah.</small>
                </div>
            </div>
        </div>
    </div>

    <!-- Tab Feedback / Umpan Balik -->
    <div class="row mt-4">
        <div class="col-12">
            <div class="card">
                <div class="card-header border-bottom bg-label-info">
                    <h5 class="card-title mb-0 text-info"><i class="ri-feedback-line me-2"></i>Umpan Balik / Komplain
                        Mahasiswa</h5>
                </div>
                <div class="card-body py-5 text-center">
                    <i class="ri-chat-voice-line ri-4x text-muted mb-3 d-block"></i>
                    <h6 class="text-muted">Modul Umpan Balik Mahasiswa Belum Tersedia</h6>
                    <p class="text-muted mb-0">Fitur komplain / feedback per pertemuan akan segera hadir pada pembaruan
                        SIAKAD berikutnya.</p>
                </div>
            </div>
        </div>
    </div>
@endsection
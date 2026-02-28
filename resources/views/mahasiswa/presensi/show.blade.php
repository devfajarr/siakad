@extends('layouts.app')

@section('title', 'Detail Kehadiran Mahasiswa')

@section('content')
    <div class="container-xxl flex-grow-1 container-p-y">
        <div class="row mb-4">
            <div class="col-12">
                <div class="d-flex justify-content-between align-items-center mb-4">
                    <nav aria-label="breadcrumb">
                        <ol class="breadcrumb mb-0">
                            <li class="breadcrumb-item"><a href="{{ route('mahasiswa.kelas.index') }}">Daftar Kelas</a></li>
                            <li class="breadcrumb-item active">Log Kehadiran</li>
                        </ol>
                    </nav>
                    <a href="{{ route('mahasiswa.kelas.index') }}" class="btn btn-label-secondary">
                        <i class="ri-arrow-left-line me-1"></i> Kembali ke Daftar Kelas
                    </a>
                </div>
                <div class="card border-0 shadow-sm overflow-hidden mb-4">
                    <div class="card-body p-0">
                        <div class="row g-0">
                            <div class="col-md-8 p-6">
                                <h4 class="fw-bold mb-1">{{ $kelasKuliah->mataKuliah->nama_mk }}</h4>
                                <p class="text-muted mb-4">{{ $kelasKuliah->mataKuliah->kode_mk }} | Kelas
                                    {{ $kelasKuliah->nama_kelas_kuliah }}
                                </p>

                                <div class="d-flex align-items-center gap-4 flex-wrap">
                                    <div class="d-flex align-items-center">
                                        <div class="avatar avatar-sm me-2">
                                            <span class="avatar-initial rounded bg-label-primary"><i
                                                    class="ri-user-follow-line"></i></span>
                                        </div>
                                        <div>
                                            <small class="text-muted d-block">Dosen Pengampu</small>
                                            <span class="fw-semibold">
                                                {{ $kelasKuliah->dosenPengajars->first()->nama_tampilan }}
                                            </span>
                                        </div>
                                    </div>
                                    <div class="d-flex align-items-center">
                                        <div class="avatar avatar-sm me-2">
                                            <span class="avatar-initial rounded bg-label-info"><i
                                                    class="ri-calendar-event-line"></i></span>
                                        </div>
                                        <div>
                                            <small class="text-muted d-block">Total Pertemuan</small>
                                            <span class="fw-semibold">{{ $summary['total_pertemuan'] }} Sesi</span>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div
                                class="col-md-4 bg-light-primary d-flex flex-column justify-content-center p-6 border-start text-center">
                                <div class="mb-2">
                                    <h2 class="fw-black mb-0 text-primary">{{ $summary['persentase'] }}%</h2>
                                    <small class="text-muted fw-medium">Persentase Kehadiran</small>
                                </div>
                                <div class="progress w-100 mb-2" style="height: 10px;">
                                    <div class="progress-bar progress-bar-striped progress-bar-animated bg-primary"
                                        role="progressbar" style="width: {{ $summary['persentase'] }}%"
                                        aria-valuenow="{{ $summary['persentase'] }}" aria-valuemin="0" aria-valuemax="100">
                                    </div>
                                </div>
                                <small class="text-muted fst-italic">Target: {{ $summary['total_hadir'] }} /
                                    {{ $summary['target'] }} Hadir</small>
                            </div>
                        </div>
                    </div>
                </div>
                <!-- Info Kelayakan Ujian -->
                <div class="alert alert-info d-flex align-items-center mb-4 shadow-sm border-0" role="alert">
                    <i class="ri-information-line fs-4 me-3 text-info"></i>
                    <div>
                        <strong>Syarat Mengikuti Ujian (UTS/UAS):</strong> Anda dinyatakan layak dan dapat mencetak Kartu
                        Ujian apabila jadwal KRS Anda berstatus <b>ACC</b> dan persentase kehadiran minimal mncapai
                        <b>{{ config('academic.min_persentase_ujian', 75) }}%</b> dari target
                        <b>{{ config('academic.target_pertemuan_per_blok', 7) }} pertemuan</b> per blok ujian (UTS = Pert
                        1-7, UAS = Pert 8-14).
                    </div>
                </div>

                <div class="card border-0 shadow-sm">
                    <div class="card-header border-bottom">
                        <h5 class="card-title mb-0">Riwayat Presensi & Jurnal Perkuliahan</h5>
                    </div>
                    <div class="table-responsive">
                        <table class="table table-hover align-middle mb-0">
                            <thead class="table-light">
                                <tr>
                                    <th class="text-center" style="width: 70px;">Pert.</th>
                                    <th>Tanggal & Waktu</th>
                                    <th>Materi / Bahasan</th>
                                    <th class="text-center">Status</th>
                                    <th>Keterangan</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($pertemuans as $pertemuan)
                                    @php
                                        $presensi = $pertemuan->presensiMahasiswas->first();
                                        $statusClass = 'bg-label-secondary';
                                        $statusLabel = 'Belum Ada';

                                        if ($presensi) {
                                            switch ($presensi->status_kehadiran) {
                                                case 'H':
                                                    $statusClass = 'bg-label-success';
                                                    $statusLabel = 'Hadir';
                                                    break;
                                                case 'S':
                                                    $statusClass = 'bg-label-warning';
                                                    $statusLabel = 'Sakit';
                                                    break;
                                                case 'I':
                                                    $statusClass = 'bg-label-info';
                                                    $statusLabel = 'Izin';
                                                    break;
                                                case 'A':
                                                    $statusClass = 'bg-label-danger';
                                                    $statusLabel = 'Alfa';
                                                    break;
                                            }
                                        }
                                    @endphp
                                    <tr>
                                        <td class="text-center fw-bold">{{ $pertemuan->pertemuan_ke }}</td>
                                        <td>
                                            <div class="fw-semibold">{{ $pertemuan->tanggal->format('d M Y') }}</div>
                                            <small class="text-muted">{{ substr($pertemuan->jam_mulai, 0, 5) }} -
                                                {{ substr($pertemuan->jam_selesai, 0, 5) }}</small>
                                        </td>
                                        <td>
                                            <div class="text-wrap" style="max-width: 400px;">
                                                {{ $pertemuan->materi }}
                                                <br>
                                                <small
                                                    class="badge bg-label-secondary mt-1">{{ $pertemuan->metode_pembelajaran }}</small>
                                            </div>
                                        </td>
                                        <td class="text-center">
                                            <span class="badge {{ $statusClass }} px-3">{{ $statusLabel }}</span>
                                        </td>
                                        <td>
                                            <small class="text-muted fst-italic">{{ $presensi->keterangan ?? '-' }}</small>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="5" class="text-center py-5">
                                            <i class="ri-calendar-todo-line ri-3x text-light mb-3 d-block"></i>
                                            <p class="text-muted">Belum ada data pertemuan yang dicatat oleh dosen.</p>
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
@endsection
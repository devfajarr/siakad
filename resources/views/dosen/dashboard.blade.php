@extends('layouts.app')

@section('title', 'Dashboard Dosen')

@section('content')
    <div class="row">
        <!-- Welcome Widget -->
        <div class="col-lg-8 mb-4">
            <div class="card">
                <div class="d-flex align-items-end row">
                    <div class="col-sm-7">
                        <div class="card-body">
                            <h5 class="card-title text-primary">Selamat Datang, {{ $dosen->nama }}!</h5>
                            <p class="mb-4">
                                Anda sedang berada di dashboard akademik semester
                                <span class="fw-bold">{{ $semester->nama_semester ?? 'Aktif' }}</span>.
                                Pantau kelas dan jadwal mengajar Anda di sini.
                            </p>

                            <a href="{{ route('dosen.kelas.index') }}" class="btn btn-sm btn-label-primary">Daftar Kelas
                                Saya</a>
                        </div>
                    </div>
                    <div class="col-sm-5 text-center text-sm-left">
                        <div class="card-body pb-0 px-0 px-md-4 text-center">
                            <img src="{{ asset('assets/img/illustrations/boy-with-laptop-light.png') }}" height="140"
                                alt="View Badge User" data-app-dark-img="illustrations/boy-with-laptop-dark.png"
                                data-app-light-img="illustrations/boy-with-laptop-light.png" />
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Statistics Widget -->
        <div class="col-lg-4 col-md-12">
            <div class="row">
                <div class="col-6 mb-4">
                    <div class="card">
                        <div class="card-body">
                            <div class="card-title d-flex align-items-start justify-content-between">
                                <div class="avatar flex-shrink-0">
                                    <span class="avatar-initial rounded bg-label-primary"><i
                                            class="ri-book-open-line"></i></span>
                                </div>
                            </div>
                            <span class="fw-semibold d-block mb-1">Total Kelas</span>
                            <h3 class="card-title mb-2">{{ $totalKelas }}</h3>
                            <small class="text-muted">Semester Aktif</small>
                        </div>
                    </div>
                </div>
                <div class="col-6 mb-4">
                    <div class="card">
                        <div class="card-body">
                            <div class="card-title d-flex align-items-start justify-content-between">
                                <div class="avatar flex-shrink-0">
                                    <span class="avatar-initial rounded bg-label-success"><i
                                            class="ri-group-line"></i></span>
                                </div>
                            </div>
                            <span class="fw-semibold d-block mb-1">Total Mhs</span>
                            <h3 class="card-title mb-2">{{ $totalMahasiswa }}</h3>
                            <small class="text-muted">Seluruh Kelas</small>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Today's Schedule -->
        <div class="col-12 col-xl-12 mb-4">
            <div class="card">
                <div class="card-header d-flex align-items-center justify-content-between">
                    <h5 class="card-title m-0 me-2">Jadwal Mengajar Hari Ini</h5>
                    <div class="dropdown">
                        <small class="text-muted">{{ \Carbon\Carbon::now()->translatedFormat('l, d F Y') }}</small>
                    </div>
                </div>
                <div class="card-body">
                    <div class="table-responsive text-nowrap">
                        <table class="table table-hover">
                            <thead>
                                <tr>
                                    <th>Waktu</th>
                                    <th>Mata Kuliah</th>
                                    <th>Kelas</th>
                                    <th>Ruangan</th>
                                    <th>Status</th>
                                </tr>
                            </thead>
                            <tbody class="table-border-bottom-0">
                                @forelse ($todaySchedules as $schedule)
                                    <tr>
                                        <td>
                                            <span class="badge bg-label-primary">
                                                {{ substr($schedule->jam_mulai, 0, 5) }} -
                                                {{ substr($schedule->jam_selesai, 0, 5) }}
                                            </span>
                                        </td>
                                        <td>
                                            <div class="d-flex flex-column">
                                                <span class="fw-bold">{{ $schedule->kelasKuliah->mataKuliah->nama_mk }}</span>
                                                <small
                                                    class="text-muted">{{ $schedule->kelasKuliah->mataKuliah->kode_mk }}</small>
                                            </div>
                                        </td>
                                        <td>{{ $schedule->kelasKuliah->nama_kelas_kuliah }}</td>
                                        <td>
                                            <i class="ri-map-pin-2-line me-1"></i> {{ $schedule->ruang->nama_ruang ?? '-' }}
                                        </td>
                                        <td>
                                            @php
                                                $now = \Carbon\Carbon::now()->format('H:i:s');
                                                $statusClass = 'bg-label-secondary';
                                                $statusText = 'Belum Mulai';

                                                if ($now >= $schedule->jam_mulai && $now <= $schedule->jam_selesai) {
                                                    $statusClass = 'bg-label-danger';
                                                    $statusText = 'Sedang Berlangsung';
                                                } elseif ($now > $schedule->jam_selesai) {
                                                    $statusClass = 'bg-label-success';
                                                    $statusText = 'Selesai';
                                                }
                                            @endphp
                                            <span class="badge {{ $statusClass }}">{{ $statusText }}</span>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="5" class="text-center py-4">
                                            <img src="{{ asset('assets/img/illustrations/girl-doing-yoga-light.png') }}"
                                                alt="No Schedule" width="100" class="mb-2">
                                            <p class="text-muted">Tidak ada jadwal mengajar untuk hari ini.</p>
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
                <div class="card-footer text-center">
                    <a href="{{ route('dosen.kelas.index') }}" class="btn btn-primary">Lihat Seluruh Kelas</a>
                </div>
            </div>
        </div>
    </div>
@endsection
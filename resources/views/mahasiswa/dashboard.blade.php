@extends('layouts.app')

@section('title', 'Dashboard Mahasiswa')

@section('content')
    <div class="row mb-6">
        <div class="col-12">
            <h4 class="py-2 mb-0">Selamat Datang, {{ $mahasiswa->nama_mahasiswa }}</h4>
            <p class="text-muted">Ringkasan aktivitas akademik Anda hari ini.</p>
        </div>
    </div>

    <div class="row">
        <!-- Widget Profil & Statistik -->
        <div class="col-lg-8">
            <div class="card mb-6 border-0 shadow-sm">
                <div class="card-body">
                    <div class="row align-items-center">
                        <div class="col-md-3 text-center mb-4 mb-md-0">
                            <div class="avatar avatar-xl mb-2 mx-auto">
                                <img src="{{ asset('assets/img/avatars/1.png') }}" alt="User Avatar" class="rounded-circle">
                            </div>
                            <span class="badge bg-label-primary">{{ $mahasiswa->jenis_kelamin }}</span>
                        </div>
                        <div class="col-md-9 border-start-md ps-md-6">
                            <h5 class="mb-1">{{ $mahasiswa->nama_mahasiswa }}</h5>
                            <p class="mb-4 text-muted">NIM: <span
                                    class="fw-bold text-dark">{{ $riwayatAktif->nim ?? '-' }}</span></p>

                            <div class="row">
                                <div class="col-sm-6 mb-3">
                                    <small class="text-muted d-block">Program Studi</small>
                                    <span
                                        class="fw-medium text-primary">{{ $riwayatAktif->prodi->nama_program_studi ?? '-' }}</span>
                                </div>
                                <div class="col-sm-6 mb-3">
                                    <small class="text-muted d-block">Periode Aktif</small>
                                    <span class="fw-medium">{{ $activeSemester->nama_semester ?? '-' }}</span>
                                </div>
                            </div>

                            <hr class="my-4">

                            <div class="d-flex justify-content-between text-center mt-2">
                                <div>
                                    <h4 class="mb-0 text-primary">{{ $totalSks }}</h4>
                                    <small class="text-muted">Total SKS Semester Ini</small>
                                </div>
                                <div>
                                    <h4 class="mb-0 text-success">{{ $totalMatkul }}</h4>
                                    <small class="text-muted">Mata Kuliah Diambil</small>
                                </div>
                                <div>
                                    <h4 class="mb-0 text-info">Aktif</h4>
                                    <small class="text-muted">Status Registrasi</small>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Widget Jadwal Hari Ini -->
            <div class="card border-0 shadow-sm">
                <div class="card-header d-flex align-items-center justify-content-between p-6">
                    <h5 class="card-title m-0">
                        <i class="ri-calendar-todo-line me-2 text-primary"></i> Jadwal Kuliah Hari Ini
                    </h5>
                    <span class="badge bg-primary">{{ \Carbon\Carbon::now()->isoFormat('dddd, D MMMM Y') }}</span>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-hover mb-0">
                            <thead class="table-light">
                                <tr>
                                    <th class="ps-6">Waktu</th>
                                    <th>Mata Kuliah & Ruangan</th>
                                    <th class="text-end pe-6">Aksi</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($todaySchedules as $schedule)
                                    <tr>
                                        <td class="ps-6 py-4">
                                            <div class="d-flex flex-column">
                                                <span
                                                    class="fw-bold text-primary">{{ substr($schedule->jam_mulai, 0, 5) }}</span>
                                                <span class="text-muted small">s/d
                                                    {{ substr($schedule->jam_selesai, 0, 5) }}</span>
                                            </div>
                                        </td>
                                        <td class="py-4">
                                            <div class="d-flex align-items-center mb-1">
                                                <span
                                                    class="fw-medium text-dark">{{ $schedule->kelasKuliah->mataKuliah->nama_mk }}</span>
                                                <span class="badge bg-label-info ms-2">Kls
                                                    {{ $schedule->kelasKuliah->nama_kelas_kuliah }}</span>
                                            </div>
                                            <div class="small">
                                                <i class="ri-map-pin-2-line me-1"></i>
                                                {{ $schedule->ruang->nama_ruang ?? 'Belum Ditentukan' }}
                                            </div>
                                        </td>
                                        <td class="text-end pe-6 py-4">
                                            <button class="btn btn-sm btn-outline-primary">Detail</button>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="3" class="text-center py-10">
                                            <div class="mb-3">
                                                <i class="ri-calendar-check-line ri-4x text-light"></i>
                                            </div>
                                            <h6 class="mb-1 text-muted">Tidak ada jadwal hari ini</h6>
                                            <p class="small text-muted mb-0">Nikmati waktu luang Anda atau persiapkan untuk
                                                jadwal esok hari.</p>
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>

        <!-- Kolom Samping (Quick Access) -->
        <div class="col-lg-4">
            <div class="card mb-6 border-0 shadow-sm bg-primary text-white overflow-hidden">
                <div class="card-body position-relative">
                    <div class="mb-4">
                        <h5 class="text-white mb-1">Cepat Akses</h5>
                        <p class="small text-white-50">Navigasi praktis ke menu utama</p>
                    </div>
                    <div class="d-grid gap-3">
                        <a href="#" class="btn btn-white text-primary fw-bold text-start p-3 border-0">
                            <i class="ri-file-list-3-line me-2"></i> Kartu Rencana Studi (KRS)
                        </a>
                        <a href="#" class="btn btn-white text-primary fw-bold text-start p-3 border-0">
                            <i class="ri-calendar-event-line me-2"></i> Jadwal Mingguan
                        </a>
                        <a href="#" class="btn btn-white text-primary fw-bold text-start p-3 border-0">
                            <i class="ri-graduation-cap-line me-2"></i> Histori Hasil Studi
                        </a>
                    </div>
                    <div class="position-absolute end-0 bottom-0 opacity-25 p-2 mb-n4 me-n4">
                        <i class="ri-dashboard-line ri-10x"></i>
                    </div>
                </div>
            </div>

            <div class="card border-0 shadow-sm">
                <div class="card-header pb-2">
                    <h5 class="card-title mb-0">Pengumuman Terbaru</h5>
                </div>
                <div class="card-body">
                    <div class="alert alert-outline-primary d-flex align-items-center p-3 mb-0" role="alert">
                        <span class="alert-icon me-3"><i class="ri-information-line"></i></span>
                        <div class="small">
                            Periode pengisian KRS akan dibuka mulai tanggal 1 Maret 2026. Pastikan Anda telah menyelesaikan
                            pembayaran registrasi.
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
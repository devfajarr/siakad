@extends('layouts.app')

@section('title', 'Detail Kelas Kuliah')

@section('content')
    <div class="row">
        <div class="col-12">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <nav aria-label="breadcrumb">
                    <ol class="breadcrumb mb-0">
                        <li class="breadcrumb-item"><a href="{{ route('mahasiswa.dashboard') }}">Dashboard</a></li>
                        <li class="breadcrumb-item"><a href="{{ route('mahasiswa.kelas.index') }}">Daftar Kelas</a></li>
                        <li class="breadcrumb-item active">Detail Kelas</li>
                    </ol>
                </nav>
                <a href="{{ route('mahasiswa.kelas.index') }}" class="btn btn-label-secondary">
                    <i class="ri-arrow-left-line me-1"></i> Kembali ke Daftar Kelas
                </a>
            </div>
        </div>
    </div>

    <div class="row">
        <div class="col-md-8">
            <div class="card mb-6 border-0 shadow-sm">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h5 class="mb-0">Informasi Mata Kuliah</h5>
                    <span class="badge bg-label-primary">Kelas {{ $kelasKuliah->nama_kelas_kuliah }}</span>
                </div>
                <div class="card-body">
                    <div class="row g-3">
                        <div class="col-sm-6">
                            <small class="text-muted d-block">Kode Mata Kuliah</small>
                            <span class="fw-bold text-dark">{{ $kelasKuliah->mataKuliah->kode_mk ?? '-' }}</span>
                        </div>
                        <div class="col-sm-6">
                            <small class="text-muted d-block">Nama Mata Kuliah</small>
                            <span class="fw-bold text-dark">{{ $kelasKuliah->mataKuliah->nama_mk ?? '-' }}</span>
                        </div>
                        <div class="col-sm-6">
                            <small class="text-muted d-block">SKS</small>
                            <span class="fw-bold text-dark">{{ $kelasKuliah->mataKuliah->sks_mata_kuliah ?? 0 }} SKS</span>
                        </div>
                        <div class="col-sm-6">
                            <small class="text-muted d-block">Semester</small>
                            <span class="fw-bold text-dark">{{ $kelasKuliah->semester->nama_semester ?? '-' }}</span>
                        </div>
                    </div>
                </div>
            </div>

            <div class="card border-0 shadow-sm">
                <div class="card-header">
                    <h5 class="mb-0">Daftar Dosen Pengajar</h5>
                </div>
                <div class="card-body">
                    <ul class="list-group list-group-flush">
                        @foreach($kelasKuliah->dosenPengajars as $pengajar)
                            <li class="list-group-item d-flex align-items-center px-0">
                                <div class="avatar me-3">
                                    <span class="avatar-initial rounded-circle bg-label-info">
                                        <i class="ri-user-line"></i>
                                    </span>
                                </div>
                                <div class="d-flex w-100 flex-wrap align-items-center justify-content-between gap-2">
                                    <div class="me-2">
                                        <h6 class="mb-0">
                                            {{ $pengajar->dosenAliasLokal->nama ?? ($pengajar->dosen->nama ?? '-') }}</h6>
                                        <small
                                            class="text-muted">{{ $pengajar->dosen->nidn ?? ($pengajar->dosen->nip ?? 'Dosen Luar') }}</small>
                                    </div>
                                    <div class="badge bg-label-secondary">Pengajar Utama</div>
                                </div>
                            </li>
                        @endforeach
                    </ul>
                </div>
            </div>
        </div>

        <div class="col-md-4">
            <div class="card mb-6 border-0 shadow-sm">
                <div class="card-header">
                    <h5 class="mb-0">Jadwal Kuliah</h5>
                </div>
                <div class="card-body">
                    @forelse($kelasKuliah->jadwalKuliahs as $jadwal)
                        <div class="d-flex align-items-start mb-4">
                            <div class="avatar me-3">
                                <span class="avatar-initial rounded bg-label-primary">
                                    <i class="ri-time-line"></i>
                                </span>
                            </div>
                            <div>
                                <h6 class="mb-1">{{ \App\Services\JadwalKuliahService::HARI[$jadwal->hari] }}</h6>
                                <p class="mb-1 small">
                                    {{ substr($jadwal->jam_mulai, 0, 5) }} - {{ substr($jadwal->jam_selesai, 0, 5) }}
                                </p>
                                <small class="text-primary">
                                    <i class="ri-map-pin-line me-1"></i> {{ $jadwal->ruang->nama_ruang ?? 'Ruangan TBD' }}
                                </small>
                            </div>
                        </div>
                    @empty
                        <div class="text-center py-4">
                            <i class="ri-time-line ri-3x text-light mb-2"></i>
                            <p class="text-muted small">Jadwal belum tersedia</p>
                        </div>
                    @endforelse
                </div>
            </div>

            <div class="card border-0 shadow-sm bg-label-info">
                <div class="card-body">
                    <h6 class="mb-2"><i class="ri-information-line me-1"></i> Informasi Tambahan</h6>
                    <p class="small mb-0">
                        Kehadiran minimal mahasiswa adalah 75% untuk dapat mengikuti Ujian Akhir Semester. Pastikan Anda
                        selalu mengisi absensi setiap pertemuan.
                    </p>
                </div>
            </div>
        </div>
    </div>
@endsection
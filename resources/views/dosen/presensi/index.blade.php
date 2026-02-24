@extends('layouts.app')

@section('title', 'Daftar Pertemuan & Jurnal')

@section('content')
    <div class="container-xxl flex-grow-1 container-p-y">
        <div class="row mb-4">
            <div class="col-12">
                <nav aria-label="breadcrumb">
                    <ol class="breadcrumb">
                        <li class="breadcrumb-item"><a href="{{ route('dosen.kelas.index') }}">Daftar Kelas</a></li>
                        <li class="breadcrumb-item active">Presensi & Jurnal</li>
                    </ol>
                </nav>
                <div class="card border-0 shadow-sm">
                    <div class="card-body">
                        <div class="d-flex justify-content-between align-items-center flex-wrap gap-2">
                            <div>
                                <h4 class="fw-bold mb-1">{{ $kelasKuliah->mataKuliah->nama_mk }}</h4>
                                <p class="mb-0 text-muted">
                                    <span class="badge bg-label-primary me-2">{{ $kelasKuliah->nama_kelas_kuliah }}</span>
                                    {{ $kelasKuliah->mataKuliah->kode_mk }} | SKS:
                                    {{ rtrim(rtrim(number_format($kelasKuliah->mataKuliah->sks, 2), '0'), '.') }}
                                </p>
                            </div>
                            <div class="d-flex gap-2">
                                <a href="{{ route('dosen.kelas.index') }}" class="btn btn-label-secondary">
                                    <i class="ri-arrow-left-line me-1"></i> Kembali
                                </a>
                                @if($kelasKuliah->presensiPertemuans->count() < config('academic.target_pertemuan'))
                                    <a href="{{ route('dosen.presensi.create', $kelasKuliah->id) }}" class="btn btn-primary">
                                        <i class="ri-add-line me-1"></i> Tambah Pertemuan
                                    </a>
                                @endif
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        @if(session('success'))
            <div class="alert alert-success alert-dismissible" role="alert">
                {{ session('success') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        @endif

        @if(session('error'))
            <div class="alert alert-danger alert-dismissible" role="alert">
                {{ session('error') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        @endif

        <div class="card border-0 shadow-sm">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="table-light">
                        <tr>
                            <th class="text-center" style="width: 70px;">Pert.</th>
                            <th>Tanggal & Waktu</th>
                            <th>Materi / Jurnal Kuliah</th>
                            <th class="text-center">Kehadiran</th>
                            <th class="text-center" style="width: 100px;">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($kelasKuliah->presensiPertemuans as $pertemuan)
                            <tr>
                                <td class="text-center fw-bold">{{ $pertemuan->pertemuan_ke }}</td>
                                <td>
                                    <div class="fw-semibold">{{ $pertemuan->tanggal->format('d M Y') }}</div>
                                    <small class="text-muted">{{ substr($pertemuan->jam_mulai, 0, 5) }} -
                                        {{ substr($pertemuan->jam_selesai, 0, 5) }}</small>
                                </td>
                                <td>
                                    <div class="text-wrap" style="max-width: 400px;">
                                        {{ Str::limit($pertemuan->materi, 150) }}
                                        <br>
                                        <small
                                            class="badge bg-label-secondary mt-1">{{ $pertemuan->metode_pembelajaran }}</small>
                                    </div>
                                </td>
                                <td class="text-center">
                                    <span class="badge bg-label-success">
                                        {{ $pertemuan->hadir_count }} /
                                        {{ $kelasKuliah->peserta_kelas_kuliah_count ?? '-' }}
                                    </span>
                                </td>
                                <td class="text-center">
                                    <a href="{{ route('dosen.presensi.edit', $pertemuan->id) }}"
                                        class="btn btn-sm btn-icon btn-label-warning" title="Edit Presensi">
                                        <i class="ri-edit-2-line"></i>
                                    </a>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" class="text-center py-5">
                                    <div class="mb-3">
                                        <i class="ri-calendar-event-line ri-4x text-light"></i>
                                    </div>
                                    <h5>Belum ada data pertemuan</h5>
                                    <p class="text-muted">Klik tombol "Tambah Pertemuan" untuk memulai pencatatan presensi.</p>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
@endsection
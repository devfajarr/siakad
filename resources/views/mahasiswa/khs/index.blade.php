@extends('layouts.app')

@section('title', 'Kartu Hasil Studi (KHS)')

@section('content')
    <div class="container-xxl flex-grow-1 container-p-y">
        <div class="d-flex justify-content-between align-items-center py-3 mb-4">
            <h4 class="fw-bold mb-0">
                <span class="text-muted fw-light">Akademik /</span> Kartu Hasil Studi (KHS)
            </h4>
        </div>

        <!-- Student Info & Semester Filter -->
        <div class="row mb-4">
            <div class="col-md-8">
                <div class="card bg-primary text-white h-100">
                    <div class="card-body">
                        <div class="d-flex align-items-start align-items-sm-center gap-4">
                            <i class="ri-user-star-line ri-4x"></i>
                            <div class="button-wrapper">
                                <h4 class="text-white mb-1">{{ $mahasiswa->nama_mahasiswa }}</h4>
                                <p class="mb-0">{{ $riwayatAktif->nim }} | {{ $riwayatAktif->prodi->nama_program_studi }}</p>
                                <div class="badge bg-label-white mt-2">Dosen PA: {{ $mahasiswa->dosen_pembimbing->nama_tampilan ?? 'Belum Ditentukan' }}</div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-md-4 mt-3 mt-md-0">
                <div class="card h-100">
                    <div class="card-body d-flex flex-column justify-content-center">
                        <label class="form-label">Pilih Semester</label>
                        <form action="{{ route('mahasiswa.khs.index') }}" method="GET" id="semesterForm">
                            <select name="semester_id" class="form-select select2" onchange="this.form.submit()">
                                @foreach($semesters as $sem)
                                    <option value="{{ $sem->id_semester }}" {{ $semesterId == $sem->id_semester ? 'selected' : '' }}>
                                        {{ $sem->nama_semester }}
                                    </option>
                                @endforeach
                            </select>
                        </form>
                    </div>
                </div>
            </div>
        </div>

        <!-- Stats Grid -->
        <div class="row g-4 mb-4">
            <div class="col-sm-6 col-xl-3">
                <div class="card">
                    <div class="card-body">
                        <div class="d-flex align-items-start justify-content-between">
                            <div class="content-left">
                                <p class="mb-1">SKS Semester</p>
                                <div class="d-flex align-items-center my-1">
                                    <h4 class="mb-0 me-2 text-primary">{{ $semesterStats['total_sks'] }}</h4>
                                </div>
                                <small class="text-muted">Total SKS Diambil</small>
                            </div>
                            <div class="avatar">
                                <span class="avatar-initial rounded bg-label-primary">
                                    <i class="ri-book-open-line ri-24px"></i>
                                </span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-sm-6 col-xl-3">
                <div class="card">
                    <div class="card-body">
                        <div class="d-flex align-items-start justify-content-between">
                            <div class="content-left">
                                <p class="mb-1">IPS Semester</p>
                                <div class="d-flex align-items-center my-1">
                                    <h4 class="mb-0 me-2 text-success">{{ number_format($semesterStats['ips'], 2) }}</h4>
                                </div>
                                <small class="text-muted">Indeks Prestasi</small>
                            </div>
                            <div class="avatar">
                                <span class="avatar-initial rounded bg-label-success">
                                    <i class="ri-line-chart-line ri-24px"></i>
                                </span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-sm-6 col-xl-3">
                <div class="card">
                    <div class="card-body">
                        <div class="d-flex align-items-start justify-content-between">
                            <div class="content-left">
                                <p class="mb-1">SKS Kumulatif</p>
                                <div class="d-flex align-items-center my-1">
                                    <h4 class="mb-0 me-2 text-warning">{{ $cumulativeStats['total_sks'] }}</h4>
                                </div>
                                <small class="text-muted">Lulus & Diambil</small>
                            </div>
                            <div class="avatar">
                                <span class="avatar-initial rounded bg-label-warning">
                                    <i class="ri-graduation-cap-line ri-24px"></i>
                                </span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-sm-6 col-xl-3">
                <div class="card">
                    <div class="card-body">
                        <div class="d-flex align-items-start justify-content-between">
                            <div class="content-left">
                                <p class="mb-1">IP Kumulatif</p>
                                <div class="d-flex align-items-center my-1">
                                    <h4 class="mb-0 me-2 text-info">{{ number_format($cumulativeStats['ipk'], 2) }}</h4>
                                </div>
                                <small class="text-muted">IPK Seluruh</small>
                            </div>
                            <div class="avatar">
                                <span class="avatar-initial rounded bg-label-info">
                                    <i class="ri-medal-line ri-24px"></i>
                                </span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- KHS Table -->
        <div class="card">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h5 class="mb-0">Kartu Hasil Studi - {{ $selectedSemester->nama_semester }}</h5>
                <button class="btn btn-outline-danger btn-sm" disabled>
                    <i class="ri-file-pdf-line me-1"></i> Cetak PDF (Soon)
                </button>
            </div>
            <div class="table-responsive text-nowrap">
                <table class="table table-hover">
                    <thead class="table-light">
                        <tr>
                            <th width="50">No</th>
                            <th>Kode</th>
                            <th>Mata Kuliah</th>
                            <th class="text-center">SKS</th>
                            <th class="text-center">Nilai</th>
                            <th class="text-center">Bobot</th>
                            <th class="text-center">Total</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($khsData as $index => $row)
                            <tr>
                                <td>{{ $index + 1 }}</td>
                                <td>{{ $row->kelasKuliah->mataKuliah->kode_mk ?? '-' }}</td>
                                <td>
                                    <div class="fw-bold">{{ $row->kelasKuliah->mataKuliah->nama_mk ?? '-' }}</div>
                                    <small class="text-muted">{{ $row->kelasKuliah->nama_kelas_kuliah }}</small>
                                </td>
                                <td class="text-center">{{ $row->sks_item }}</td>
                                <td class="text-center">
                                    @if($row->nilai_huruf)
                                        <span class="badge bg-label-primary fw-bold">{{ $row->nilai_huruf }}</span>
                                    @elseif(!$row->kelasKuliah->is_locked)
                                        <span class="badge bg-label-secondary" title="Menunggu penguncian nilai oleh Admin">
                                            <i class="ri-time-line me-1"></i> Proses
                                        </span>
                                    @else
                                        <span class="badge bg-label-danger">-</span>
                                    @endif
                                </td>
                                <td class="text-center">
                                    {{ $row->nilai_indeks !== null ? number_format($row->nilai_indeks, 2) : ($row->kelasKuliah->is_locked ? '0.00' : '-') }}
                                </td>
                                <td class="text-center">
                                    {{ $row->bobot_item !== null ? number_format($row->bobot_item, 2) : ($row->kelasKuliah->is_locked ? '0.00' : '-') }}
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" class="text-center py-5">
                                    <i class="ri-information-line ri-3x text-muted mb-2"></i>
                                    <p class="text-muted">Data KHS tidak ditemukan untuk semester ini.</p>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                    @if($khsData->isNotEmpty())
                        <tfoot class="table-light">
                            <tr>
                                <th colspan="3" class="text-end">Jumlah</th>
                                <th class="text-center">{{ $semesterStats['total_sks'] }}</th>
                                <th></th>
                                <th></th>
                                <th class="text-center">{{ number_format($semesterStats['total_bobot'], 2) }}</th>
                            </tr>
                            <tr>
                                <th colspan="6" class="text-end">Indeks Prestasi Semester (IPS)</th>
                                <th class="text-center">{{ number_format($semesterStats['ips'], 2) }}</th>
                            </tr>
                        </tfoot>
                    @endif
                </table>
            </div>
        </div>
    </div>
@endsection

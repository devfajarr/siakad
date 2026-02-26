@extends('layouts.app')

@section('title', 'Monitoring Perkuliahan')

@section('content')
    <div class="d-flex align-items-center justify-content-between mb-4">
        <h4 class="fw-bold py-3 mb-0">
            <span class="text-muted fw-light">Kaprodi /</span> Monitoring Perkuliahan
        </h4>
        <div class="text-end">
            <h6 class="mb-0 fw-bold">
                {{ $kaprodiEntries->map(fn($e) => $e->prodi->nama_program_studi)->implode(' & ') }}
            </h6>
            <small class="text-muted">Semester: {{ $selectedSemester->nama_semester }}</small>
        </div>
    </div>

    <!-- Stats Overview -->
    <div class="row g-4 mb-4">
        <div class="col-sm-6 col-xl-4">
            <div class="card">
                <div class="card-body">
                    <div class="d-flex align-items-start justify-content-between">
                        <div class="content-left">
                            <span class="text-heading">Total Kelas</span>
                            <div class="d-flex align-items-center my-1">
                                <h4 class="mb-0 me-2">{{ $totalKelas }}</h4>
                                <small class="text-success">(Semester Aktif)</small>
                            </div>
                            <small class="mb-0">Kelas terdaftar di prodi</small>
                        </div>
                        <div class="avatar">
                            <span class="avatar-initial rounded bg-label-primary">
                                <i class="ri-artboard-line ri-24px"></i>
                            </span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-sm-6 col-xl-4">
            <div class="card">
                <div class="card-body">
                    <div class="d-flex align-items-start justify-content-between">
                        <div class="content-left">
                            <span class="text-heading">Total Mahasiswa</span>
                            <div class="d-flex align-items-center my-1">
                                <h4 class="mb-0 me-2">{{ $totalMahasiswa }}</h4>
                                <small class="text-success">(Aktif KRS)</small>
                            </div>
                            <small class="mb-0">Mahasiswa prodi terdaftar</small>
                        </div>
                        <div class="avatar">
                            <span class="avatar-initial rounded bg-label-success">
                                <i class="ri-group-line ri-24px"></i>
                            </span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-sm-6 col-xl-4">
            <div class="card">
                <div class="card-body">
                    <div class="d-flex align-items-start justify-content-between">
                        <div class="content-left">
                            <span class="text-heading">Rata-rata Progres</span>
                            <div class="d-flex align-items-center my-1">
                                <h4 class="mb-0 me-2">{{ number_format($avgProgres, 1) }}%</h4>
                                <small class="text-primary">(Global)</small>
                            </div>
                            <small class="mb-0">Realisasi pertemuan (Target 14)</small>
                        </div>
                        <div class="avatar">
                            <span class="avatar-initial rounded bg-label-info">
                                <i class="ri-line-chart-line ri-24px"></i>
                            </span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Monitoring Table -->
    <div class="card">
        <div class="card-header border-bottom d-flex justify-content-between align-items-center">
            <h5 class="card-title mb-0">Daftar Progres Perkuliahan</h5>
            <div class="d-flex gap-2">
                <form action="{{ route('dosen.monitoring-kaprodi.index') }}" method="GET" class="d-flex gap-2">
                    <select name="semester_id" class="form-select form-select-sm" onchange="this.form.submit()"
                        style="min-width: 200px;">
                        @foreach($availableSemesters as $semester)
                            <option value="{{ $semester->id_semester }}" {{ $selectedSemester->id_semester == $semester->id_semester ? 'selected' : '' }}>
                                {{ $semester->nama_semester }}
                            </option>
                        @endforeach
                    </select>
                </form>

                @if($kaprodiEntries->count() > 1)
                    <select class="form-select form-select-sm" id="prodiFilter" style="min-width: 200px;">
                        <option value="">Semua Program Studi</option>
                        @foreach($kaprodiEntries as $entry)
                            <option value="{{ $entry->prodi->nama_program_studi }}">
                                {{ $entry->prodi->nama_program_studi }}
                            </option>
                        @endforeach
                    </select>
                @endif
            </div>
        </div>
        <div class="card-datatable table-responsive">
            <table class="table table-hover border-top" id="tableMonitoring">
                <thead>
                    <tr>
                        <th>Mata Kuliah</th>
                        <th>Program Studi</th>
                        <th>Dosen Pengampu</th>
                        <th class="text-center">Pertemuan (14)</th>
                        <th>Progres</th>
                        <th>Status</th>
                        <th>Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($kelasData as $kelas)
                        <tr>
                            <td>
                                <div class="d-flex flex-column">
                                    <span class="fw-bold text-heading">{{ $kelas['nama_mk'] }}</span>
                                    <small class="text-muted">{{ $kelas['kode_mk'] }} - {{ $kelas['nama_kelas'] }}</small>
                                </div>
                            </td>
                            <td data-search="{{ $kelas['prodi'] }}">
                                <span class="badge bg-label-secondary">{{ $kelas['prodi'] }}</span>
                            </td>
                            <td>{{ $kelas['dosen'] }}</td>
                            <td class="text-center">
                                <span class="badge rounded-pill bg-label-primary">
                                    {{ $kelas['pertemuan_count'] ?? 0 }} / 14
                                </span>
                            </td>
                            <td style="min-width: 150px;">
                                <div class="d-flex align-items-center">
                                    <div class="progress w-100 me-2" style="height: 8px;">
                                        @php
                                            $barClass = $kelas['progres_percent'] >= 100 ? 'bg-success' :
                                                ($kelas['progres_percent'] >= 75 ? 'bg-info' :
                                                    ($kelas['progres_percent'] >= 25 ? 'bg-primary' : 'bg-warning'));
                                        @endphp
                                        <div class="progress-bar {{ $barClass }}" role="progressbar"
                                            style="width: {{ $kelas['progres_percent'] }}%"
                                            aria-valuenow="{{ $kelas['progres_percent'] }}" aria-valuemin="0"
                                            aria-valuemax="100"></div>
                                    </div>
                                    <small>{{ $kelas['progres_percent'] }}%</small>
                                </div>
                            </td>
                            <td>
                                @php
                                    $statusClass = match ($kelas['status']) {
                                        'Selesai' => 'bg-label-success',
                                        'Berjalan' => 'bg-label-info',
                                        'Mulai' => 'bg-label-primary',
                                        default => 'bg-label-secondary'
                                    };
                                @endphp
                                <span class="badge {{ $statusClass }}">{{ $kelas['status'] }}</span>
                            </td>
                            <td>
                                <a href="{{ route('dosen.monitoring-kaprodi.show', $kelas['id']) }}"
                                    class="btn btn-sm btn-icon btn-text-secondary rounded-pill" data-bs-toggle="tooltip"
                                    title="Lihat Jurnal & Presensi">
                                    <i class="ri-eye-line ri-20px"></i>
                                </a>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
@endsection

@push('styles')
    <link rel="stylesheet" href="{{ asset('assets/vendor/libs/datatables-bs5/datatables.bootstrap5.css') }}" />
@endpush

@push('scripts')
    <script src="{{ asset('assets/vendor/libs/datatables-bs5/datatables-bootstrap5.js') }}"></script>
    <script>
        $(function () {
            if ($.fn.DataTable) {
                var table = $('#tableMonitoring').DataTable({
                    order: [[4, 'desc']], // Urutkan berdasarkan progres tertinggi (sekarang index 4)
                    language: {
                        searchPlaceholder: 'Cari Kelas/Dosen...',
                        sLengthMenu: '_MENU_',
                    }
                });

                $('#prodiFilter').on('change', function () {
                    var val = $(this).val();
                    table.column(1).search(val).draw();
                });
            }
        });
    </script>
@endpush
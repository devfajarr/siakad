@extends('layouts.app')

@section('title', 'Admin Control Tower')

@section('content')
    <div class="row g-4 mb-4">
        <!-- Card 1: Status Feeder -->
        <div class="col-sm-6 col-xl-3">
            <div class="card h-100 shadow-sm">
                <div class="card-body d-flex justify-content-between align-items-center">
                    <div>
                        <h6 class="card-title mb-1 text-muted">Status Feeder</h6>
                        <h4 class="mb-1 text-success fw-bold">
                            Terhubung
                        </h4>
                        <small class="text-muted">Periode: 20231 <span class="ms-1 px-1 bg-label-primary rounded">Token
                                Aktif</span></small>
                    </div>
                    <div class="avatar">
                        <span class="avatar-initial rounded bg-label-success">
                            <i class="ri-database-2-line ri-24px"></i>
                        </span>
                    </div>
                </div>
            </div>
        </div>

        <!-- Card 2: Total Mahasiswa Aktif -->
        <div class="col-sm-6 col-xl-3">
            <div class="card h-100 shadow-sm">
                <div class="card-body d-flex justify-content-between align-items-center">
                    <div>
                        <h6 class="card-title mb-1 text-muted">Mahasiswa Aktif</h6>
                        <h4 class="mb-1 fw-bold">1,250</h4>
                        <small class="text-success fw-semibold"><i class="ri-arrow-up-s-line"></i> Semester Aktif</small>
                    </div>
                    <div class="avatar">
                        <span class="avatar-initial rounded bg-label-primary">
                            <i class="ri-group-line ri-24px"></i>
                        </span>
                    </div>
                </div>
            </div>
        </div>

        <!-- Card 3: Dosen Mengajar -->
        <div class="col-sm-6 col-xl-3">
            <div class="card h-100 shadow-sm">
                <div class="card-body d-flex justify-content-between align-items-center">
                    <div>
                        <h6 class="card-title mb-1 text-muted">Dosen Mengajar</h6>
                        <h4 class="mb-1 fw-bold">45</h4>
                        <small class="text-muted">Semester Berjalan</small>
                    </div>
                    <div class="avatar">
                        <span class="avatar-initial rounded bg-label-info">
                            <i class="ri-user-star-line ri-24px"></i>
                        </span>
                    </div>
                </div>
            </div>
        </div>

        <!-- Card 4: Data Invalid -->
        <div class="col-sm-6 col-xl-3">
            <div class="card h-100 shadow-sm border-danger">
                <div class="card-body d-flex justify-content-between align-items-center">
                    <div>
                        <h6 class="card-title mb-1 text-danger">Data Invalid</h6>
                        <h4 class="mb-1 text-danger fw-bold">12</h4>
                        <small class="text-danger fw-semibold">Perlu Perbaikan</small>
                    </div>
                    <div class="avatar" data-bs-toggle="tooltip" data-bs-placement="top" title="Data yang tidak valid">
                        <span class="avatar-initial rounded bg-label-danger">
                            <i class="ri-error-warning-line ri-24px"></i>
                        </span>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="row g-4 mb-4">
        <!-- Left Column: Operasional (70%) -->
        <div class="col-lg-8">
            <div class="card shadow-sm h-100">
                <div class="card-header">
                    <h5 class="card-title mb-0">Operasional Akademik</h5>
                </div>
                <div class="card-body">
                    <ul class="nav nav-tabs nav-fill" role="tablist">
                        <li class="nav-item">
                            <button type="button" class="nav-link active" role="tab" data-bs-toggle="tab"
                                data-bs-target="#navs-krs" aria-controls="navs-krs" aria-selected="true">
                                <i class="ri-file-list-3-line me-1"></i> KRS Menunggu Validasi
                            </button>
                        </li>
                        <li class="nav-item">
                            <button type="button" class="nav-link" role="tab" data-bs-toggle="tab"
                                data-bs-target="#navs-nilai" aria-controls="navs-nilai" aria-selected="false">
                                <i class="ri-bar-chart-line me-1"></i> Progress Input Nilai
                            </button>
                        </li>
                    </ul>
                    <div class="tab-content">
                        <!-- Tab 1: KRS -->
                        <div class="tab-pane fade show active" id="navs-krs" role="tabpanel">
                            <div class="table-responsive text-nowrap">
                                <table class="table table-hover">
                                    <thead>
                                        <tr>
                                            <th>NIM</th>
                                            <th>Nama</th>
                                            <th>Prodi</th>
                                            <th>Status</th>
                                            <th>Action</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <tr>
                                            <td>2023001</td>
                                            <td>Andi Saputra</td>
                                            <td>TI</td>
                                            <td><span class="badge bg-label-warning me-1">Menunggu</span></td>
                                            <td>
                                                <button class="btn btn-sm btn-success">Approve</button>
                                            </td>
                                        </tr>
                                        <tr>
                                            <td>2023002</td>
                                            <td>Budi Santoso</td>
                                            <td>MI</td>
                                            <td><span class="badge bg-label-warning me-1">Menunggu</span></td>
                                            <td>
                                                <button class="btn btn-sm btn-success">Approve</button>
                                            </td>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>
                        </div>

                        <!-- Tab 2: Input Nilai -->
                        <div class="tab-pane fade" id="navs-nilai" role="tabpanel">
                            <div class="mt-3">
                                <div class="mb-3">
                                    <div class="d-flex justify-content-between">
                                        <span class="fw-semibold">Teknik Informatika (TI)</span>
                                        <span class="fw-semibold">75%</span>
                                    </div>
                                    <div class="progress mt-1" style="height: 10px;">
                                        <div class="progress-bar bg-primary" role="progressbar" style="width: 75%"
                                            aria-valuenow="75" aria-valuemin="0" aria-valuemax="100"></div>
                                    </div>
                                </div>
                                <div class="mb-3">
                                    <div class="d-flex justify-content-between">
                                        <span class="fw-semibold">Manajemen Informatika (MI)</span>
                                        <span class="fw-semibold">60%</span>
                                    </div>
                                    <div class="progress mt-1" style="height: 10px;">
                                        <div class="progress-bar bg-info" role="progressbar" style="width: 60%"
                                            aria-valuenow="60" aria-valuemin="0" aria-valuemax="100"></div>
                                    </div>
                                </div>
                                <div class="mb-3">
                                    <div class="d-flex justify-content-between">
                                        <span class="fw-semibold">Akuntansi (AK)</span>
                                        <span class="fw-semibold">90%</span>
                                    </div>
                                    <div class="progress mt-1" style="height: 10px;">
                                        <div class="progress-bar bg-success" role="progressbar" style="width: 90%"
                                            aria-valuenow="90" aria-valuemin="0" aria-valuemax="100"></div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Right Column: Feeder Health (30%) -->
        <div class="col-lg-4">
            <div class="card shadow-sm h-100 bg-label-warning">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h5 class="card-title mb-0 text-dark">Feeder Health Panel</h5>
                    <i class="ri-pulse-line ri-24px text-dark"></i>
                </div>
                <div class="card-body">
                    <h6 class="text-danger mb-2">🔴 Error Terakhir Feeder</h6>
                    <ul class="list-group list-group-flush bg-transparent mb-4">
                        <li
                            class="list-group-item bg-transparent px-0 py-1 d-flex justify-content-between align-items-center">
                            <span><i class="ri-error-warning-fill text-danger me-2"></i>Error 100</span>
                            <span class="text-muted small">NIK Ganda</span>
                        </li>
                        <li
                            class="list-group-item bg-transparent px-0 py-1 d-flex justify-content-between align-items-center">
                            <span><i class="ri-error-warning-fill text-danger me-2"></i>Error 204</span>
                            <span class="text-muted small">Prodi Tidak Ditemukan</span>
                        </li>
                        <li
                            class="list-group-item bg-transparent px-0 py-1 d-flex justify-content-between align-items-center">
                            <span><i class="ri-error-warning-fill text-danger me-2"></i>Error 301</span>
                            <span class="text-muted small">Periode Tidak Aktif</span>
                        </li>
                    </ul>

                    <h6 class="text-dark mb-2">⚡ Quick Action</h6>
                    <div class="d-grid gap-2">
                        <button class="btn btn-sm btn-outline-dark text-start">
                            <i class="ri-refresh-line me-2"></i>Sync Referensi
                        </button>
                        <button class="btn btn-sm btn-outline-dark text-start">
                            <i class="ri-user-add-line me-2"></i>Sync Mahasiswa Baru
                        </button>
                        <button class="btn btn-sm btn-outline-danger text-start">
                            <i class="ri-plug-line me-2"></i>Reconnect Feeder
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="row">
        <!-- Todo List Admin -->
        <div class="col-12">
            <div class="card shadow-sm">
                <div class="card-header">
                    <h5 class="card-title mb-0">To-Do List Admin</h5>
                </div>
                <div class="card-body p-0">
                    <div class="row m-0">
                        <div class="col-md-6 border-end p-4">
                            <h6 class="text-warning mb-3"><i class="ri-money-dollar-circle-line me-2"></i>Pembayaran Pending
                            </h6>
                            <ul class="list-unstyled mb-0">
                                <li class="mb-2 d-flex align-items-center">
                                    <i class="ri-checkbox-blank-circle-line text-muted me-2 font-small-2"></i>
                                    <span>5 Transaksi menunggu verifikasi</span>
                                </li>
                                <li class="d-flex align-items-center">
                                    <i class="ri-checkbox-blank-circle-line text-muted me-2 font-small-2"></i>
                                    <span>2 Bukti transfer belum dicek</span>
                                </li>
                            </ul>
                        </div>
                        <div class="col-md-6 p-4">
                            <h6 class="text-danger mb-3"><i class="ri-calendar-check-line me-2"></i>Jadwal Belum Absen</h6>
                            <ul class="list-unstyled mb-0">
                                <li class="mb-2 d-flex align-items-center">
                                    <i class="ri-error-warning-line text-danger me-2"></i>
                                    <span>Algoritma – Dosen belum presensi</span>
                                </li>
                                <li class="d-flex align-items-center">
                                    <i class="ri-error-warning-line text-danger me-2"></i>
                                    <span>Basis Data – Dosen belum presensi</span>
                                </li>
                            </ul>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection

@push('scripts')
    <script>
        // Initialize tooltips
        var tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'))
        var tooltipList = tooltipTriggerList.map(function (tooltipTriggerEl) {
            return new bootstrap.Tooltip(tooltipTriggerEl)
        })
    </script>
@endpush
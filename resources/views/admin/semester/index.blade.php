@extends('layouts.app')

@section('title', 'Master Semester')

@section('content')
    <div class="container-xxl flex-grow-1 container-p-y">
        <div class="row mb-4 align-items-center">
            <div class="col-sm-6">
                <h4 class="mb-0 fw-bold">Master Semester</h4>
                <p class="text-muted mb-0">Daftar periode akademik yang tersinkronisasi dari server berjalan.</p>
            </div>
            <div class="col-sm-6 text-sm-end mt-3 mt-sm-0">
                <!-- Tempat tombol Sync (jika ada API Feeder kelak) -->
            </div>
        </div>

        <!-- Alert Notifikasi -->
        @if(session('success'))
            <div class="alert alert-success alert-dismissible fade show" role="alert">
                <i class="ri-checkbox-circle-line me-2"></i>{{ session('success') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        @endif

        @if(session('error'))
            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                <i class="ri-error-warning-line me-2"></i>{{ session('error') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        @endif

        <!-- Widget Informasi Semester Aktif -->
        <div class="card bg-label-primary shadow-sm mb-4 border-0">
            <div class="card-body d-flex align-items-center">
                <div class="avatar avatar-lg me-3 flex-shrink-0">
                    <span class="avatar-initial rounded bg-primary">
                        <i class="ri-calendar-check-line fs-2"></i>
                    </span>
                </div>
                <div class="d-flex w-100 flex-wrap align-items-center justify-content-between gap-2">
                    <div class="me-2">
                        <h6 class="mb-0 fw-bold text-primary">Periode Akademik Aktif Global</h6>
                        <small class="text-muted">Referensi utama untuk Dashboard, KRS, dan Jadwal Perkuliahan</small>
                    </div>
                    @if($activeSemester)
                        <div class="d-flex align-items-center gap-4">
                            <div class="text-center">
                                <p class="mb-0 text-muted small">Nama Semester</p>
                                <span class="fw-bold">{{ $activeSemester->nama_semester }}</span>
                            </div>
                            <div class="text-center">
                                <p class="mb-0 text-muted small">Tahun Ajaran</p>
                                <span class="fw-bold">{{ $activeSemester->id_tahun_ajaran }}</span>
                            </div>
                            <div class="text-center">
                                <p class="mb-0 text-muted small">ID Semester</p>
                                <span class="badge bg-primary">{{ $activeSemester->id_semester }}</span>
                            </div>
                        </div>
                    @else
                        <span class="badge bg-label-warning fw-bold">Belum Ada Periode Aktif Ditetapkan</span>
                    @endif
                </div>
            </div>
        </div>

        <div class="card shadow-sm border-0">
            <div class="card-body">
                <!-- Informasi Penting -->
                <div class="alert alert-primary d-flex align-items-center mb-4" role="alert">
                    <i class="ri-information-line fs-4 me-3"></i>
                    <div>
                        <strong>Info Single Source of Truth:</strong> Hanya <strong>SATU</strong> semester yang diizinkan
                        berstatus <span class="badge bg-success">Sedang Berjalan</span>.
                        Mengubah semester yang berjalan akan secara otomatis memindahkan semester lama ke Arsip/Riwayat dan
                        mereset filter global sistem.
                    </div>
                </div>

                <div class="table-responsive text-nowrap">
                    <table class="table table-hover table-striped" id="semesterTable">
                        <thead class="table-light">
                            <tr>
                                <th width="5%" class="text-center">No</th>
                                <th>ID Semester</th>
                                <th>Nama Periode</th>
                                <th>Tahun Ajaran</th>
                                <th class="text-center">Mulai - Selesai</th>
                                <th class="text-center">Status Global</th>
                                <th class="text-center" width="15%">Aksi Aktivasi</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($semesters as $index => $semester)
                                <tr>
                                    <td class="text-center">{{ $index + 1 }}</td>
                                    <td><span class="fw-bold text-primary">{{ $semester->id_semester }}</span></td>
                                    <td>{{ $semester->nama_semester }}</td>
                                    <td>{{ $semester->id_tahun_ajaran }}</td>
                                    <td class="text-center">
                                        {{ $semester->tanggal_mulai ? $semester->tanggal_mulai->format('d M Y') : 'N/A' }}
                                        <i class="ri-arrow-right-line mx-1 text-muted"></i>
                                        {{ $semester->tanggal_selesai ? $semester->tanggal_selesai->format('d M Y') : 'N/A' }}
                                    </td>

                                    <!-- KOLOM STATUS -->
                                    <td class="text-center">
                                        @if($semester->a_periode_aktif)
                                            <span class="badge bg-success shadow-sm">
                                                <i class="ri-shield-star-line me-1"></i> Ditetapkan sebagai Aktif
                                            </span>
                                        @else
                                            <span class="badge bg-outline-secondary text-secondary shadow-sm">
                                                Belum Aktif
                                            </span>
                                        @endif
                                    </td>

                                    <!-- KOLOM AKSI (TOMBOL AKTIVASI) -->
                                    <td class="text-center">
                                        @if($semester->a_periode_aktif)
                                            <button class="btn btn-sm btn-success disabled">
                                                <i class="ri-shield-star-line me-1"></i> Ditetapkan sebagai Aktif
                                            </button>
                                        @else
                                            <!-- Form Button Aktifkan -->
                                            <form action="{{ route('admin.semester.set-active', $semester->id_semester) }}"
                                                method="POST" class="d-inline"
                                                onsubmit="return confirm('Menetapkan periode ini akan menonaktifkan referensi periode lainnya. Lanjutkan?');">
                                                @csrf
                                                <button type="submit" class="btn btn-sm btn-outline-primary">
                                                    <i class="ri-toggle-line me-1"></i> Jadikan Periode Aktif
                                                </button>
                                            </form>
                                        @endif
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
@endsection

@push('styles')
    <!-- DataTables CSS -->
    <link rel="stylesheet" href="{{ asset('assets/vendor/libs/datatables-bs5/datatables.bootstrap5.css') }}" />
    <link rel="stylesheet" href="{{ asset('assets/vendor/libs/datatables-responsive-bs5/responsive.bootstrap5.css') }}" />
@endpush

@push('scripts')
    <!-- DataTables JS -->
    <script src="{{ asset('assets/vendor/libs/datatables-bs5/datatables-bootstrap5.js') }}"></script>

    <script>
        $(document).ready(function () {
            $('#semesterTable').DataTable({
                responsive: false, // Matikan responsive accordion bawaan, biar scroll-X
                scrollX: true,
                language: {
                    url: '//cdn.datatables.net/plug-ins/1.13.7/i18n/id.json',
                    searchPlaceholder: "Cari data semester..."
                },
                order: [[1, 'desc']], // Default order by ID Semester descending
                columnDefs: [
                    { orderable: false, targets: [6] } // Aksi tidak bisa diurutkan
                ]
            });
        });
    </script>
@endpush
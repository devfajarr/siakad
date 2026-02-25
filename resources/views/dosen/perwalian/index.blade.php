@extends('layouts.app')

@section('title', 'Daftar Perwalian')

@section('content')
    <h4 class="fw-bold py-3 mb-4"><span class="text-muted fw-light">Dosen /</span> Perwalian</h4>

    <!-- Statistics Cards -->
    <div class="row g-4 mb-4">
        <div class="col-sm-6 col-xl-4">
            <div class="card">
                <div class="card-body">
                    <div class="d-flex align-items-start justify-content-between">
                        <div class="content-left">
                            <span>Total Mahasiswa</span>
                            <div class="d-flex align-items-end mt-2">
                                <h4 class="mb-0 me-2">{{ $stats['total'] }}</h4>
                                <small class="text-muted">Orang</small>
                            </div>
                            <p class="mb-0">Keseluruhan bimbingan</p>
                        </div>
                        <div class="avatar">
                            <span class="avatar-initial rounded bg-label-primary">
                                <i class="ri-user-line ri-24px"></i>
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
                            <span>Menunggu Persetujuan</span>
                            <div class="d-flex align-items-end mt-2">
                                <h4 class="mb-0 me-2 text-warning">{{ $stats['pending'] }}</h4>
                                <small class="text-warning">Mahasiswa</small>
                            </div>
                            <p class="mb-0">Status Pending</p>
                        </div>
                        <div class="avatar">
                            <span class="avatar-initial rounded bg-label-warning">
                                <i class="ri-time-line ri-24px"></i>
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
                            <span>Sudah di-ACC</span>
                            <div class="d-flex align-items-end mt-2">
                                <h4 class="mb-0 me-2 text-success">{{ $stats['acc'] }}</h4>
                                <small class="text-success">Mahasiswa</small>
                            </div>
                            <p class="mb-0">Semester {{ $semesterAktif->nama_semester }}</p>
                        </div>
                        <div class="avatar">
                            <span class="avatar-initial rounded bg-label-success">
                                <i class="ri-check-line ri-24px"></i>
                            </span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="card">
        <div class="card-header border-bottom">
            <h5 class="card-title mb-0">Daftar Mahasiswa Bimbingan</h5>
            <small class="text-muted">Semester Aktif: {{ $semesterAktif->nama_semester }}</small>
        </div>
        <div class="card-datatable table-responsive">
            <table class="table table-hover table-bordered" id="tableBimbingan">
                <thead>
                    <tr>
                        <th style="width: 50px;">No</th>
                        <th>NIM</th>
                        <th>Nama Mahasiswa</th>
                        <th>Program Studi</th>
                        <th>Status KRS</th>
                        <th style="width: 100px;">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($mahasiswaBimbingan as $index => $m)
                        <tr>
                            <td>{{ $index + 1 }}</td>
                            <td><span class="fw-bold">{{ $m->nim }}</span></td>
                            <td>{{ $m->nama_mahasiswa }}</td>
                            <td>{{ $m->riwayatAktif?->programStudi?->nama_program_studi }}</td>
                            <td>{!! $m->status_krs_label !!}</td>
                            <td>
                                <a href="{{ route('dosen.perwalian.show', $m->id) }}" class="btn btn-sm btn-primary">
                                    <i class="ri-eye-line me-1"></i> Lihat KRS
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
                $('#tableBimbingan').DataTable({
                    language: {
                        url: "//cdn.datatables.net/plug-ins/1.10.24/i18n/Indonesian.json"
                    }
                });
            }
        });
    </script>
@endpush
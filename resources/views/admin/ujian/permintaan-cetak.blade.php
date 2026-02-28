@extends('layouts.app')

@section('title', 'Permintaan Cetak Kartu Ujian')

@section('content')
    <div class="container-xxl flex-grow-1 container-p-y">
        <div class="row mb-4 align-items-center">
            <div class="col-sm-6">
                <h4 class="mb-0 fw-bold">Permintaan Cetak Kartu Ujian</h4>
                <p class="text-muted mb-0">Daftar mahasiswa yang mengajukan cetak kartu ujian.</p>
            </div>
            <div class="col-sm-6 text-sm-end mt-3 mt-sm-0">
                <a href="{{ route('admin.ujian.index', ['id_semester' => $idSemester]) }}" class="btn btn-label-secondary">
                    <i class="ri-arrow-left-line me-1"></i> Kembali ke Jadwal
                </a>
            </div>
        </div>

        <!-- Alert -->
        @if(session('success'))
            <div class="alert alert-success alert-dismissible fade show" role="alert">
                <i class="ri-checkbox-circle-line me-2"></i>{{ session('success') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        @endif

        <!-- Filter Semester -->
        <div class="card mb-4 shadow-sm border-0">
            <div class="card-body py-3">
                <form action="{{ route('admin.ujian.permintaan-cetak') }}" method="GET" class="row align-items-center">
                    <div class="col-auto">
                        <label class="form-label mb-0 fw-bold">Semester:</label>
                    </div>
                    <div class="col-auto">
                        <select name="id_semester" class="form-select" onchange="this.form.submit()">
                            @foreach($semesters as $sem)
                                <option value="{{ $sem->id_semester }}" {{ $idSemester == $sem->id_semester ? 'selected' : '' }}>
                                    {{ $sem->nama_semester }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                </form>
            </div>
        </div>

        <!-- Tabel Permintaan -->
        <div class="card shadow-sm border-0">
            <div class="card-body">
                <div class="table-responsive text-nowrap">
                    <table class="table table-hover table-striped" id="permintaanCetakTable">
                        <thead class="table-light">
                            <tr>
                                <th width="5%" class="text-center">No</th>
                                <th>Mahasiswa</th>
                                <th>NIM</th>
                                <th>Mata Kuliah</th>
                                <th class="text-center">Tipe Ujian</th>
                                <th>Diminta Pada</th>
                                <th class="text-center" width="15%">Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($permintaan as $index => $item)
                                @php
                                    $riwayat = $item->pesertaKelasKuliah->riwayatPendidikan ?? null;
                                    $mhs = $riwayat->mahasiswa ?? null;
                                @endphp
                                <tr>
                                    <td class="text-center">{{ $index + 1 }}</td>
                                    <td class="fw-bold">{{ $mhs->nama_mahasiswa ?? '-' }}</td>
                                    <td><span class="text-primary">{{ $riwayat->nim ?? '-' }}</span></td>
                                    <td>{{ $item->jadwalUjian->kelasKuliah->mataKuliah->nama_mk ?? '-' }}</td>
                                    <td class="text-center">
                                        <span
                                            class="badge bg-{{ $item->jadwalUjian->tipe_ujian === 'UTS' ? 'info' : 'primary' }}">
                                            {{ $item->jadwalUjian->tipe_ujian }}
                                        </span>
                                    </td>
                                    <td>{{ $item->diminta_pada ? $item->diminta_pada->format('d M Y H:i') : '-' }}</td>
                                    <td class="text-center">
                                        <div class="btn-group btn-group-sm">
                                            <a href="{{ route('admin.ujian.print-kartu', $item->id) }}" target="_blank"
                                                class="btn btn-sm btn-outline-primary" data-bs-toggle="tooltip"
                                                title="Preview & Cetak">
                                                <i class="ri-printer-line me-1"></i> Cetak
                                            </a>
                                            <form action="{{ route('admin.ujian.cetak-kartu', $item->id) }}" method="POST"
                                                class="d-inline">
                                                @csrf
                                                <button type="submit" class="btn btn-sm btn-success" data-bs-toggle="tooltip"
                                                    title="Tandai Sudah Dicetak">
                                                    <i class="ri-checkbox-circle-line me-1"></i> Selesai
                                                </button>
                                            </form>
                                        </div>
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
    <link rel="stylesheet" href="{{ asset('assets/vendor/libs/datatables-bs5/datatables.bootstrap5.css') }}" />
@endpush

@push('scripts')
    <script src="{{ asset('assets/vendor/libs/datatables-bs5/datatables-bootstrap5.js') }}"></script>
    <script>
        $(document).ready(function () {
            $('#permintaanCetakTable').DataTable({
                responsive: false,
                scrollX: true,
                language: {
                    search: "Cari:",
                    searchPlaceholder: "Cari permintaan...",
                    lengthMenu: "Tampilkan _MENU_ data",
                    zeroRecords: "Tidak ada permintaan cetak saat ini.",
                    info: "Menampilkan _START_ - _END_ dari _TOTAL_ data",
                    infoEmpty: "Tidak ada data",
                    infoFiltered: "(difilter dari _MAX_ total data)",
                    paginate: { first: "Awal", last: "Akhir", next: "\u203A", previous: "\u2039" }
                },
                order: [[5, 'asc']],
                columnDefs: [
                    { orderable: false, targets: [6] }
                ]
            });
        });
    </script>
@endpush
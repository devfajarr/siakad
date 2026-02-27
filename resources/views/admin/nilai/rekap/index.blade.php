@extends('layouts.app')

@section('title', 'Rekapitulasi Nilai - Progres Prodi')

@push('styles')
    <link rel="stylesheet" href="{{ asset('assets/vendor/libs/datatables-bs5/datatables.bootstrap5.css') }}" />
    <link rel="stylesheet" href="{{ asset('assets/vendor/libs/datatables-responsive-bs5/responsive.bootstrap5.css') }}" />
    <link rel="stylesheet" href="{{ asset('assets/vendor/libs/select2/select2.css') }}" />
@endpush

@section('content')
    <div class="container-xxl flex-grow-1 container-p-y">
        <div class="d-flex justify-content-between align-items-center py-3 mb-4">
            <h4 class="fw-bold mb-0">
                <span class="text-muted fw-light">Akademik /</span> Rekapitulasi Nilai
            </h4>
        </div>

        <div class="card mb-4">
            <div class="card-body">
                <form action="{{ route('admin.rekap-nilai.index') }}" method="GET" class="row g-3 align-items-center">
                    <div class="col-md-4">
                        <label class="form-label">Pilih Semester</label>
                        <select name="semester_id" class="form-select select2" id="semester_id"
                            onchange="this.form.submit()">
                            @foreach($semesters as $s)
                                <option value="{{ $s->id_semester }}" {{ $s->id_semester == $semesterId ? 'selected' : '' }}>
                                    {{ $s->nama_semester }}
                                    @if($s->id_semester == $activeSemester->id_semester)
                                        (Semester Aktif)
                                    @endif
                                </option>
                            @endforeach
                        </select>
                    </div>
                </form>
            </div>
        </div>

        <div class="card">
            <div class="card-datatable table-responsive">
                <table class="table table-hover" id="tableRekap">
                    <thead class="table-light">
                        <tr>
                            <th width="50">No</th>
                            <th>Program Studi</th>
                            <th class="text-center">Kelas</th>
                            <th class="text-center">Peserta</th>
                            <th class="text-center">Terisi</th>
                            <th width="200">Progres</th>
                            <th width="100" class="text-center">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($prodiStats as $index => $row)
                            <tr>
                                <td>{{ $index + 1 }}</td>
                                <td>
                                    <span class="fw-bold">{{ $row->nama_prodi }}</span>
                                </td>
                                <td class="text-center">
                                    <span class="badge bg-label-secondary">
                                        {{ $row->kelas_locked }} / {{ $row->total_kelas }} Locked
                                    </span>
                                </td>
                                <td class="text-center">{{ number_format($row->total_mhs) }}</td>
                                <td class="text-center">{{ number_format($row->total_terisi) }}</td>
                                <td>
                                    <div class="d-flex align-items-center">
                                        <div class="progress w-100 me-2" style="height: 8px;">
                                            <div class="progress-bar {{ $row->persentase == 100 ? 'bg-success' : ($row->persentase > 50 ? 'bg-primary' : 'bg-warning') }}"
                                                role="progressbar" style="width: {{ $row->persentase }}%"
                                                aria-valuenow="{{ $row->persentase }}" aria-valuemin="0" aria-valuemax="100">
                                            </div>
                                        </div>
                                        <small>{{ $row->persentase }}%</small>
                                    </div>
                                </td>
                                <td class="text-center">
                                    <a href="{{ route('admin.rekap-nilai.show', [$row->id_prodi, 'semester_id' => $semesterId]) }}"
                                        class="btn btn-sm btn-icon btn-outline-primary" title="Detail Kelas">
                                        <i class="ri-eye-line"></i>
                                    </a>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>
@endsection

@push('scripts')
    <script src="{{ asset('assets/vendor/libs/datatables-bs5/datatables-bootstrap5.js') }}"></script>
    <script src="{{ asset('assets/vendor/libs/select2/select2.js') }}"></script>
    <script>
        $(document).ready(function () {
            // Inisialisasi Select2
            if ($.fn.select2) {
                $('.select2').select2({
                    width: '100%',
                    placeholder: "-- Pilih Semester --",
                    allowClear: true
                });
            }

            // Inisialisasi DataTables
            if ($.fn.DataTable) {
                $('#tableRekap').DataTable({
                    language: {
                        search: "Cari:",
                        lengthMenu: "Tampilkan _MENU_ data",
                        info: "Menampilkan _START_ sampai _END_ dari _TOTAL_ data",
                        infoEmpty: "Data tidak ditemukan",
                        zeroRecords: "Data tidak ditemukan",
                        paginate: {
                            first: "Pertama",
                            last: "Terakhir",
                            next: '<i class="ri-arrow-right-s-line"></i>',
                            previous: '<i class="ri-arrow-left-s-line"></i>'
                        }
                    },
                    dom: '<"card-header flex-column flex-md-row"<"head-label text-center"><"dt-action-buttons text-end pt-3 pt-md-0"f>>t<"row"<"col-sm-12 col-md-6"i><"col-sm-12 col-md-6"p>>',
                    displayLength: 25,
                    lengthMenu: [10, 25, 50, 75, 100],
                    order: [[0, 'asc']]
                });

                // Populate head-label with title and semester info
                $('div.head-label').html('<h5 class="card-title mb-0">Progres Pengisian Nilai per Program Studi <small class="text-muted">(Semester: {{ $selectedSemester->nama_semester ?? $semesterId }})</small></h5>');
            }
        });
    </script>
@endpush
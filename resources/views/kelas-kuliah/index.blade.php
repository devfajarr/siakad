@extends('layouts.app')

@section('title', 'Manajemen Kelas Kuliah')

@push('styles')
    <link rel="stylesheet" href="{{ asset('assets/vendor/libs/datatables-bs5/datatables.bootstrap5.css') }}" />
    <link rel="stylesheet" href="{{ asset('assets/vendor/libs/datatables-responsive-bs5/responsive.bootstrap5.css') }}" />
    <link rel="stylesheet" href="{{ asset('assets/vendor/libs/datatables-buttons-bs5/buttons.bootstrap5.css') }}" />
    <link rel="stylesheet" href="{{ asset('assets/vendor/libs/select2/select2.css') }}" />
@endpush

@section('content')
    {{-- Page Header --}}
    <h4 class="fw-bold py-3 mb-2"><span class="text-muted fw-light">Perkuliahan /</span> Kelas Kuliah</h4>

    <div class="card">
        <div class="card-header border-bottom">
            <div class="d-flex flex-wrap justify-content-between align-items-center gap-3">
                <div class="d-flex align-items-center gap-2">
                    <h5 class="card-title mb-0">Daftar Kelas Kuliah</h5>
                    @if(isset($activeSemester))
                        <span class="badge bg-label-primary rounded-pill">
                            <i class="ri-calendar-line me-1"></i> Semester: {{ $activeSemester->nama_semester }}
                        </span>
                    @endif
                </div>

                <div class="d-flex gap-2 align-items-center flex-wrap">
                    <button type="button" class="btn btn-outline-secondary dropdown-toggle" data-bs-toggle="dropdown"
                        aria-expanded="false">
                        <i class="ri-filter-3-line me-1"></i> Filter / Sort
                    </button>
                    <ul class="dropdown-menu p-3" style="width: 300px;">
                        <div class="mb-3">
                            <label for="filter_semester" class="form-label">Semester</label>
                            <select id="filter_semester" class="form-select select2">
                                <option value="">Semua Semester</option>
                                @foreach($semesters as $semester)
                                    <option value="{{ $semester->id_semester }}" {{ isset($activeSemester) && $semester->id_semester == $activeSemester->id_semester ? 'selected' : '' }}>
                                        {{ $semester->nama_semester }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                        <div class="mb-3">
                            <label for="filter_status" class="form-label">Status Sinkronisasi</label>
                            <select id="filter_status" class="form-select">
                                <option value="">Semua Status</option>
                                <option value="synced">Sudah Sync</option>
                                <option value="pending_push">Pending Push</option>
                                <option value="created_local">Lokal (Belum Sync)</option>
                            </select>
                        </div>
                        <div class="d-grid">
                            <button type="button" class="btn btn-primary btn-apply-filter">Terapkan</button>
                        </div>
                    </ul>

                    <a href="{{ route('admin.kelas-kuliah.create') }}" class="btn btn-primary waves-effect waves-light">
                        <i class="ri-add-line me-1"></i> Tambah
                    </a>
                </div>
            </div>
        </div>

        <div class="card-datatable table-responsive">
            <table id="kelasKuliahTable" class="table table-bordered table-hover text-nowrap">
                <thead class="table-light">
                    <tr>
                        <th width="100">Action</th>
                        <th>Status</th>
                        <th width="50">No</th>
                        <th>Semester</th>
                        <th>Kode MK</th>
                        <th>Nama Mata Kuliah</th>
                        <th>Nama Kelas</th>
                        <th>Bobot (sks)</th>
                        <th>Dosen Pengajar</th>
                        <th>Peserta</th>
                    </tr>
                </thead>
                <tbody>
                    {{-- Data loaded via Ajax --}}
                </tbody>
            </table>
        </div>
    </div>
@endsection

@push('scripts')
    <script src="{{ asset('assets/vendor/libs/datatables-bs5/datatables-bootstrap5.js') }}"></script>
    <script src="{{ asset('assets/vendor/libs/select2/select2.js') }}"></script>

    <script>
        $(function () {
            // Select2 init
            $('.select2').select2({
                dropdownParent: $('.dropdown-menu') // Fix select2 inside dropdown
            });

            // DataTables init
            let table = $('#kelasKuliahTable').DataTable({
                processing: true,
                serverSide: true,
                ajax: {
                    url: "{{ route('admin.kelas-kuliah.index') }}",
                    data: function (d) {
                        d.id_semester = $('#filter_semester').val();
                        d.status_sinkronisasi = $('#filter_status').val();
                    }
                },
                columns: [
                    { data: 'action', name: 'action', orderable: false, searchable: false },
                    { data: 'status', name: 'status', orderable: false, searchable: false },
                    { data: 'DT_RowIndex', name: 'DT_RowIndex', orderable: false, searchable: false },
                    { data: 'semester_nama', name: 'semester.nama_semester' },
                    { data: 'kode_mk', name: 'mataKuliah.kode_mk' },
                    { data: 'nama_mk', name: 'mataKuliah.nama_mk' },
                    { data: 'nama_kelas_kuliah', name: 'nama_kelas_kuliah' },
                    { data: 'bobot_sks', name: 'sks_mk' }, // Assuming sks_mk in kelas_kuliah or mata_kuliah
                    { data: 'dosen_pengajar', name: 'dosen_pengajar', orderable: false, searchable: false }, // Needs relationship search logic if searchable
                    { data: 'peserta_kelas', name: 'peserta_kelas', orderable: false, searchable: false },
                ],
                order: [[3, 'desc'], [5, 'asc']], // Order by Semester desc, then Nama MK asc
                responsive: false, // Requirement said "tidak ada kolom yang collapse/hidden di mobile", but table-responsive wrapper handles scroll
                scrollX: true,
                pageLength: 10,
                language: {
                    search: '',
                    searchPlaceholder: 'Cari Kelas / MK...',
                    lengthMenu: '_MENU_',
                    info: 'Menampilkan _START_ - _END_ dari _TOTAL_ data',
                    infoEmpty: 'Tidak ada data',
                    emptyTable: 'Tidak ada data kelas kuliah.',
                    processing: '<div class="spinner-border text-primary" role="status"><span class="visually-hidden">Loading...</span></div>',
                    paginate: {
                        first: '«',
                        last: '»',
                        next: '›',
                        previous: '‹'
                    }
                },
                dom: '<"row"<"col-sm-12 col-md-6"l><"col-sm-12 col-md-6 d-flex justify-content-center justify-content-md-end"f>>t<"row"<"col-sm-12 col-md-6"i><"col-sm-12 col-md-6"p>>',
            });

            // Custom Filter
            $('.btn-apply-filter').on('click', function () {
                table.draw();
                // Update badge text if needed or simple redirect/reload if structure requires it
                // For now just redraw standard ajax

                // Close dropdown
                $(this).closest('.dropdown-menu').removeClass('show');
                $(this).closest('.dropdown').find('.dropdown-toggle').removeClass('show');
            });

            // Delete Confirmation
            $(document).on('click', '.btn-delete', function () {
                let form = $(this).closest('form');
                Swal.fire({
                    title: 'Apakah Anda yakin?',
                    text: "Data yang dihapus tidak dapat dikembalikan!",
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonColor: '#3085d6',
                    cancelButtonColor: '#d33',
                    confirmButtonText: 'Ya, hapus!',
                    cancelButtonText: 'Batal'
                }).then((result) => {
                    if (result.isConfirmed) {
                        form.submit();
                    }
                });
            });
        });
    </script>
@endpush
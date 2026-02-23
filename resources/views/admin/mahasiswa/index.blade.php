@extends('layouts.app')

@section('title', 'Manajemen Data Mahasiswa')

@push('styles')
    <link rel="stylesheet" href="{{ asset('assets/vendor/libs/select2/select2.css') }}" />
    <link rel="stylesheet" href="{{ asset('assets/vendor/libs/datatables-bs5/datatables.bootstrap5.css') }}" />
    <link rel="stylesheet" href="{{ asset('assets/vendor/libs/datatables-responsive-bs5/responsive.bootstrap5.css') }}" />
    <link rel="stylesheet"
        href="{{ asset('assets/vendor/libs/datatables-checkboxes-jquery/datatables.checkboxes.css') }}" />
    <link rel="stylesheet" href="{{ asset('assets/vendor/libs/datatables-buttons-bs5/buttons.bootstrap5.css') }}" />
    <link rel="stylesheet" href="{{ asset('assets/vendor/libs/flatpickr/flatpickr.css') }}" />
    <link rel="stylesheet" href="{{ asset('assets/vendor/libs/datatables-rowgroup-bs5/rowgroup.bootstrap5.css') }}" />
    <link rel="stylesheet" href="{{ asset('assets/vendor/libs/@form-validation/form-validation.css') }}" />
@endpush

@section('content')
    <div class="card">

        <div class="table-responsive pt-2">
            <table class="datatables-basic table table-bordered table-hover text-nowrap">
                <thead class="table-light">
                    <tr>
                        <th width="30px">
                            <input type="checkbox" class="form-check-input" id="checkAll">
                        </th>
                        <th width="100px">Action</th>
                        <th width="120px">Status</th>
                        <th width="50px">No</th>
                        <th>Nama</th>
                        <th>NIM</th>
                        <th>Program Studi</th>
                        <th>Tahun Angkatan</th>
                        <th>Jenis Kelamin</th>
                        <th>Agama</th>
                        <th>Total SKS Diambil</th>
                        <th>Tanggal Lahir</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($mahasiswa as $index => $item)
                        <tr>
                            <td>
                                @if (is_null($item->user_id))
                                    <input type="checkbox" class="form-check-input mahasiswa-checkbox" name="mahasiswa_ids[]" value="{{ $item->id }}">
                                @endif
                            </td>
                            <td>
                                <div class="d-flex gap-2">
                                    <a href="{{ route('admin.mahasiswa.show', $item->id) }}" class="btn btn-icon btn-sm btn-info rounded-pill" title="Detail">
                                        <i class="ri-search-line"></i>
                                    </a>
                                    <a href="{{ route('admin.mahasiswa.edit', $item->id) }}" class="btn btn-icon btn-sm btn-warning rounded-pill" title="Edit">
                                        <i class="ri-pencil-line"></i>
                                    </a>
                                    <form action="{{ route('admin.mahasiswa.destroy', $item->id) }}" method="POST" class="d-inline" onsubmit="return confirm('Apakah Anda yakin ingin menghapus data ini?');">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="btn btn-icon btn-sm btn-danger rounded-pill" title="Delete">
                                            <i class="ri-delete-bin-line"></i>
                                        </button>
                                    </form>
                                    @if(is_null($item->user_id) && !empty($item->riwayatAktif->nim))
                                        <form action="{{ route('admin.mahasiswa.generate-user', $item->id) }}" method="POST" class="d-inline">
                                            @csrf
                                            <button type="submit" class="btn btn-icon btn-sm btn-success rounded-pill" title="Generate Akun Login">
                                                <i class="ri-user-add-line"></i>
                                            </button>
                                        </form>
                                    @endif
                                </div>
                            </td>
                            <td>
                                @if ($item->is_synced)
                                    <span class="badge bg-success rounded-pill"> <i class="ri-check-line me-1"></i> sudah sync</span>
                                @else
                                    <span class="badge bg-warning rounded-pill"> <i class="ri-time-line me-1"></i> belum sync</span>
                                @endif
                            </td>
                            <td>{{ $index + 1 }}</td>
                            <td>
                                <span class="fw-bold text-primary">{{ $item->nama_mahasiswa }}</span>
                            </td>
                            <td>{{ $item->riwayatAktif->nim ?? '-' }}</td>
                            <td>{{ $item->riwayatAktif->prodi->nama_program_studi ?? '-' }}</td>
                            <td>{{ $item->riwayatAktif->semester->id_tahun_ajaran ?? '-' }}</td>
                            <td>{{ $item->jenis_kelamin == 'L' ? 'Laki - Laki' : 'Perempuan' }}</td>
                            <td>{{ $item->agama->nama_agama ?? '-' }}</td>
                            <td class="text-center">
                                <span class="fw-bold">{{ floatval($item->total_sks) }}</span>
                            </td>
                            <td>{{ $item->tanggal_lahir ? $item->tanggal_lahir->format('d/m/Y') : '-' }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        <div class="card-footer py-2">
             {{-- Pagination handled by DataTables --}}
        </div>
    </div>

    <!-- Modal Filter -->
    <div class="modal fade" id="modalFilter" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="modalFilterTitle">Filter Data Mahasiswa</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <form action="{{ route('admin.mahasiswa.index') }}" method="GET">
                    <div class="modal-body">
                        <div class="row g-3">
                            <div class="col-12">
                                <label class="form-label">Tahun Angkatan / Semester Masuk</label>
                                <select class="form-select select2-filter" name="periode_masuk" data-placeholder="-- Semua Angkatan --">
                                    <option value="">-- Semua Angkatan --</option>
                                    @foreach($semesters as $smt)
                                        <option value="{{ $smt->id_semester }}" {{ request('periode_masuk') == $smt->id_semester ? 'selected' : '' }}>
                                            {{ $smt->nama_semester }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-12">
                                <label class="form-label">Program Studi</label>
                                <select class="form-select select2-filter" name="prodi" data-placeholder="-- Semua Program Studi --">
                                    <option value="">-- Semua Program Studi --</option>
                                    @foreach($prodis as $prd)
                                        <option value="{{ $prd->id_prodi }}" {{ request('prodi') == $prd->id_prodi ? 'selected' : '' }}>
                                            {{ $prd->nama_program_studi }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer d-flex justify-content-between">
                        <a href="{{ route('admin.mahasiswa.index') }}" class="btn btn-outline-secondary">Reset</a>
                        <div>
                            <button type="button" class="btn btn-outline-secondary me-2" data-bs-dismiss="modal">Batal</button>
                            <button type="submit" class="btn btn-primary">Terapkan Filter</button>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>
@endsection

@push('scripts')
    <script src="{{ asset('assets/vendor/libs/datatables-bs5/datatables-bootstrap5.js') }}"></script>
    <script src="{{ asset('assets/vendor/libs/moment/moment.js') }}"></script>
    <script src="{{ asset('assets/vendor/libs/flatpickr/flatpickr.js') }}"></script>
    <script src="{{ asset('assets/vendor/libs/select2/select2.js') }}"></script>
    <script src="{{ asset('assets/vendor/libs/@form-validation/popular.js') }}"></script>
    <script src="{{ asset('assets/vendor/libs/@form-validation/bootstrap5.js') }}"></script>
    <script src="{{ asset('assets/vendor/libs/@form-validation/auto-focus.js') }}"></script>

    <script>
        $(function () {
            var dt_basic_table = $('.datatables-basic');

            if (dt_basic_table.length) {
                var dt_basic = dt_basic_table.DataTable({
                    columnDefs: [{
                        // Actions
                        targets: 0,
                        orderable: false,
                        searchable: false,
                    }
                    ],
                    dom: '<"card-header flex-column flex-md-row border-bottom"<"head-label text-center"><"dt-action-buttons text-end pt-3 pt-md-0"B>><"row"<"col-sm-12 col-md-6 mt-5 mt-md-0"l><"col-sm-12 col-md-6 d-flex justify-content-center justify-content-md-end"f>>t<"row"<"col-sm-12 col-md-6"i><"col-sm-12 col-md-6"p>>',
                    displayLength: 7,
                    lengthMenu: [7, 10, 25, 50, 75, 100],
                    buttons: [
                        {
                            text: '<i class="ri-filter-3-line ri-16px me-sm-1"></i> <span class="d-none d-sm-inline-block">Filter</span>',
                            className: 'btn btn-outline-secondary me-2 waves-effect waves-light',
                            action: function (e, dt, node, config) {
                                $('#modalFilter').modal('show');
                            }
                        },
                        {
                            extend: 'collection',
                            className: 'btn btn-label-primary dropdown-toggle me-4 waves-effect waves-light',
                            text: '<i class="ri-external-link-line me-sm-1"></i> <span class="d-none d-sm-inline-block">Export</span>',
                            buttons: [
                                {
                                    extend: 'print',
                                    text: '<i class="ri-printer-line me-1" ></i>Print',
                                    className: 'dropdown-item',
                                    exportOptions: {
                                        columns: [0, 1, 2, 3, 4, 5, 6],
                                    }
                                },
                                {
                                    extend: 'csv',
                                    text: '<i class="ri-file-text-line me-1" ></i>Csv',
                                    className: 'dropdown-item',
                                    exportOptions: {
                                        columns: [0, 1, 2, 3, 4, 5, 6],
                                    }
                                },
                                {
                                    extend: 'excel',
                                    text: '<i class="ri-file-excel-line me-1"></i>Excel',
                                    className: 'dropdown-item',
                                    exportOptions: {
                                        columns: [0, 1, 2, 3, 4, 5, 6],
                                    }
                                },
                                {
                                    extend: 'pdf',
                                    text: '<i class="ri-file-pdf-line me-1"></i>Pdf',
                                    className: 'dropdown-item',
                                    exportOptions: {
                                        columns: [0, 1, 2, 3, 4, 5, 6],
                                    }
                                },
                                {
                                    extend: 'copy',
                                    text: '<i class="ri-file-copy-line me-1" ></i>Copy',
                                    className: 'dropdown-item',
                                    exportOptions: {
                                        columns: [0, 1, 2, 3, 4, 5, 6],
                                    }
                                }
                            ]
                        },
                        {
                            text: '<i class="ri-user-add-line ri-16px me-sm-1"></i> <span class="d-none d-sm-inline-block">Buat Akun Kolektif</span>',
                            className: 'btn btn-success waves-effect waves-light me-2',
                            action: function (e, dt, node, config) {
                                let selectedIds = [];
                                $('.mahasiswa-checkbox:checked').each(function () {
                                    selectedIds.push($(this).val());
                                });

                                if (selectedIds.length === 0) {
                                    alert('Silakan pilih minimal satu mahasiswa untuk dibuatkan akun.');
                                    return;
                                }

                                if (confirm(`Apakah Anda yakin ingin membuat akun secara massal untuk ${selectedIds.length} mahasiswa terpilih?`)) {
                                    let form = $('<form>', {
                                        'method': 'POST',
                                        'action': '{{ route("admin.mahasiswa.bulk-generate-users") }}'
                                    });

                                    form.append($('<input>', {
                                        'type': 'hidden',
                                        'name': '_token',
                                        'value': '{{ csrf_token() }}'
                                    }));

                                    selectedIds.forEach(function (id) {
                                        form.append($('<input>', {
                                            'type': 'hidden',
                                            'name': 'mahasiswa_ids[]',
                                            'value': id
                                        }));
                                    });

                                    $(document.body).append(form);
                                    form.submit();
                                }
                            }
                        },
                        {
                            text: '<i class="ri-add-line ri-16px me-sm-1"></i> <span class="d-none d-sm-inline-block">Tambah Mahasiswa</span>',
                            className: 'create-new btn btn-primary waves-effect waves-light',
                            action: function (e, dt, node, config) {
                                window.location.href = "{{ route('admin.mahasiswa.create') }}";
                            }
                        }
                    ],
                    responsive: false,
                    scrollX: true
                });

                // Handle select all checkbox
                $('#checkAll').on('change', function () {
                    var isChecked = $(this).prop('checked');
                    $('.mahasiswa-checkbox').prop('checked', isChecked);
                });

                // Update select all checkbox state when individual checkboxes change
                $('.datatables-basic').on('change', '.mahasiswa-checkbox', function () {
                    var totalCheckboxes = $('.mahasiswa-checkbox').length;
                    var checkedCheckboxes = $('.mahasiswa-checkbox:checked').length;
                    
                    if (totalCheckboxes === checkedCheckboxes) {
                        $('#checkAll').prop('checked', true);
                        $('#checkAll').prop('indeterminate', false);
                    } else if (checkedCheckboxes > 0) {
                        $('#checkAll').prop('checked', false);
                        $('#checkAll').prop('indeterminate', true);
                    } else {
                        $('#checkAll').prop('checked', false);
                        $('#checkAll').prop('indeterminate', false);
                    }
                });

                let filterBadge = '';
                @if(request()->filled('periode_masuk') || request()->filled('prodi'))
                    filterBadge = '<span class="badge bg-primary ms-2 fs-6"><i class="ri-filter-fill"></i> Data Difilter</span>';
                @endif
                $('div.head-label').html('<h5 class="card-title mb-0">Daftar Mahasiswa' + filterBadge + '</h5>');
            }

            if ($('.select2-filter').length) {
                $('.select2-filter').select2({
                    dropdownParent: $('#modalFilter'),
                    width: '100%',
                    allowClear: true
                });
            }
        });
    </script>
@endpush
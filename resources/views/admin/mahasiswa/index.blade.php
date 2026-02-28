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
    <style>
        /* Fix toggle switch shrinking inside DataTables scrollX */
        .dataTables_scrollBody .form-check.form-switch {
            min-width: 120px !important;
            padding-left: 2.5em !important;
            display: flex !important;
            align-items: center !important;
            gap: 0.4em !important;
        }

        .dataTables_scrollBody .form-check.form-switch .form-check-input {
            width: 2.5em !important;
            min-width: 2.5em !important;
            height: 1.3em !important;
            flex-shrink: 0 !important;
            cursor: pointer !important;
            margin-top: 0 !important;
        }

        .dataTables_scrollBody .form-check.form-switch .form-check-label {
            white-space: nowrap !important;
            cursor: pointer !important;
        }
    </style>
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
                        <th width="140px">Tipe Kelas</th>
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
                                    <input type="checkbox" class="form-check-input mahasiswa-checkbox" name="mahasiswa_ids[]"
                                        value="{{ $item->id }}">
                                @endif
                            </td>
                            <td>
                                <div class="d-flex gap-2">
                                    <a href="{{ route('admin.mahasiswa.show', $item->id) }}"
                                        class="btn btn-icon btn-sm btn-info rounded-pill" title="Detail">
                                        <i class="ri-search-line"></i>
                                    </a>
                                    <a href="{{ route('admin.mahasiswa.edit', $item->id) }}"
                                        class="btn btn-icon btn-sm btn-warning rounded-pill" title="Edit">
                                        <i class="ri-pencil-line"></i>
                                    </a>
                                    <form action="{{ route('admin.mahasiswa.destroy', $item->id) }}" method="POST"
                                        class="d-inline"
                                        onsubmit="return confirm('Apakah Anda yakin ingin menghapus data ini?');">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="btn btn-icon btn-sm btn-danger rounded-pill"
                                            title="Delete">
                                            <i class="ri-delete-bin-line"></i>
                                        </button>
                                    </form>
                                    @if(is_null($item->user_id) && !empty($item->riwayatAktif->nim))
                                        <form action="{{ route('admin.mahasiswa.generate-user', $item->id) }}" method="POST"
                                            class="d-inline">
                                            @csrf
                                            <button type="submit" class="btn btn-icon btn-sm btn-success rounded-pill"
                                                title="Generate Akun Login">
                                                <i class="ri-user-add-line"></i>
                                            </button>
                                        </form>
                                    @endif
                                </div>
                            </td>
                            <td>
                                @if ($item->is_synced)
                                    <span class="badge bg-success rounded-pill"> <i class="ri-check-line me-1"></i> sudah
                                        sync</span>
                                @else
                                    <span class="badge bg-warning rounded-pill"> <i class="ri-time-line me-1"></i> belum sync</span>
                                @endif
                            </td>
                            <td>{{  $loop->iteration }}</td>
                            <td>
                                <span class="fw-bold text-primary">{{ $item->nama_mahasiswa }}</span>
                            </td>
                            <td>{{ $item->riwayatAktif->nim ?? '-' }}</td>
                            <td>{{ $item->riwayatAktif->prodi->nama_program_studi ?? '-' }}</td>
                            <td>{{ $item->riwayatAktif->semester->id_tahun_ajaran ?? '-' }}</td>
                            <td>
                                <div class="form-check form-switch mb-0" style="min-width: 110px; padding-left: 2.5em;">
                                    <input class="form-check-input toggle-tipe-kelas" type="checkbox" role="switch"
                                        style="width: 2.5em; height: 1.3em;" data-id="{{ $item->id }}" {{ $item->tipe_kelas === 'Sore' ? 'checked' : '' }}>
                                    <label class="form-check-label tipe-label-{{ $item->id }}" style="white-space: nowrap;">
                                        @if($item->tipe_kelas === 'Sore')
                                            <span class="badge bg-warning">Sore</span>
                                        @else
                                            <span class="badge bg-info">{{ $item->tipe_kelas ?: '-' }}</span>
                                        @endif
                                    </label>
                                </div>
                            </td>
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
                                <select class="form-select select2-filter" name="periode_masuk"
                                    data-placeholder="-- Semua Angkatan --">
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
                                <select class="form-select select2-filter" name="prodi"
                                    data-placeholder="-- Semua Program Studi --">
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
                            <button type="button" class="btn btn-outline-secondary me-2"
                                data-bs-dismiss="modal">Batal</button>
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
                    order: [],
                    columnDefs: [
                        {
                            targets: [0, 1],
                            orderable: false,
                            searchable: false,
                        },
                        {
                            targets: 8,
                            orderable: false,
                            searchable: false,
                            width: '140px'
                        }
                    ],
                    dom: '<"card-header flex-column flex-md-row border-bottom pb-0"<"head-label text-center"><"dt-action-buttons text-end pt-3 pt-md-0"B>>' +
                        '<"row px-3 py-3"<"col-sm-12 col-md-6"l><"col-sm-12 col-md-6 d-flex justify-content-center justify-content-md-end"f>>' +
                        't' +
                        '<"row px-3 py-2"<"col-sm-12 col-md-6"i><"col-sm-12 col-md-6"p>>',
                    displayLength: 25,
                    lengthMenu: [10, 25, 50, 100],
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
                                        columns: [3, 4, 5, 6, 7, 9, 10, 11, 12],
                                    }
                                },
                                {
                                    extend: 'csv',
                                    text: '<i class="ri-file-text-line me-1" ></i>Csv',
                                    className: 'dropdown-item',
                                    exportOptions: {
                                        columns: [3, 4, 5, 6, 7, 9, 10, 11, 12],
                                    }
                                },
                                {
                                    extend: 'excel',
                                    text: '<i class="ri-file-excel-line me-1"></i>Excel',
                                    className: 'dropdown-item',
                                    exportOptions: {
                                        columns: [3, 4, 5, 6, 7, 9, 10, 11, 12],
                                    }
                                },
                                {
                                    extend: 'pdf',
                                    text: '<i class="ri-file-pdf-line me-1"></i>Pdf',
                                    className: 'dropdown-item',
                                    exportOptions: {
                                        columns: [3, 4, 5, 6, 7, 9, 10, 11, 12],
                                    }
                                },
                                {
                                    extend: 'copy',
                                    text: '<i class="ri-file-copy-line me-1" ></i>Copy',
                                    className: 'dropdown-item',
                                    exportOptions: {
                                        columns: [3, 4, 5, 6, 7, 9, 10, 11, 12],
                                    }
                                }
                            ]
                        },
                        {
                            extend: 'collection',
                            className: 'btn btn-outline-info dropdown-toggle me-2 waves-effect waves-light',
                            text: '<i class="ri-tools-line me-sm-1"></i> <span class="d-none d-sm-inline-block">Aksi</span>',
                            buttons: [
                                {
                                    text: '<i class="ri-user-add-line me-1"></i> Buat Akun Kolektif',
                                    className: 'dropdown-item',
                                    action: function () {
                                        let selectedIds = [];
                                        $('.mahasiswa-checkbox:checked').each(function () { selectedIds.push($(this).val()); });
                                        if (selectedIds.length === 0) { alert('Silakan pilih minimal satu mahasiswa.'); return; }
                                        if (confirm(`Buat akun massal untuk ${selectedIds.length} mahasiswa terpilih?`)) {
                                            let form = $('<form>', { 'method': 'POST', 'action': '{{ route("admin.mahasiswa.bulk-generate-users") }}' });
                                            form.append($('<input>', { 'type': 'hidden', 'name': '_token', 'value': '{{ csrf_token() }}' }));
                                            selectedIds.forEach(function (id) { form.append($('<input>', { 'type': 'hidden', 'name': 'mahasiswa_ids[]', 'value': id })); });
                                            $(document.body).append(form);
                                            form.submit();
                                        }
                                    }
                                },
                                {
                                    text: '<i class="ri-sun-line me-1"></i> Set Tipe: Pagi',
                                    className: 'dropdown-item',
                                    action: function () { processBulkTipeKelas('Pagi'); }
                                },
                                {
                                    text: '<i class="ri-moon-line me-1"></i> Set Tipe: Sore',
                                    className: 'dropdown-item',
                                    action: function () { processBulkTipeKelas('Sore'); }
                                },
                                {
                                    text: '<i class="ri-refresh-line me-1"></i> Inisialisasi Tipe Kelas',
                                    className: 'dropdown-item',
                                    action: function () { processInitTipeKelas(); }
                                }
                            ]
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
                    scrollX: true,
                    drawCallback: function (settings) {
                        // Dynamic row numbering sesuai dengan page saat ini
                        var api = this.api();
                        var startIndex = api.page.info().start;
                        api.column(3, { page: 'current' }).nodes().each(function (cell, i) {
                            cell.innerHTML = startIndex + i + 1;
                        });
                    }
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

                // AJAX Handle Toggle Tipe Kelas (Checked = Sore, Unchecked = Pagi)
                $('.datatables-basic').on('change', '.toggle-tipe-kelas', function () {
                    let id = $(this).data('id');
                    let isChecked = $(this).is(':checked');
                    let newTipe = isChecked ? 'Sore' : 'Pagi';
                    let $label = $('.tipe-label-' + id);
                    let $checkbox = $(this);

                    $checkbox.prop('disabled', true);

                    $.ajax({
                        url: '{{ route("admin.mahasiswa.toggle-tipe-kelas") }}',
                        type: 'POST',
                        data: {
                            _token: '{{ csrf_token() }}',
                            id: id,
                            tipe_kelas: newTipe
                        },
                        success: function (response) {
                            if (response.success) {
                                if (newTipe === 'Sore') {
                                    $label.html('<span class="badge bg-warning">Sore</span>');
                                } else {
                                    $label.html('<span class="badge bg-info">Pagi</span>');
                                }
                            } else {
                                alert('Gagal merubah status!');
                                $checkbox.prop('checked', !isChecked);
                            }
                        },
                        error: function () {
                            alert('Terjadi kesalahan server!');
                            $checkbox.prop('checked', !isChecked);
                        },
                        complete: function () {
                            $checkbox.prop('disabled', false);
                        }
                    });
                });
            }

            if ($('.select2-filter').length) {
                $('.select2-filter').select2({
                    dropdownParent: $('#modalFilter'),
                    width: '100%',
                    allowClear: true
                });
            }
        });

        // Function for Bulk Tipe Kelas Updates
        function processBulkTipeKelas(targetTipe) {
            let selectedIds = [];
            $('.mahasiswa-checkbox:checked').each(function () {
                selectedIds.push($(this).val());
            });

            if (selectedIds.length === 0) {
                alert('Silakan pilih minimal satu mahasiswa.');
                return;
            }

            if (confirm(`Apakah Anda yakin ingin mengubah tipe kelas menjadi ${targetTipe} untuk ${selectedIds.length} mahasiswa terpilih?`)) {
                let form = $('<form>', {
                    'method': 'POST',
                    'action': '{{ route("admin.mahasiswa.bulk-tipe-kelas") }}'
                });

                form.append($('<input>', {
                    'type': 'hidden',
                    'name': '_token',
                    'value': '{{ csrf_token() }}'
                }));

                form.append($('<input>', {
                    'type': 'hidden',
                    'name': 'tipe_kelas',
                    'value': targetTipe
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

        // Function for Mass Init Tipe Kelas from NIM
        function processInitTipeKelas() {
            if (!confirm('Proses ini akan mengisi tipe kelas (Pagi/Sore) untuk seluruh mahasiswa yang belum memiliki tipe kelas, berdasarkan digit ke-5 NIM.\n\nLanjutkan?')) {
                return;
            }

            $.ajax({
                url: '{{ route("admin.mahasiswa.init-tipe-kelas") }}',
                type: 'POST',
                data: { _token: '{{ csrf_token() }}' },
                beforeSend: function () {
                    // Disable button to prevent double-click
                    $('.btn-outline-warning').prop('disabled', true).html('<i class="ri-loader-4-line ri-16px me-sm-1 spin"></i> Memproses...');
                },
                success: function (response) {
                    if (response.success) {
                        alert(response.message);
                        location.reload();
                    } else {
                        alert('Gagal: ' + response.message);
                    }
                },
                error: function (xhr) {
                    let msg = 'Terjadi kesalahan server.';
                    if (xhr.responseJSON && xhr.responseJSON.message) {
                        msg = xhr.responseJSON.message;
                    }
                    alert(msg);
                },
                complete: function () {
                    $('.btn-outline-warning').prop('disabled', false).html('<i class="ri-refresh-line ri-16px me-sm-1"></i> Inisialisasi Tipe Kelas');
                }
            });
        }
    </script>
@endpush
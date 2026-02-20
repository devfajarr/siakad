@extends('layouts.app')

@section('title', 'Manajemen Data Mahasiswa')

@push('styles')
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
        <div class="card-header border-bottom d-flex justify-content-between align-items-center mb-3">
            <h5 class="card-title mb-0">Daftar Mahasiswa</h5>
            <div class="d-flex gap-2 align-items-center">
                 <form action="{{ route('admin.mahasiswa.index') }}" method="GET" class="d-flex gap-2">
                    <input type="text" name="q" class="form-control" placeholder="Search..." value="{{ request('q') }}">
                    <button type="submit" class="btn btn-primary"><i class="ri-search-line"></i></button>
                </form>
            </div>
        </div>
        
        <div class="px-4 pb-3">
            <div class="d-flex flex-wrap gap-2 justify-content-between">
                <div class="d-flex gap-2">
                    <button class="btn btn-outline-secondary"><i class="ri-filter-3-line me-1"></i> FILTER / SORT</button>
                    <a href="{{ route('admin.mahasiswa.create') }}" class="btn btn-primary"><i class="ri-add-line me-1"></i> TAMBAH</a>
                </div>
                <div class="text-muted d-flex align-items-center">
                    Halaman ini menampilkan data berdasarkan angkatan : <span class="badge bg-warning text-dark ms-1">2023</span>
                </div>
            </div>
        </div>

        <div class="table-responsive">
            <table class="table table-bordered table-hover text-nowrap">
                <thead class="table-light">
                    <tr>
                        <th width="100px">Action</th>
                        <th width="120px">Status</th>
                        <th width="50px">No</th>
                        <th>Nama</th>
                        <th>NIM</th>
                        <th>Program Studi</th>
                        <th>Jenis Kelamin</th>
                        <th>Agama</th>
                        <th>Total SKS Diambil</th>
                        <th>Tanggal Lahir</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($mahasiswa as $index => $item)
                        <tr>
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
                                </div>
                            </td>
                            <td>
                                @if ($item->is_synced)
                                    <span class="badge bg-success rounded-pill"> <i class="ri-check-line me-1"></i> sudah sync</span>
                                @else
                                    <span class="badge bg-warning rounded-pill"> <i class="ri-time-line me-1"></i> belum sync</span>
                                @endif
                            </td>
                            <td>{{ $mahasiswa->firstItem() + $index }}</td>
                            <td>
                                <span class="fw-bold text-primary">{{ $item->nama_mahasiswa }}</span>
                            </td>
                            <td>{{ $item->riwayatAktif->nim ?? '-' }}</td>
                            <td>{{ $item->riwayatAktif->prodi->nama_program_studi ?? '-' }}</td>
                            <td>{{ $item->jenis_kelamin == 'L' ? 'Laki - Laki' : 'Perempuan' }}</td>
                            <td>{{ $item->agama->nama_agama ?? '-' }}</td>
                            <td class="text-center">
                                {{-- 
                                    TODO:
                                    Kolom Total SKS akan diganti dengan hasil agregasi relasi KelasPerkuliahan
                                    setelah tabel & relasi siap.
                                --}}
                                <span class="fw-bold">0</span>
                            </td>
                            <td>{{ $item->tanggal_lahir ? $item->tanggal_lahir->format('d/m/Y') : '-' }}</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="10" class="text-center p-4">
                                <div class="text-muted">Data mahasiswa tidak ditemukan.</div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div class="card-footer d-flex justify-content-end py-3">
             {{ $mahasiswa->links() }}
        </div>
    </div>
@endsection

@push('scripts')
    <script src="{{ asset('assets/vendor/libs/datatables-bs5/datatables-bootstrap5.js') }}"></script>
    <script src="{{ asset('assets/vendor/libs/moment/moment.js') }}"></script>
    <script src="{{ asset('assets/vendor/libs/flatpickr/flatpickr.js') }}"></script>
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
                        targets: -1,
                        title: 'Actions',
                        orderable: false,
                        searchable: false,
                    }
                    ],
                    dom: '<"card-header flex-column flex-md-row border-bottom"<"head-label text-center"><"dt-action-buttons text-end pt-3 pt-md-0"B>><"row"<"col-sm-12 col-md-6 mt-5 mt-md-0"l><"col-sm-12 col-md-6 d-flex justify-content-center justify-content-md-end"f>>t<"row"<"col-sm-12 col-md-6"i><"col-sm-12 col-md-6"p>>',
                    displayLength: 7,
                    lengthMenu: [7, 10, 25, 50, 75, 100],
                    buttons: [
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
                                        columns: [0, 1, 2, 3, 4, 5],
                                    }
                                },
                                {
                                    extend: 'csv',
                                    text: '<i class="ri-file-text-line me-1" ></i>Csv',
                                    className: 'dropdown-item',
                                    exportOptions: {
                                        columns: [0, 1, 2, 3, 4, 5],
                                    }
                                },
                                {
                                    extend: 'excel',
                                    text: '<i class="ri-file-excel-line me-1"></i>Excel',
                                    className: 'dropdown-item',
                                    exportOptions: {
                                        columns: [0, 1, 2, 3, 4, 5],
                                    }
                                },
                                {
                                    extend: 'pdf',
                                    text: '<i class="ri-file-pdf-line me-1"></i>Pdf',
                                    className: 'dropdown-item',
                                    exportOptions: {
                                        columns: [0, 1, 2, 3, 4, 5],
                                    }
                                },
                                {
                                    extend: 'copy',
                                    text: '<i class="ri-file-copy-line me-1" ></i>Copy',
                                    className: 'dropdown-item',
                                    exportOptions: {
                                        columns: [0, 1, 2, 3, 4, 5],
                                    }
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
                    responsive: true
                });
                $('div.head-label').html('<h5 class="card-title mb-0">Data Mahasiswa</h5>');
            }
        });
    </script>
@endpush
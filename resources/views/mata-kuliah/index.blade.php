@extends('layouts.app')

@section('title', 'Manajemen Mata Kuliah')

@push('styles')
    <link rel="stylesheet" href="{{ asset('assets/vendor/libs/datatables-bs5/datatables.bootstrap5.css') }}" />
    <link rel="stylesheet" href="{{ asset('assets/vendor/libs/datatables-responsive-bs5/responsive.bootstrap5.css') }}" />
    <link rel="stylesheet" href="{{ asset('assets/vendor/libs/datatables-buttons-bs5/buttons.bootstrap5.css') }}" />
@endpush

@section('content')
    {{-- Page Header --}}
    <h4 class="fw-bold py-3 mb-2"><span class="text-muted fw-light">Perkuliahan /</span> Mata Kuliah</h4>

    <div class="card">
        <div class="card-header border-bottom d-flex flex-wrap justify-content-between align-items-center gap-3">
            <h5 class="card-title mb-0">Daftar Mata Kuliah</h5>
            <div class="d-flex gap-2 align-items-center flex-wrap">
                <a href="{{ route('admin.mata-kuliah.create') }}" class="btn btn-primary waves-effect waves-light">
                    <i class="ri-add-line me-1"></i> Tambah
                </a>
            </div>
        </div>

        <div class="card-datatable table-responsive">
            <table id="mataKuliahTable" class="table table-bordered table-hover text-nowrap">
                <thead class="table-light">
                    <tr>
                        <th>Action</th>
                        <th>Status</th>
                        <th>No</th>
                        <th>Kode MK</th>
                        <th>Nama Mata Kuliah</th>
                        <th>Bobot (sks)</th>
                        <th>Program Studi</th>
                        <th>Jenis Mata Kuliah</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($mataKuliah as $index => $item)
                        <tr>
                            <td>
                                <div class="d-flex gap-1">
                                    {{-- View Button (Available for all) --}}
                                    {{-- View Button (Available for all) --}}
                                    <a href="{{ route('admin.mata-kuliah.show', $item->id) }}"
                                        class="btn btn-icon btn-sm btn-info rounded-pill" title="Detail">
                                        <i class="ri-eye-line"></i>
                                    </a>

                                    {{-- Edit & Delete (Only for Lokal) --}}
                                    @if ($item->sumber_data == 'lokal')
                                        <a href="{{ route('admin.mata-kuliah.edit', $item->id) }}"
                                            class="btn btn-icon btn-sm btn-warning rounded-pill" title="Edit">
                                            <i class="ri-pencil-line"></i>
                                        </a>

                                        <form action="{{ route('admin.mata-kuliah.destroy', $item->id) }}" method="POST"
                                            class="d-inline">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="btn btn-icon btn-sm btn-danger rounded-pill" title="Hapus"
                                                onclick="return confirm('Apakah Anda yakin ingin menghapus data ini?')">
                                                <i class="ri-delete-bin-line"></i>
                                            </button>
                                        </form>
                                    @endif
                                </div>
                            </td>
                            <td>
                                @php
                                    $statusClass = 'bg-label-secondary';
                                    $statusText = 'Unknown';

                                    if ($item->is_deleted_server) {
                                        $statusClass = 'bg-label-danger';
                                        $statusText = 'Dihapus Server';
                                    } else {
                                        switch ($item->status_sinkronisasi) {
                                            case 'synced':
                                                $statusClass = 'bg-label-success';
                                                $statusText = 'Sudah Sync';
                                                break;
                                            case 'created_local':
                                                $statusClass = 'bg-label-info';
                                                $statusText = 'Lokal';
                                                break;
                                            case 'updated_local':
                                                $statusClass = 'bg-label-warning';
                                                $statusText = 'Update Lokal';
                                                break;
                                            case 'pending_push':
                                                $statusClass = 'bg-label-secondary';
                                                $statusText = 'Pending Push';
                                                break;
                                        }
                                    }
                                @endphp
                                <span class="badge {{ $statusClass }} rounded-pill">{{ $statusText }}</span>
                            </td>
                            <td>{{ $loop->iteration }}</td>
                            <td><span class="fw-semibold text-primary">{{ $item->kode_mk }}</span></td>
                            <td>{{ $item->nama_mk }}</td>
                            <td>{{ $item->sks }}</td>
                            <td>{{ $item->prodi ? $item->prodi->nama_program_studi : '-' }}</td>
                            <td>{{ $item->jenis_mk }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>

    {{-- Placeholder Modal --}}
    <div class="modal fade" id="mataKuliahModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-lg" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="mataKuliahModalLabel">Form Mata Kuliah</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <p>Modal content will be implemented later.</p>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Close</button>
                </div>
            </div>
        </div>
    </div>
@endsection

@push('scripts')
    <script src="{{ asset('assets/vendor/libs/datatables-bs5/datatables-bootstrap5.js') }}"></script>

    <script>
        $(function () {
            // DataTables init
            $('#mataKuliahTable').DataTable({
                responsive: false,
                scrollX: false,
                pageLength: 10,
                columnDefs: [{
                    targets: 0,
                    orderable: false,
                    searchable: false,
                }],
                language: {
                    search: '',
                    searchPlaceholder: 'Search...',
                    lengthMenu: '_MENU_',
                    info: 'Menampilkan _START_ - _END_ dari _TOTAL_ data',
                    infoEmpty: 'Tidak ada data',
                    emptyTable: 'Tidak ada data mata kuliah.',
                    paginate: {
                        first: '«',
                        last: '»',
                        next: '›',
                        previous: '‹'
                    }
                },
                dom: '<"row"<"col-sm-12 col-md-6"l><"col-sm-12 col-md-6 d-flex justify-content-center justify-content-md-end"f>>t<"row"<"col-sm-12 col-md-6"i><"col-sm-12 col-md-6"p>>',
            });

            // Handle Add Button
            $('#btnAddMataKuliah').on('click', function () {
                $('#mataKuliahModal').modal('show');
            });

            // Temp logic for demo
            $('.btn-edit, .btn-view').on('click', function () {
                console.log($(this).data('matkul'));
                // Only log for now as modal body isn't fully implemented
            });
        });
    </script>
@endpush
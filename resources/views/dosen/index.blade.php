@extends('layouts.app')

@section('title', 'Manajemen Data Dosen')

@push('styles')
    <link rel="stylesheet" href="{{ asset('assets/vendor/libs/datatables-bs5/datatables.bootstrap5.css') }}" />
    <link rel="stylesheet" href="{{ asset('assets/vendor/libs/datatables-responsive-bs5/responsive.bootstrap5.css') }}" />
    <link rel="stylesheet" href="{{ asset('assets/vendor/libs/datatables-buttons-bs5/buttons.bootstrap5.css') }}" />
@endpush

@section('content')
    {{-- Page Header --}}
    <h4 class="fw-bold py-3 mb-2"><span class="text-muted fw-light">Master Data /</span> Dosen</h4>

    <div class="card">
        <div class="card-header border-bottom d-flex flex-wrap justify-content-between align-items-center gap-3">
            <h5 class="card-title mb-0">Daftar Dosen</h5>
            <div class="d-flex gap-2 align-items-center flex-wrap">
                <form id="syncForm" action="{{ route('admin.dosen.sync') }}" method="POST" class="d-inline">
                    @csrf
                    <button type="button" id="btnSync" class="btn btn-outline-info waves-effect">
                        <i class="ri-refresh-line me-1"></i> Sync Dosen
                    </button>
                </form>
                <button type="button" class="btn btn-primary waves-effect waves-light" id="btnAddDosen">
                    <i class="ri-add-line me-1"></i> Add Dosen
                </button>
            </div>
        </div>

        <div class="card-datatable table-responsive">
            <table id="dosenTable" class="table table-bordered table-hover text-nowrap">
                <thead class="table-light">
                    <tr>
                        <th>Sumber Data</th>
                        <th>Action</th>
                        <th>Nama</th>
                        <th>NIDN</th>
                        <th>NIP</th>
                        <th>Jenis Kelamin</th>
                        <th>Agama</th>
                        <th>Status</th>
                        <th>Tanggal Lahir</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($dosen as $index => $item)
                        <tr>
                            <td>
                                @if ($item->status_sinkronisasi == 'pusat')
                                    <span class="badge bg-label-info rounded-pill">Pusat</span>
                                @else
                                    <span class="badge bg-label-success rounded-pill">Lokal</span>
                                @endif
                            </td>
                            <td>
                                <div class="d-flex gap-1">
                                    {{-- View Button (Available for all) --}}
                                    <button type="button" class="btn btn-icon btn-sm btn-info rounded-pill btn-view"
                                        title="Detail" data-dosen="{{ json_encode($item) }}">
                                        <i class="ri-eye-line"></i>
                                    </button>

                                    {{-- Edit & Delete (Only for Lokal) --}}
                                    @if ($item->status_sinkronisasi != 'pusat')
                                        <button type="button" class="btn btn-icon btn-sm btn-warning rounded-pill btn-edit"
                                            title="Edit" data-dosen="{{ json_encode($item) }}">
                                            <i class="ri-pencil-line"></i>
                                        </button>
                                        <form action="{{ route('admin.dosen.destroy', $item->id) }}" method="POST"
                                            class="d-inline form-delete">
                                            @csrf
                                            @method('DELETE')
                                            <button type="button" class="btn btn-icon btn-sm btn-danger rounded-pill btn-delete"
                                                title="Hapus">
                                                <i class="ri-delete-bin-line"></i>
                                            </button>
                                        </form>
                                    @endif
                                </div>
                            </td>
                            <td>
                                <span class="fw-semibold text-primary">{{ $item->nama }}</span>
                            </td>
                            <td>{{ $item->nidn ?? '-' }}</td>
                            <td>{{ $item->nip ?? '-' }}</td>
                            <td>
                                @if ($item->jenis_kelamin === 'L')
                                    Laki - Laki
                                @elseif($item->jenis_kelamin === 'P')
                                    Perempuan
                                @else
                                    -
                                @endif
                            </td>
                            <td>
                                @php
                                    $agamaMap = [
                                        1 => 'Islam',
                                        2 => 'Kristen',
                                        3 => 'Katolik',
                                        4 => 'Hindu',
                                        5 => 'Budha',
                                        6 => 'Konghucu',
                                        98 => 'Tidak diisi',
                                        99 => 'Lain-lain',
                                    ];
                                @endphp
                                {{ $agamaMap[$item->id_agama] ?? '-' }}
                            </td>
                            <td>
                                @if ($item->is_active)
                                    <span class="badge bg-label-success rounded-pill">Aktif</span>
                                @else
                                    <span class="badge bg-label-danger rounded-pill">Tidak Aktif</span>
                                @endif
                            </td>
                            <td>{{ $item->tanggal_lahir ? $item->tanggal_lahir->format('d/m/Y') : '-' }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>

    @include('dosen._modal')
    @include('dosen._view_modal')
@endsection

@push('scripts')
    <script src="{{ asset('assets/vendor/libs/datatables-bs5/datatables-bootstrap5.js') }}"></script>

    <script>
        $(function () {
            // DataTables init
            $('#dosenTable').DataTable({
                responsive: false,
                scrollX: false,
                columnDefs: [{
                    targets: 0,
                    orderable: false,
                    searchable: false,
                }],
                order: [
                    [2, 'asc']
                ],
                language: {
                    search: '',
                    searchPlaceholder: 'Search...',
                    lengthMenu: '_MENU_',
                    info: 'Menampilkan _START_ - _END_ dari _TOTAL_ data',
                    infoEmpty: 'Tidak ada data',
                    emptyTable: 'Tidak ada data dosen.',
                    paginate: {
                        first: '«',
                        last: '»',
                        next: '›',
                        previous: '‹'
                    }
                },
                dom: '<"row"<"col-sm-12 col-md-6"l><"col-sm-12 col-md-6 d-flex justify-content-center justify-content-md-end"f>>t<"row"<"col-sm-12 col-md-6"i><"col-sm-12 col-md-6"p>>',
            });

            // Sync button with SweetAlert confirmation
            $('#btnSync').on('click', function () {
                Swal.fire({
                    title: 'Sinkronisasi Dosen',
                    text: 'Apakah Anda yakin ingin menarik data dosen dari API Pusat? Proses ini mungkin memerlukan waktu.',
                    icon: 'question',
                    showCancelButton: true,
                    confirmButtonText: '<i class="ri-refresh-line me-1"></i> Ya, Sync Sekarang',
                    cancelButtonText: 'Batal',
                    customClass: {
                        confirmButton: 'btn btn-primary me-2',
                        cancelButton: 'btn btn-outline-secondary'
                    },
                    buttonsStyling: false,
                }).then((result) => {
                    if (result.isConfirmed) {
                        document.getElementById('syncForm').submit();
                    }
                });
            });

            // Handle Add Button
            $('#btnAddDosen').on('click', function () {
                $('#dosenModalLabel span').text('Tambah Dosen Baru');
                $('#formDosen').attr('action', "{{ route('admin.dosen.store') }}");
                $('#methodField').html(''); // Clear hidden field
                $('#formDosen')[0].reset();
                $('#dosenModal').modal('show');
            });

            // Handle Edit Button
            $('.btn-edit').on('click', function () {
                const data = $(this).data('dosen');
                const updateUrl = "{{ route('admin.dosen.update', ':id') }}".replace(':id', data.id);

                $('#dosenModalLabel span').text('Edit Dosen Lokal');
                $('#formDosen').attr('action', updateUrl);
                $('#methodField').html('<input type="hidden" name="_method" value="PUT">');

                // Populate Form
                $('#dosenNama').val(data.nama);
                $('#dosenNidn').val(data.nidn);
                $('#dosenNip').val(data.nip);
                $('#dosenEmail').val(data.email);
                $('#dosenJk').val(data.jenis_kelamin);
                $('#dosenAgama').val(data.id_agama);
                $('#dosenTglLahir').val(data.tanggal_lahir ? data.tanggal_lahir.split('T')[0] : '');
                $('#dosenTempatLahir').val(data.tempat_lahir);
                $('#dosenStatus').val(data.is_active ? 1 : 0);

                $('#dosenModal').modal('show');
            });

            // Handle View Button (for Pusat & Lokal)
            $('.btn-view').on('click', function () {
                const data = $(this).data('dosen');
                const modal = $('#viewDosenModal');

                // Populate View Modal Inputs (all are disabled)
                modal.find('#dosenNama').val(data.nama);
                modal.find('#dosenNidn').val(data.nidn);
                modal.find('#dosenNip').val(data.nip);
                modal.find('#dosenEmail').val(data.email);
                modal.find('#dosenJk').val(data.jenis_kelamin);
                modal.find('#dosenAgama').val(data.id_agama);
                modal.find('#dosenTglLahir').val(data.tanggal_lahir ? data.tanggal_lahir.split('T')[0] : '');
                modal.find('#dosenTempatLahir').val(data.tempat_lahir);
                modal.find('#dosenStatus').val(data.is_active ? 1 : 0);

                modal.modal('show');
            });

            // Delete Confirmation
            $('.btn-delete').on('click', function (e) {
                e.preventDefault();
                const form = $(this).closest('form');

                Swal.fire({
                    title: 'Hapus Dosen?',
                    text: "Data yang dihapus tidak dapat dikembalikan!",
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonText: 'Ya, Hapus!',
                    cancelButtonText: 'Batal',
                    customClass: {
                        confirmButton: 'btn btn-danger me-3',
                        cancelButton: 'btn btn-label-secondary'
                    },
                    buttonsStyling: false
                }).then(function (result) {
                    if (result.value) {
                        form.submit();
                    }
                });
            });
        });
    </script>
@endpush
@extends('layouts.app')

@section('title', 'Manajemen Data Pegawai')

@push('styles')
    <link rel="stylesheet" href="{{ asset('assets/vendor/libs/datatables-bs5/datatables.bootstrap5.css') }}" />
    <link rel="stylesheet" href="{{ asset('assets/vendor/libs/datatables-responsive-bs5/responsive.bootstrap5.css') }}" />
    <link rel="stylesheet" href="{{ asset('assets/vendor/libs/datatables-buttons-bs5/buttons.bootstrap5.css') }}" />
@endpush

@section('content')
    <h4 class="fw-bold py-3 mb-2"><span class="text-muted fw-light">Sivitas Akademika /</span> Pegawai</h4>

    <div class="card">
        <div class="card-header border-bottom d-flex flex-wrap justify-content-between align-items-center gap-3">
            <h5 class="card-title mb-0">Daftar Pegawai</h5>
            <button type="button" class="btn btn-primary waves-effect waves-light" id="btnAddPegawai">
                <i class="ri-add-line me-1"></i> Add Pegawai
            </button>
        </div>

        <div class="card-datatable table-responsive">
            <table id="pegawaiTable" class="table table-bordered table-hover text-nowrap">
                <thead class="table-light">
                    <tr>
                        <th style="width: 50px;">Aksi</th>
                        <th>Status Akun</th>
                        <th>NIP</th>
                        <th>Nama Lengkap</th>
                        <th>Unit Kerja</th>
                        <th>Jabatan</th>
                        <th>No HP / WA</th>
                        <th>Email</th>
                        <th>Status Kepegawaian</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($pegawais as $pegawai)
                        <tr>
                            <td>
                                <div class="d-flex gap-1">
                                    <button type="button" class="btn btn-icon btn-sm btn-secondary rounded-pill btn-edit"
                                        title="Edit" data-pegawai="{{ json_encode($pegawai) }}">
                                        <i class="ri-pencil-line"></i>
                                    </button>
                                    <form action="{{ route('admin.pegawai.destroy', $pegawai->id) }}" method="POST"
                                        class="d-inline form-delete">
                                        @csrf
                                        @method('DELETE')
                                        <button type="button" class="btn btn-icon btn-sm btn-danger rounded-pill btn-delete"
                                            title="Hapus">
                                            <i class="ri-delete-bin-line"></i>
                                        </button>
                                    </form>
                                </div>
                            </td>
                            <td>
                                @if($pegawai->user_id)
                                    <span class="badge bg-label-success"><i class="ri-check-line me-1"></i> Terhubung</span>
                                @else
                                    <span class="badge bg-label-danger"><i class="ri-close-line me-1"></i> Belum Ada</span>
                                @endif
                            </td>
                            <td><span class="fw-semibold">{{ $pegawai->nip }}</span></td>
                            <td><span class="fw-semibold text-primary">{{ $pegawai->nama_lengkap }}</span></td>
                            <td>{{ $pegawai->unit_kerja ?? '-' }}</td>
                            <td>{{ $pegawai->jabatan ?? '-' }}</td>
                            <td>{{ $pegawai->no_hp ?? '-' }}</td>
                            <td>{{ $pegawai->email ?? '-' }}</td>
                            <td>
                                @if ($pegawai->is_active)
                                    <span class="badge bg-label-success rounded-pill">Aktif</span>
                                @else
                                    <span class="badge bg-label-danger rounded-pill">Tidak Aktif</span>
                                @endif
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>

    @include('admin.pegawai._modal')
@endsection

@push('scripts')
    <script src="{{ asset('assets/vendor/libs/datatables-bs5/datatables-bootstrap5.js') }}"></script>

    <script>
        $(function () {
            // DataTables init
            $('#pegawaiTable').DataTable({
                responsive: false,
                scrollX: false,
                columnDefs: [{
                    targets: 0,
                    orderable: false,
                    searchable: false,
                }],
                order: [
                    [2, 'asc'] // Sort by NIP default
                ],
                language: {
                    search: '',
                    searchPlaceholder: 'Search...',
                    lengthMenu: '_MENU_',
                    info: 'Menampilkan _START_ - _END_ dari _TOTAL_ data',
                    infoEmpty: 'Tidak ada data',
                    emptyTable: 'Tidak ada data pegawai.',
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
            $('#btnAddPegawai').on('click', function () {
                $('#pegawaiModalLabel span').text('Tambah Pegawai Baru');
                $('#formPegawai').attr('action', "{{ route('admin.pegawai.store') }}");
                $('#methodField').html(''); // Clear hidden field
                $('#formPegawai')[0].reset();
                $('#pegawaiIsActive').val(1);
                $('#pegawaiModal').modal('show');
            });

            // Handle Edit Button
            $(document).on('click', '.btn-edit', function () {
                const data = $(this).data('pegawai');
                const updateUrl = "{{ route('admin.pegawai.update', ':id') }}".replace(':id', data.id);

                $('#pegawaiModalLabel span').text('Edit Data Pegawai');
                $('#formPegawai').attr('action', updateUrl);
                $('#methodField').html('<input type="hidden" name="_method" value="PUT">');

                // Populate Form
                $('#pegawaiNip').val(data.nip);
                $('#pegawaiNamaLengkap').val(data.nama_lengkap);
                $('#pegawaiUnitKerja').val(data.unit_kerja);
                $('#pegawaiJabatan').val(data.jabatan);
                $('#pegawaiNoHp').val(data.no_hp);
                $('#pegawaiEmail').val(data.email);
                $('#pegawaiIsActive').val(data.is_active ? 1 : 0);

                $('#pegawaiModal').modal('show');
            });

            // Delete Confirmation
            $(document).on('click', '.btn-delete', function (e) {
                e.preventDefault();
                const form = $(this).closest('form');
                const deleteUrl = form.attr('action');

                Swal.fire({
                    title: 'Hapus Data Pegawai?',
                    text: "Seluruh data terkait akan dihapus. Akun terkait (jika ada) juga akan melalui proses soft-delete.",
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
                    if (result.isConfirmed) {
                        $.ajax({
                            url: deleteUrl,
                            type: 'POST',
                            data: {
                                _method: 'DELETE',
                                _token: '{{ csrf_token() }}'
                            },
                            success: function (response) {
                                if (response.success) {
                                    Swal.fire('Terhapus!', response.message, 'success').then(() => {
                                        location.reload();
                                    });
                                } else {
                                    Swal.fire('Gagal!', response.message, 'error');
                                }
                            },
                            error: function () {
                                Swal.fire('Error!', 'Terjadi kesalahan server.', 'error');
                            }
                        });
                    }
                });
            });
        });
    </script>
@endpush
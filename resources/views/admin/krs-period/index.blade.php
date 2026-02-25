@extends('layouts.app')

@section('title', 'Manajemen Periode KRS')

@push('styles')
    <link rel="stylesheet" href="{{ asset('assets/vendor/libs/datatables-bs5/datatables.bootstrap5.css') }}" />
    <link rel="stylesheet" href="{{ asset('assets/vendor/libs/select2/select2.css') }}" />
@endpush

@section('content')
    <h4 class="fw-bold py-3 mb-2"><span class="text-muted fw-light">Pengaturan /</span> Periode KRS</h4>

    <div class="card">
        <div class="card-header border-bottom d-flex justify-content-between align-items-center">
            <h5 class="card-title mb-0">Daftar Periode KRS</h5>
            <button type="button" class="btn btn-primary" id="btnAddPeriod">
                <i class="ri-add-line me-1"></i> Tambah Periode
            </button>
        </div>
        <div class="card-datatable table-responsive">
            <table class="table table-hover table-bordered" id="tablePeriod">
                <thead>
                    <tr>
                        <th>Semester</th>
                        <th>Nama Periode</th>
                        <th>Mulai</th>
                        <th>Selesai</th>
                        <th>Status</th>
                        <th style="width: 100px;">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($periods as $p)
                        <tr>
                            <td>{{ $p->semester->nama_semester }}</td>
                            <td>{{ $p->nama_periode }}</td>
                            <td>{{ $p->tgl_mulai->format('d/m/Y H:i') }}</td>
                            <td>{{ $p->tgl_selesai->format('d/m/Y H:i') }}</td>
                            <td>
                                @if($p->is_active)
                                    <span class="badge bg-label-success">Aktif</span>
                                @else
                                    <span class="badge bg-label-danger">Non-Aktif</span>
                                @endif
                            </td>
                            <td>
                                <div class="d-flex gap-1">
                                    <button class="btn btn-sm btn-icon btn-secondary btn-edit"
                                        data-period="{{ json_encode($p) }}" title="Edit">
                                        <i class="ri-pencil-line"></i>
                                    </button>
                                    <form action="{{ route('admin.krs-period.destroy', $p->id) }}" method="POST"
                                        class="d-inline form-delete">
                                        @csrf
                                        @method('DELETE')
                                        <button type="button" class="btn btn-sm btn-icon btn-danger btn-delete" title="Hapus">
                                            <i class="ri-delete-bin-line"></i>
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

    {{-- Modal Create/Edit --}}
    <div class="modal fade" id="modalPeriod" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <form class="modal-content" id="formPeriod" method="POST">
                @csrf
                <div id="methodField"></div>
                <div class="modal-header">
                    <h5 class="modal-title" id="modalTitle">Tambah Periode KRS</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="row g-3">
                        <div class="col-12">
                            <label class="form-label">Semester</label>
                            <select name="id_semester" id="id_semester" class="form-select select2" required>
                                <option value="">-- Pilih Semester --</option>
                                @foreach($semesters as $s)
                                    <option value="{{ $s->id_semester }}">{{ $s->nama_semester }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-12">
                            <label class="form-label">Nama Periode</label>
                            <input type="text" name="nama_periode" id="nama_periode" class="form-control"
                                placeholder="Contoh: Pengisian KRS Semester Ganjil" required />
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Tanggal Mulai</label>
                            <input type="datetime-local" name="tgl_mulai" id="tgl_mulai" class="form-control" required />
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Tanggal Selesai</label>
                            <input type="datetime-local" name="tgl_selesai" id="tgl_selesai" class="form-control"
                                required />
                        </div>
                        <div class="col-12">
                            <div class="form-check form-switch mt-2">
                                <input class="form-check-input" type="checkbox" name="is_active" id="is_active" value="1"
                                    checked>
                                <label class="form-check-label" for="is_active">Aktifkan Periode Ini</label>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-primary">Simpan</button>
                </div>
            </form>
        </div>
    </div>
@endsection

@push('scripts')
    <script src="{{ asset('assets/vendor/libs/datatables-bs5/datatables-bootstrap5.js') }}"></script>
    <script src="{{ asset('assets/vendor/libs/select2/select2.js') }}"></script>
    <script>
        $(function () {
            // DataTable
            if ($.fn.DataTable) {
                $('#tablePeriod').DataTable({
                    order: [[2, 'desc']]
                });
            }

            // Select2
            if ($.fn.select2) {
                $('.select2').select2({
                    dropdownParent: $('#modalPeriod')
                });
            }

            // Add Click
            $('#btnAddPeriod').on('click', function () {
                $('#modalTitle').text('Tambah Periode KRS');
                $('#formPeriod').attr('action', "{{ route('admin.krs-period.store') }}");
                $('#methodField').html('');
                $('#formPeriod')[0].reset();
                $('#id_semester').val('').trigger('change');
                $('#is_active').prop('checked', true);
                $('#modalPeriod').modal('show');
            });

            // Edit Click
            $(document).on('click', '.btn-edit', function () {
                const data = $(this).data('period');
                $('#modalTitle').text('Edit Periode KRS');
                $('#formPeriod').attr('action', "{{ route('admin.krs-period.index') }}/" + data.id);
                $('#methodField').html('<input type="hidden" name="_method" value="PUT">');

                $('#id_semester').val(data.id_semester).trigger('change');
                $('#nama_periode').val(data.nama_periode);

                // Format datetime-local (ISO string slice)
                if (data.tgl_mulai) $('#tgl_mulai').val(data.tgl_mulai.substring(0, 16));
                if (data.tgl_selesai) $('#tgl_selesai').val(data.tgl_selesai.substring(0, 16));

                $('#is_active').prop('checked', data.is_active);

                $('#modalPeriod').modal('show');
            });

            // Delete Confirmation
            $(document).on('click', '.btn-delete', function (e) {
                e.preventDefault();
                const form = $(this).closest('form');
                Swal.fire({
                    title: 'Hapus Periode KRS?',
                    text: "Data yang dihapus tidak dapat dikembalikan.",
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
                        form.submit();
                    }
                });
            });
        });
    </script>
@endpush
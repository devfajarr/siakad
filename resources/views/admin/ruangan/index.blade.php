@extends('layouts.app')

@section('title', 'Master Ruangan')

@section('content')
    <h4 class="fw-bold py-3 mb-4">
        <span class="text-muted fw-light">Master Data /</span> Ruangan
    </h4>

    <div class="card">
        <div class="card-header d-flex justify-content-between align-items-center">
            <h5 class="mb-0">Daftar Ruangan Perkuliahan</h5>
            <button type="button" class="btn btn-primary" id="btnTambahRuang">
                <i class="ri-add-line me-1"></i> Tambah Ruangan
            </button>
        </div>

        <div class="card-body">
            @if (session('success'))
                <div class="alert alert-success alert-dismissible fade show" role="alert">
                    <i class="ri-check-line me-2"></i>{{ session('success') }}
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
            @endif

            @if (session('error'))
                <div class="alert alert-danger alert-dismissible fade show" role="alert">
                    <i class="ri-error-warning-line me-2"></i>{{ session('error') }}
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
            @endif

            @if ($errors->any())
                <div class="alert alert-danger alert-dismissible fade show" role="alert">
                    <ul class="mb-0">
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
            @endif

            <div class="table-responsive text-nowrap mt-2">
                <table class="table table-bordered table-striped table-hover align-middle" id="tableRuangan">
                    <thead class="table-light text-center">
                        <tr>
                            <th width="5%">No</th>
                            <th width="20%">Kode Ruang</th>
                            <th width="40%">Nama Ruang</th>
                            <th width="15%">Kapasitas</th>
                            <th width="20%">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($ruangs as $index => $ruang)
                            <tr class="text-center">
                                <td>{{ $index + 1 }}</td>
                                <td><span class="fw-bold text-primary">{{ $ruang->kode_ruang }}</span></td>
                                <td class="text-start">{{ $ruang->nama_ruang }}</td>
                                <td>{{ $ruang->kapasitas }} Kursi</td>
                                <td>
                                    <div class="d-flex justify-content-center gap-1">
                                        <button type="button" class="btn btn-icon btn-sm btn-outline-warning btn-edit"
                                            data-id="{{ $ruang->id }}" data-kode="{{ $ruang->kode_ruang }}"
                                            data-nama="{{ $ruang->nama_ruang }}" data-kapasitas="{{ $ruang->kapasitas }}"
                                            title="Edit Ruangan">
                                            <i class="ri-edit-2-line"></i>
                                        </button>

                                        <form action="{{ route('admin.ruangan.destroy', $ruang->id) }}" method="POST"
                                            class="d-inline">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="btn btn-icon btn-sm btn-outline-danger"
                                                onclick="return confirm('Yakin ingin menghapus ruangan {{ $ruang->kode_ruang }}?');"
                                                title="Hapus Ruangan">
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
    </div>

    {{-- Modal Reuse Create & Edit --}}
    <div class="modal fade" id="modalRuang" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="modalRuangTitle">Tambah Ruangan Baru</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <form action="{{ route('admin.ruangan.store') }}" method="POST" id="formRuang">
                    @csrf
                    <input type="hidden" name="_method" id="formMethod" value="POST">

                    <div class="modal-body">
                        <div class="row g-3">
                            <div class="col-md-12">
                                <label for="kode_ruang" class="form-label">Kode Ruang <span
                                        class="text-danger">*</span></label>
                                <input type="text" id="kode_ruang" name="kode_ruang" class="form-control"
                                    placeholder="Contoh: R.101" required>
                            </div>
                            <div class="col-md-12">
                                <label for="nama_ruang" class="form-label">Nama Ruangan <span
                                        class="text-danger">*</span></label>
                                <input type="text" id="nama_ruang" name="nama_ruang" class="form-control"
                                    placeholder="Contoh: Lab Komputer Terpadu" required>
                            </div>
                            <div class="col-md-12">
                                <label for="kapasitas" class="form-label">Kapasitas Kursi <span
                                        class="text-danger">*</span></label>
                                <div class="input-group">
                                    <input type="number" id="kapasitas" name="kapasitas" class="form-control" value="40"
                                        min="0" required>
                                    <span class="input-group-text">Kursi</span>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Batal</button>
                        <button type="submit" class="btn btn-primary" id="btnSimpan"><i class="ri-save-line me-1"></i>
                            Simpan Data</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
@endsection

@push('styles')
    <link rel="stylesheet" href="{{ asset('assets/vendor/libs/datatables-bs5/datatables.bootstrap5.css') }}" />
    <link rel="stylesheet" href="{{ asset('assets/vendor/libs/datatables-responsive-bs5/responsive.bootstrap5.css') }}" />
@endpush

@push('scripts')
    <script src="{{ asset('assets/vendor/libs/datatables-bs5/datatables-bootstrap5.js') }}"></script>
    <script>
        $(document).ready(function () {
            // Init DataTable
            $('#tableRuangan').DataTable({
                responsive: true,
                language: {
                    search: "Cari:",
                    lengthMenu: "Tampilkan _MENU_ entri",
                    info: "Menampilkan _START_ sampai _END_ dari _TOTAL_ entri",
                    paginate: {
                        first: "Pertama",
                        last: "Terakhir",
                        next: "Lanjut",
                        previous: "Kembali"
                    }
                }
            });

            // Handle Tambah Button
            $('#btnTambahRuang').click(function () {
                $('#formRuang').trigger("reset");
                $('#formMethod').val('POST');
                $('#formRuang').attr('action', '{{ route('admin.ruangan.store') }}');
                $('#modalRuangTitle').text('Tambah Ruangan Baru');
                $('#btnSimpan').html('<i class="ri-save-line me-1"></i> Simpan Data');

                var modal = new bootstrap.Modal(document.getElementById('modalRuang'));
                modal.show();
            });

            // Handle Edit Button via jQuery delegation
            $('#tableRuangan').on('click', '.btn-edit', function () {
                var btn = $(this);

                $('#kode_ruang').val(btn.data('kode'));
                $('#nama_ruang').val(btn.data('nama'));
                $('#kapasitas').val(btn.data('kapasitas'));

                // Change Form Method to PUT for update
                $('#formMethod').val('PUT');

                // Replace Form Action URL
                var updateUrl = '{{ url("admin/ruangan") }}/' + btn.data('id');
                $('#formRuang').attr('action', updateUrl);

                $('#modalRuangTitle').text('Edit Data Ruangan');
                $('#btnSimpan').html('<i class="ri-save-line me-1"></i> Perbarui Data');

                var modal = new bootstrap.Modal(document.getElementById('modalRuang'));
                modal.show();
            });

            // Auto show modal if there are validation errors (from old input)
            @if ($errors->any())
                // Assuming it's a create failure by default, if we need strict old-state recovery, can be enhanced.
                var myModal = new bootstrap.Modal(document.getElementById('modalRuang'));
                myModal.show();
            @endif
            });
    </script>
@endpush
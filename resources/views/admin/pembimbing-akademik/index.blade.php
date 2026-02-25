@extends('layouts.app')

@push('styles')
    <link rel="stylesheet" href="{{ asset('assets/vendor/libs/datatables-bs5/datatables.bootstrap5.css') }}">
    <link rel="stylesheet" href="{{ asset('assets/vendor/libs/select2/select2.css') }}">
@endpush

@section('content')
    <div class="container-xxl flex-grow-1 container-p-y">
        <div class="row mb-4">
            <div class="col-md-4">
                <div class="card">
                    <div class="card-body">
                        <div class="d-flex align-items-center mb-2">
                            <div class="avatar me-2">
                                <span class="avatar-initial rounded bg-label-primary"><i class="ri-git-merge-line"></i></span>
                            </div>
                            <h4 class="ms-1 mb-0">{{ $statistics['total_mappings'] }}</h4>
                        </div>
                        <p class="mb-0 text-muted">Total Mapping PA</p>
                    </div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="card">
                    <div class="card-body">
                        <div class="d-flex align-items-center mb-2">
                            <div class="avatar me-2">
                                <span class="avatar-initial rounded bg-label-success"><i class="ri-building-line"></i></span>
                            </div>
                            <h4 class="ms-1 mb-0">{{ $statistics['total_prodi_mapped'] }}</h4>
                        </div>
                        <p class="mb-0 text-muted">Prodi Ter-Mapping</p>
                    </div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="card">
                    <div class="card-body">
                        <div class="d-flex align-items-center mb-2">
                            <div class="avatar me-2">
                                <span class="avatar-initial rounded bg-label-info"><i class="ri-user-star-line"></i></span>
                            </div>
                            <h4 class="ms-1 mb-0">{{ $statistics['total_dosen_involved'] }}</h4>
                        </div>
                        <p class="mb-0 text-muted">Dosen PA Terlibat</p>
                    </div>
                </div>
            </div>
        </div>

        <div class="card mb-4">
            <div class="card-body">
                <form action="{{ route('admin.pembimbing-akademik.index') }}" method="GET" class="row g-3">
                    <div class="col-md-5">
                        <label class="form-label">Filter Semester</label>
                        <select name="id_semester" class="form-select select2" onchange="this.form.submit()">
                            @foreach($semesters as $s)
                                <option value="{{ $s->id_semester }}" {{ $selectedSemesterId == $s->id_semester ? 'selected' : '' }}>
                                    {{ $s->nama_semester }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-5">
                        <label class="form-label">Filter Program Studi</label>
                        <select name="id_prodi" class="form-select select2" onchange="this.form.submit()">
                            <option value="">-- Semua Prodi --</option>
                            @foreach($prodis as $p)
                                <option value="{{ $p->id_prodi }}" {{ request('id_prodi') == $p->id_prodi ? 'selected' : '' }}>
                                    {{ $p->nama_program_studi }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-2">
                        <label class="form-label">&nbsp;</label>
                        <button type="button" class="btn btn-outline-primary btn-sm d-block w-100" data-bs-toggle="modal" data-bs-target="#modalCopySemester">
                            <i class="ri-file-copy-line me-1"></i> Copy Semester
                        </button>
                    </div>
                </form>
            </div>
        </div>

        <div class="card">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h5 class="mb-0">Daftar Penugasan Dosen PA</h5>
                <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#modalAddMapping">
                    <i class="ri-add-line me-1"></i> Tambah Penugasan PA
                </button>
            </div>
            <div class="card-body">
                <div class="table-responsive text-nowrap">
                    <table class="table table-hover" id="tableMapping">
                        <thead>
                            <tr>
                                <th>Program Studi</th>
                                <th>Dosen PA</th>
                                <th>NIDN</th>
                                <th>Semester</th>
                                <th>Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($mappings as $m)
                            <tr>
                                <td>{{ $m->prodi?->nama_program_studi ?? '-' }}</td>
                                <td>{{ $m->dosen?->nama_admin_display ?? '-' }}</td>
                                <td>{{ $m->dosen?->nidn ?? '-' }}</td>
                                <td><span class="badge bg-label-info">{{ $m->semester?->nama_semester }}</span></td>
                                <td>
                                    <form action="{{ route('admin.pembimbing-akademik.destroy', $m->id) }}" method="POST" class="d-inline">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="btn btn-sm btn-outline-danger" onclick="return confirm('Hapus penugasan ini?')">
                                            <i class="ri-delete-bin-line"></i>
                                        </button>
                                    </form>
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <!-- Modal Add Mapping -->
    <div class="modal fade" id="modalAddMapping" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Tambah Dosen PA Baru</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <form id="formAddMapping">
                    @csrf
                    <div class="modal-body">
                        <div class="mb-3">
                            <label class="form-label">Semester</label>
                            <select name="id_semester" class="form-select" required>
                                @foreach($semesters as $s)
                                    <option value="{{ $s->id_semester }}" {{ $selectedSemesterId == $s->id_semester ? 'selected' : '' }}>
                                        {{ $s->nama_semester }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Program Studi</label>
                            <select name="id_prodi" id="select_prodi_add" class="form-select select2" required>
                                <option value="">-- Pilih Prodi --</option>
                                @foreach($prodis as $p)
                                    <option value="{{ $p->id_prodi }}">{{ $p->nama_program_studi }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Pilih Dosen PA</label>
                            <select name="id_dosen" id="select_dosen_add" class="form-select select2" required disabled>
                                <option value="">-- Pilih Prodi Dahulu --</option>
                            </select>
                            <small class="text-muted">Pilih dosen yang akan ditugaskan sebagai Pembimbing Akademik.</small>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Batal</button>
                        <button type="submit" class="btn btn-primary">Simpan Penugasan</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Modal Copy Semester -->
    <div class="modal fade" id="modalCopySemester" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Salin Data PA antar Semester</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <form action="{{ route('admin.pembimbing-akademik.copy-semester') }}" method="POST">
                    @csrf
                    <div class="modal-body">
                        <div class="alert alert-info small">
                            Gunakan fitur ini untuk menduplikasi seluruh penugasan Dosen PA dari semester sebelumnya ke semester baru.
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Dari Semester (Sumber)</label>
                            <select name="from_semester" class="form-select" required>
                                <option value="">-- Pilih Semester --</option>
                                @foreach($semesters as $s)
                                    <option value="{{ $s->id_semester }}">{{ $s->nama_semester }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Ke Semester (Tujuan)</label>
                            <select name="to_semester" class="form-select" required>
                                <option value="">-- Pilih Semester --</option>
                                @foreach($semesters as $s)
                                    <option value="{{ $s->id_semester }}" {{ $selectedSemesterId == $s->id_semester ? 'selected' : '' }}>
                                        {{ $s->nama_semester }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Tutup</button>
                        <button type="submit" class="btn btn-primary">Salin Sekarang</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

@endsection

@push('scripts')
    <script src="{{ asset('assets/vendor/libs/datatables-bs5/datatables-bootstrap5.js') }}"></script>
    <script src="{{ asset('assets/vendor/libs/select2/select2.js') }}"></script>
    <script>
        $(function () {
            // Initialize DataTables
            if ($.fn.DataTable) {
                $('#tableMapping').DataTable({
                    processing: true,
                    serverSide: false
                });
            }

            // Initialize Select2
            if ($.fn.select2) {
                $('.select2').each(function() {
                    $(this).select2({
                        dropdownParent: $(this).closest('.modal').length ? $(this).closest('.modal') : null
                    });
                });
            }

            // Smart Filter: Fetch Dosen when Prodi is selected
            $('#select_prodi_add').on('change', function () {
                var prodiId = $(this).val();
                if (prodiId) {
                    $('#select_dosen_add').prop('disabled', true).html('<option value="">Memuat...</option>');
                    $.get("{{ url('admin/pembimbing-akademik/dosen-by-prodi') }}/" + prodiId, function (data) {
                        var options = '<option value="">-- Pilih Dosen --</option>';
                        data.forEach(function (dosen) {
                            var displayName = dosen.nama_alias ? `${dosen.nama} (${dosen.nama_alias})` : dosen.nama;
                            options += `<option value="${dosen.id}">${displayName} (NIDN: ${dosen.nidn})</option>`;
                        });
                        $('#select_dosen_add').prop('disabled', false).html(options);
                        
                        // Re-initialize Select2 for the dynamic content if needed
                        if ($.fn.select2) {
                            $('#select_dosen_add').select2({
                                dropdownParent: $('#select_dosen_add').closest('.modal')
                            });
                        }
                    });
                } else {
                    $('#select_dosen_add').prop('disabled', true).html('<option value="">-- Pilih Prodi Dahulu --</option>');
                    if ($.fn.select2) $('#select_dosen_add').select2();
                }
            });

            $('#formAddMapping').on('submit', function (e) {
                e.preventDefault();
                $.post("{{ route('admin.pembimbing-akademik.store') }}", $(this).serialize(), function (res) {
                    $('#modalAddMapping').modal('hide');
                    Swal.fire({
                        icon: 'success',
                        title: 'Berhasil',
                        text: res.message,
                        showConfirmButton: true,
                        confirmButtonText: 'OK',
                        customClass: {
                            confirmButton: 'btn btn-primary'
                        },
                        buttonsStyling: false
                    }).then(() => {
                        location.reload();
                    });
                }).fail(function (err) {
                    Swal.fire({
                        icon: 'error',
                        title: 'Gagal',
                        text: err.responseJSON.message,
                        showConfirmButton: true,
                        confirmButtonText: 'OK',
                        customClass: {
                            confirmButton: 'btn btn-primary'
                        },
                        buttonsStyling: false
                    });
                });
            });
        });
    </script>
@endpush
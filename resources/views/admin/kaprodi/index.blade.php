@extends('layouts.app')

@push('styles')
    <link rel="stylesheet" href="{{ asset('assets/vendor/libs/datatables-bs5/datatables.bootstrap5.css') }}">
    <link rel="stylesheet" href="{{ asset('assets/vendor/libs/datatables-responsive-bs5/responsive.bootstrap5.css') }}">
@endpush

@section('content')
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h5 class="mb-0">Manajemen Kaprodi Aktif</h5>
                    <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addKaprodiModal">
                        <i class="ri-add-line me-1"></i> Tambah Kaprodi Baru
                    </button>
                </div>

                <div class="card-body">
                    <div class="table-responsive text-nowrap">
                        <table class="table table-hover" id="tableKaprodi">
                            <thead>
                                <tr>
                                    <th>No</th>
                                    <th>Program Studi</th>
                                    <th>Nama Kaprodi</th>
                                    <th>NIDN / NIP</th>
                                    <th class="text-center">Aksi</th>
                                </tr>
                            </thead>
                            <tbody class="table-border-bottom-0">
                                @foreach ($kaprodis as $index => $kaprodi)
                                    <tr>
                                        <td>{{ $index + 1 }}</td>
                                        <td>
                                            <strong>{{ $kaprodi->prodi->nama_program_studi }}</strong>
                                        </td>
                                        <td>
                                            {{ $kaprodi->dosen->nama_admin_display }}
                                        </td>
                                        <td>
                                            {{ $kaprodi->dosen->nidn ?? $kaprodi->dosen->nip ?? '-' }}
                                        </td>
                                        <td class="text-center">
                                            <div class="d-flex justify-content-center gap-1">
                                                <button class="btn btn-sm btn-icon btn-text-secondary rounded-pill waves-effect"
                                                    data-bs-toggle="modal" data-bs-target="#editKaprodiModal{{ $kaprodi->id }}"
                                                    title="Ubah Dosen">
                                                    <i class="ri-edit-2-line text-primary"></i>
                                                </button>
                                                <form action="{{ route('admin.kaprodi.destroy', $kaprodi->id) }}" method="POST"
                                                    onsubmit="return confirm('Apakah Anda yakin ingin mencabut jabatan Kaprodi ini? Access role akan otomatis dicabut jika dosen tidak memiliki jabatan lain.')">
                                                    @csrf
                                                    @method('DELETE')
                                                    <button type="submit"
                                                        class="btn btn-sm btn-icon btn-text-secondary rounded-pill waves-effect"
                                                        title="Hapus">
                                                        <i class="ri-delete-bin-line text-danger"></i>
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
        </div>
    </div>

    <!-- Modal Tambah -->
    <div class="modal fade" id="addKaprodiModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <form action="{{ route('admin.kaprodi.store') }}" method="POST">
                    @csrf
                    <div class="modal-header">
                        <h5 class="modal-title">Tambah Kaprodi Baru</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        <div class="mb-3">
                            <label class="form-label">Program Studi</label>
                            <select name="id_prodi" class="form-select @error('id_prodi') is-invalid @enderror" required>
                                <option value="">-- Pilih Program Studi --</option>
                                @foreach($availableProdis as $prodi)
                                    <option value="{{ $prodi->id_prodi }}">{{ $prodi->nama_program_studi }}</option>
                                @endforeach
                            </select>
                            @error('id_prodi') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Pilih Dosen Kaprodi</label>
                            <select name="dosen_id" id="select2-dosen-add" class="form-select" required></select>
                            <small class="text-muted">Cari dosen berdasarkan Nama, NIDN, atau NIP.</small>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Batal</button>
                        <button type="submit" class="btn btn-primary">Simpan</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Modal Edit -->
    @foreach ($kaprodis as $kaprodi)
        <div class="modal fade" id="editKaprodiModal{{ $kaprodi->id }}" tabindex="-1" aria-hidden="true">
            <div class="modal-dialog modal-dialog-centered">
                <div class="modal-content">
                    <form action="{{ route('admin.kaprodi.update', $kaprodi->id) }}" method="POST">
                        @csrf
                        @method('PUT')
                        <div class="modal-header">
                            <h5 class="modal-title">Ganti Kaprodi: {{ $kaprodi->prodi->nama_program_studi }}</h5>
                            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                        </div>
                        <div class="modal-body">
                            <div class="mb-3">
                                <label class="form-label">Pilih Dosen Baru</label>
                                <select name="dosen_id" id="select2-dosen-edit-{{ $kaprodi->id }}" class="form-select" required>
                                    <option value="{{ $kaprodi->dosen_id }}" selected>{{ $kaprodi->dosen->nama_admin_display }}
                                    </option>
                                </select>
                                <small class="text-muted">Ganti dosen yang menjabat sebagai Kaprodi di Program Studi
                                    ini.</small>
                            </div>
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Batal</button>
                            <button type="submit" class="btn btn-primary">Simpan</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    @endforeach

@endsection

@push('scripts')
    <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
    <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>

    <script src="{{ asset('assets/vendor/libs/datatables-bs5/datatables-bootstrap5.js') }}"></script>

    <script>
        $(document).ready(function () {
            // Initialize DataTables
            if ($.fn.DataTable) {
                $('#tableKaprodi').DataTable({
                    processing: true,
                    serverSide: false,
                    language: {
                        search: "",
                        searchPlaceholder: "Cari data..."
                    }
                });
            }

            function initSelect2(selector) {
                const $target = $(selector);
                if (!$target.length) return;

                $target.select2({
                    dropdownParent: $target.closest('.modal'),
                    placeholder: 'Ketik nama atau NIDN dosen/nip...',
                    allowClear: true,
                    minimumInputLength: 2,
                    ajax: {
                        url: "{{ route('admin.kaprodi.search-dosen') }}",
                        dataType: 'json',
                        delay: 300,
                        processResults: function (data) {
                            return { results: data };
                        },
                        cache: true
                    }
                });
            }

            // Inisialisasi saat modal ditampilkan agar width terhitung dengan benar
            $('.modal').on('shown.bs.modal', function () {
                const modalId = $(this).attr('id');
                if (modalId === 'addKaprodiModal') {
                    initSelect2('#select2-dosen-add');
                } else if (modalId.startsWith('editKaprodiModal')) {
                    const id = modalId.replace('editKaprodiModal', '');
                    initSelect2('#select2-dosen-edit-' + id);
                }
            });
        });
    </script>
    <style>
        /* Select2 Premium Styling */
        .select2-container--default .select2-selection--single {
            height: 38px !important;
            border: 1px solid #d9dee3 !important;
            border-radius: 0.375rem !important;
            transition: border-color 0.15s ease-in-out, box-shadow 0.15s ease-in-out;
        }

        .select2-container--default.select2-container--focus .select2-selection--single {
            border-color: #696cff !important;
            box-shadow: 0 0 0.25rem 0.05rem rgba(105, 108, 255, 0.1) !important;
        }

        .select2-container--default .select2-selection--single .select2-selection__rendered {
            line-height: 38px !important;
            padding-left: 12px !important;
            color: #566a7f !important;
        }

        .select2-container--default .select2-selection--single .select2-selection__arrow {
            height: 36px !important;
            right: 8px !important;
        }

        .select2-dropdown {
            border: 1px solid #d9dee3 !important;
            border-radius: 0.375rem !important;
            box-shadow: 0 0.25rem 1rem rgba(161, 172, 184, 0.45) !important;
            z-index: 2000 !important;
            /* Ensure it stays above modal */
        }

        .select2-results__option--highlighted[aria-selected] {
            background-color: #696cff !important;
        }

        /* Modal Body Spacing */
        .modal-body .form-label {
            font-size: 0.75rem;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            color: #a1acb8;
        }
    </style>
@endpush
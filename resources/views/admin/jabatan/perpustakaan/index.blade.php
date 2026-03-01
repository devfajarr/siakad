@extends('layouts.app')
@section('title', 'Manajemen Perpustakaan')

@push('styles')
    <link rel="stylesheet" href="{{ asset('assets/vendor/libs/datatables-bs5/datatables.bootstrap5.css') }}">
    <link rel="stylesheet" href="{{ asset('assets/vendor/libs/select2/select2.css') }}">
@endpush

@section('content')
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h5 class="mb-0">Manajemen Pejabat Perpustakaan</h5>
                    <button type="button" class="btn btn-primary" data-bs-toggle="modal"
                        data-bs-target="#addPerpustakaanModal">
                        <i class="ri-add-line me-1"></i> Tambah Pejabat Perpustakaan
                    </button>
                </div>
                <div class="card-body">
                    <div class="table-responsive text-nowrap">
                        <table class="table table-hover" id="tablePerpustakaan">
                            <thead>
                                <tr>
                                    <th>No</th>
                                    <th>Nama Lengkap</th>
                                    <th>Status Tipe</th>
                                    <th>Status Aktif</th>
                                    <th class="text-center">Aksi</th>
                                </tr>
                            </thead>
                            <tbody class="table-border-bottom-0">
                                @foreach ($perpustakaans as $index => $item)
                                    <tr>
                                        <td>{{ $index + 1 }}</td>
                                        <td>
                                            @if($item->id_dosen)
                                                {{ $item->dosen->nama_admin_display ?? $item->dosen->nama_lengkap }}
                                            @else
                                                {{ $item->pegawai->nama_lengkap }}
                                            @endif
                                        </td>
                                        <td>
                                            @if($item->id_dosen)
                                                <span class="badge bg-label-info">Dosen</span>
                                            @else
                                                <span class="badge bg-label-warning">Pegawai</span>
                                            @endif
                                        </td>
                                        <td>
                                            @if($item->is_active)
                                                <span class="badge bg-success">Aktif</span>
                                            @else
                                                <span class="badge bg-danger">Tidak Aktif</span>
                                            @endif
                                        </td>
                                        <td class="text-center">
                                            <div class="d-flex justify-content-center gap-1">
                                                <button class="btn btn-sm btn-icon btn-text-secondary rounded-pill waves-effect"
                                                    data-bs-toggle="modal"
                                                    data-bs-target="#editPerpustakaanModal{{ $item->id }}" title="Ubah Status">
                                                    <i class="ri-edit-2-line text-primary"></i>
                                                </button>
                                                <form action="{{ route('admin.perpustakaan.destroy', $item->id) }}"
                                                    method="POST"
                                                    onsubmit="return confirm('Apakah Anda yakin ingin mencabut jabatan ini? Access role akan otomatis dicabut.')">
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

                                    <!-- Modal Edit -->
                                    <div class="modal fade" id="editPerpustakaanModal{{ $item->id }}" tabindex="-1"
                                        aria-hidden="true">
                                        <div class="modal-dialog modal-dialog-centered">
                                            <div class="modal-content">
                                                <form action="{{ route('admin.perpustakaan.update', $item->id) }}"
                                                    method="POST">
                                                    @csrf
                                                    @method('PUT')
                                                    <div class="modal-header">
                                                        <h5 class="modal-title">Ubah Status Jabatan</h5>
                                                        <button type="button" class="btn-close" data-bs-dismiss="modal"
                                                            aria-label="Close"></button>
                                                    </div>
                                                    <div class="modal-body">
                                                        <div class="mb-3">
                                                            <label class="form-label">Status Aktif</label>
                                                            <select name="is_active" class="form-select">
                                                                <option value="1" {{ $item->is_active ? 'selected' : '' }}>Aktif
                                                                </option>
                                                                <option value="0" {{ !$item->is_active ? 'selected' : '' }}>
                                                                    Tidak Aktif</option>
                                                            </select>
                                                        </div>
                                                    </div>
                                                    <div class="modal-footer">
                                                        <button type="button" class="btn btn-outline-secondary"
                                                            data-bs-dismiss="modal">Batal</button>
                                                        <button type="submit" class="btn btn-primary">Simpan</button>
                                                    </div>
                                                </form>
                                            </div>
                                        </div>
                                    </div>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Modal Tambah -->
    <div class="modal fade" id="addPerpustakaanModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered modal-lg">
            <div class="modal-content">
                <form action="{{ route('admin.perpustakaan.store') }}" method="POST">
                    @csrf
                    <div class="modal-header">
                        <h5 class="modal-title">Tambah Pejabat Perpustakaan</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        <div class="mb-3">
                            <label class="form-label d-block">Tipe User Jabatan</label>
                            <div class="form-check form-check-inline mt-2">
                                <input class="form-check-input" type="radio" name="tipe_user" id="tipe_dosen_perpus"
                                    value="dosen" checked onchange="toggleUserSelectPerpus('dosen')">
                                <label class="form-check-label" for="tipe_dosen_perpus">Dosen</label>
                            </div>
                            <div class="form-check form-check-inline">
                                <input class="form-check-input" type="radio" name="tipe_user" id="tipe_pegawai_perpus"
                                    value="pegawai" onchange="toggleUserSelectPerpus('pegawai')">
                                <label class="form-check-label" for="tipe_pegawai_perpus">Pegawai</label>
                            </div>
                        </div>

                        <div class="mb-3" id="select_dosen_wrapper_perpus">
                            <label class="form-label">Pilih Dosen</label>
                            <select name="id_dosen" class="form-select select2-perpus" style="width: 100%;">
                                <option value="">-- Cari Dosen --</option>
                                @foreach($dosens as $d)
                                    <option value="{{ $d->id }}">{{ $d->nama_lengkap }} - {{ $d->nidn ?? $d->nip }}</option>
                                @endforeach
                            </select>
                        </div>

                        <div class="mb-3 d-none" id="select_pegawai_wrapper_perpus">
                            <label class="form-label">Pilih Pegawai</label>
                            <select name="id_pegawai" class="form-select select2-perpus" style="width: 100%;">
                                <option value="">-- Cari Pegawai --</option>
                                @foreach($pegawais as $p)
                                    <option value="{{ $p->id }}">{{ $p->nama_lengkap }} - {{ $p->nip ?? $p->nik }}</option>
                                @endforeach
                            </select>
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
@endsection

@push('scripts')
    <script src="{{ asset('assets/vendor/libs/datatables-bs5/datatables-bootstrap5.js') }}"></script>
    <script src="{{ asset('assets/vendor/libs/select2/select2.js') }}"></script>
    <script>
        function toggleUserSelectPerpus(type) {
            if (type === 'dosen') {
                $('#select_dosen_wrapper_perpus').removeClass('d-none');
                $('#select_pegawai_wrapper_perpus').addClass('d-none');
                $('select[name="id_pegawai"]').val('').trigger('change');
            } else {
                $('#select_dosen_wrapper_perpus').addClass('d-none');
                $('#select_pegawai_wrapper_perpus').removeClass('d-none');
                $('select[name="id_dosen"]').val('').trigger('change');
            }
        }

        $(document).ready(function () {
            $('#tablePerpustakaan').DataTable();
            $('.select2-perpus').select2({
                dropdownParent: $('#addPerpustakaanModal')
            });
        });
    </script>
@endpush
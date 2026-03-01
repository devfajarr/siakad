@extends('layouts.app')
@section('title', 'Manajemen Wakil Direktur')

@push('styles')
    <link rel="stylesheet" href="{{ asset('assets/vendor/libs/datatables-bs5/datatables.bootstrap5.css') }}">
    <link rel="stylesheet" href="{{ asset('assets/vendor/libs/select2/select2.css') }}">
@endpush

@section('content')
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h5 class="mb-0">Manajemen Wakil Direktur</h5>
                    <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addWadirModal">
                        <i class="ri-add-line me-1"></i> Tambah Wakil Direktur
                    </button>
                </div>
                <div class="card-body">
                    <div class="table-responsive text-nowrap">
                        <table class="table table-hover" id="tableWadir">
                            <thead>
                                <tr>
                                    <th>No</th>
                                    <th>Bidang Wadir</th>
                                    <th>Nama Lengkap</th>
                                    <th>Status Aktif</th>
                                    <th class="text-center">Aksi</th>
                                </tr>
                            </thead>
                            <tbody class="table-border-bottom-0">
                                @foreach ($wakil_direkturs as $index => $item)
                                    <tr>
                                        <td>{{ $index + 1 }}</td>
                                        <td>
                                            @if($item->tipe_wadir == 1)
                                                <span class="badge bg-label-primary">Bidang 1 (Akademik)</span>
                                            @elseif($item->tipe_wadir == 2)
                                                <span class="badge bg-label-success">Bidang 2 (Keuangan)</span>
                                            @elseif($item->tipe_wadir == 3)
                                                <span class="badge bg-label-warning">Bidang 3 (Kemahasiswaan)</span>
                                            @else
                                                <span class="badge bg-label-secondary">Lainnya</span>
                                            @endif
                                        </td>
                                        <td>
                                            {{ $item->dosen->nama_admin_display ?? $item->dosen->nama_lengkap }}
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
                                                    data-bs-toggle="modal" data-bs-target="#editWadirModal{{ $item->id }}"
                                                    title="Ubah Status">
                                                    <i class="ri-edit-2-line text-primary"></i>
                                                </button>
                                                <form action="{{ route('admin.wakil-direktur.destroy', $item->id) }}"
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
                                    <div class="modal fade" id="editWadirModal{{ $item->id }}" tabindex="-1" aria-hidden="true">
                                        <div class="modal-dialog modal-dialog-centered">
                                            <div class="modal-content">
                                                <form action="{{ route('admin.wakil-direktur.update', $item->id) }}"
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
    <div class="modal fade" id="addWadirModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered modal-lg">
            <div class="modal-content">
                <form action="{{ route('admin.wakil-direktur.store') }}" method="POST">
                    @csrf
                    <div class="modal-header">
                        <h5 class="modal-title">Tambah Wakil Direktur Baru</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        <div class="mb-3">
                            <label class="form-label">Bidang Wakil Direktur</label>
                            <select name="tipe_wadir" class="form-select" required>
                                <option value="">-- Pilih Bidang --</option>
                                <option value="1">Bidang 1 (Akademik)</option>
                                <option value="2">Bidang 2 (Keuangan)</option>
                                <option value="3">Bidang 3 (Kemahasiswaan)</option>
                            </select>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Pilih Dosen Kandidat</label>
                            <select name="id_dosen" class="form-select select2-wadir" style="width: 100%;" required>
                                <option value="">-- Cari Dosen --</option>
                                @foreach($dosens as $d)
                                    <option value="{{ $d->id }}">{{ $d->nama_lengkap }} - {{ $d->nidn ?? $d->nip }}</option>
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
        $(document).ready(function () {
            $('#tableWadir').DataTable();
            $('.select2-wadir').select2({
                dropdownParent: $('#addWadirModal')
            });
        });
    </script>
@endpush
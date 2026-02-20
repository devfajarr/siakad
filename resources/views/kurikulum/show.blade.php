@extends('layouts.app')

@section('title', 'Detail Kurikulum')

@push('styles')
    <link rel="stylesheet" href="{{ asset('assets/vendor/libs/datatables-bs5/datatables.bootstrap5.css') }}" />
    <link rel="stylesheet" href="{{ asset('assets/vendor/libs/datatables-responsive-bs5/responsive.bootstrap5.css') }}" />
    <link rel="stylesheet" href="{{ asset('assets/vendor/libs/datatables-buttons-bs5/buttons.bootstrap5.css') }}" />
    <link rel="stylesheet" href="{{ asset('assets/vendor/libs/select2/select2.css') }}" />
@endpush

@section('content')
    <h4 class="fw-bold py-3 mb-4">
        <span class="text-muted fw-light">Perkuliahan / Kurikulum /</span> Detail Data
    </h4>

    <!-- Detail Kurikulum -->
    <div class="card mb-4">
        <div class="card-header d-flex justify-content-between align-items-center">
            <h5 class="mb-0">Kurikulum Kuliah</h5>
            <div>
                <a href="{{ route('admin.kurikulum.create') }}" class="btn btn-primary btn-sm">
                    <i class="ri-add-line me-1"></i> Tambah
                </a>
                @if($kurikulum->sumber_data == 'lokal')
                    <a href="{{ route('admin.kurikulum.edit', $kurikulum->id) }}" class="btn btn-warning btn-sm">
                        <i class="ri-pencil-line me-1"></i> Ubah
                    </a>
                    <form action="{{ route('admin.kurikulum.destroy', $kurikulum->id) }}" method="POST" class="d-inline"
                        onsubmit="return confirm('Yakin ingin menghapus data ini?');">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="btn btn-danger btn-sm">
                            <i class="ri-delete-bin-line me-1"></i> Hapus
                        </button>
                    </form>
                @endif
                <a href="{{ route('admin.kurikulum.index') }}" class="btn btn-success btn-sm">
                    <i class="ri-list-check me-1"></i> Daftar
                </a>
            </div>
        </div>
        <div class="card-body">
            <div class="alert alert-info d-flex align-items-center" role="alert">
                <i class="ri-information-line me-2"></i>
                <div>Mengatur Kurikulum per Program Studi</div>
            </div>

            <div class="row">
                <div class="col-md-6 mb-3">
                    <label class="form-label fw-bold">Nama Kurikulum <span class="text-danger">*</span></label>
                    <input type="text" class="form-control" value="{{ $kurikulum->nama_kurikulum }}" readonly>
                </div>
                <div class="col-md-6 mb-3">
                    <label class="form-label fw-bold">Jumlah Bobot Mata Kuliah Pilihan <span
                            class="text-danger">*</span></label>
                    <div class="input-group">
                        <input type="text" class="form-control" value="{{ $kurikulum->jumlah_sks_pilihan }}" readonly>
                        <span class="input-group-text">sks</span>
                    </div>
                </div>
                <div class="col-md-6 mb-3">
                    <label class="form-label fw-bold">Program Studi <span class="text-danger">*</span></label>
                    <input type="text" class="form-control" value="{{ $kurikulum->prodi->nama_program_studi ?? '-' }}"
                        readonly>
                </div>
                <div class="col-md-6 mb-3">
                    <label class="form-label fw-bold">Mulai Berlaku <span class="text-danger">*</span></label>
                    <input type="text" class="form-control" value="{{ $kurikulum->semester->nama_semester ?? '-' }}"
                        readonly>
                </div>
                <div class="col-md-6 mb-3">
                    <label class="form-label fw-bold">Jumlah SKS</label>
                    <div class="input-group">
                        <input type="text" class="form-control bg-light" value="{{ $kurikulum->jumlah_sks_lulus }}"
                            readonly>
                        <span class="input-group-text text-muted">( sks Wajib + sks Pilihan )</span>
                    </div>
                </div>
                <div class="col-md-6 mb-3">
                    <label class="form-label fw-bold">Jumlah Bobot Mata Kuliah Wajib <span
                            class="text-danger">*</span></label>
                    <div class="input-group">
                        <input type="text" class="form-control" value="{{ $kurikulum->jumlah_sks_wajib }}" readonly>
                        <span class="input-group-text">sks</span>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Kelola Mata Kuliah -->
    <div class="card">
        <div class="card-header border-bottom">
            <h5 class="mb-0 text-primary">Salin data Matakuliah Kurikulum dari</h5>
        </div>
        <div class="card-body pt-4">
            <div class="row g-3 align-items-center mb-4">
                <div class="col-md-4">
                    <select class="form-select select2" disabled>
                        <option>Pilih Kurikulum yang akan disalin</option>
                        <!-- Placeholder for future implementation -->
                    </select>
                </div>
                <div class="col-md-8">
                    <button class="btn btn-info text-white me-1 disabled">
                        <i class="ri-file-copy-line me-1"></i> SALIN MATAKULIAH
                    </button>
                    @if($kurikulum->sumber_data == 'lokal')
                        <button class="btn btn-warning text-white me-1 disabled">
                            <i class="ri-edit-2-line me-1"></i> EDIT KOLEKTIF MATAKULIAH
                        </button>
                        <button class="btn btn-info text-white" data-bs-toggle="modal" data-bs-target="#modalTambahMatkul">
                            <i class="ri-add-line me-1"></i> TAMBAH MATAKULIAH
                        </button>
                    @endif
                </div>
            </div>

            <div class="table-responsive text-nowrap">
                <table class="table table-hover table-striped" id="table-matkul-kurikulum">
                    <thead>
                        <tr>
                            <th>Action</th>
                            <th>Status</th>
                            <th>No</th>
                            <th>Kode MataKuliah</th>
                            <th>Nama MataKuliah</th>
                            <th class="text-center">Mata Kuliah (SKS)</th>
                            <th class="text-center">Tatap Muka (SKS)</th>
                            <th class="text-center">Praktikum (SKS)</th>
                            <th class="text-center">Prakt Lapangan (SKS)</th>
                            <th class="text-center">Simulasi (SKS)</th>
                            <th class="text-center">Semester</th>
                            <th class="text-center">Wajib</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($kurikulum->matakuliah as $index => $mk)
                            <tr>
                                <td>
                                    @if($kurikulum->sumber_data == 'lokal')
                                        <form
                                            action="{{ route('admin.kurikulum.matkul.destroy', ['id' => $kurikulum->id, 'id_matkul' => $mk->id_matkul]) }}"
                                            method="POST" onsubmit="return confirm('Hapus mata kuliah ini dari kurikulum?');">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="btn btn-icon btn-sm btn-danger">
                                                <i class="ri-delete-bin-line"></i>
                                            </button>
                                        </form>
                                    @endif
                                </td>
                                <td>
                                    @if($mk->pivot->status_sinkronisasi == 'synced')
                                        <span class="badge bg-success rounded-pill"><i class="ri-check-line me-1"></i> sudah
                                            sync</span>
                                    @else
                                        <span class="badge bg-warning rounded-pill">belum sync</span>
                                    @endif
                                </td>
                                <td>{{ $index + 1 }}</td>
                                <td>{{ $mk->kode_mk }}</td>
                                <td>{{ $mk->nama_mk }}</td>
                                <td class="text-center">{{ $mk->pivot->sks_mata_kuliah }}</td>
                                <td class="text-center">{{ $mk->pivot->sks_tatap_muka }}</td>
                                <td class="text-center">{{ $mk->pivot->sks_praktek }}</td>
                                <td class="text-center">{{ $mk->pivot->sks_praktek_lapangan }}</td>
                                <td class="text-center">{{ $mk->pivot->sks_simulasi }}</td>
                                <td class="text-center">{{ $mk->pivot->semester }}</td>
                                <td class="text-center">
                                    @if($mk->pivot->apakah_wajib)
                                        <i class="ri-checkbox-circle-fill text-primary fs-4"></i>
                                    @else
                                        <i class="ri-close-circle-line text-muted fs-4"></i>
                                    @endif
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                    <tfoot>
                        <tr class="table-primary fw-bold">
                            <td colspan="5" class="text-end">Total SKS</td>
                            <td class="text-center">
                                {{ $kurikulum->matakuliah->sum(fn($mk) => $mk->pivot->sks_mata_kuliah) }}
                            </td>
                            <td colspan="6"></td>
                        </tr>
                    </tfoot>
                </table>
            </div>
        </div>
    </div>

    <!-- Modal Tambah Matkul -->
    @include('kurikulum._modal_tambah_matakuliah')

@endsection

@push('scripts')
    <script src="{{ asset('assets/vendor/libs/datatables-bs5/datatables-bootstrap5.js') }}"></script>
    <script src="{{ asset('assets/vendor/libs/select2/select2.js') }}"></script>
    <script>
        $(document).ready(function () {
            $('.select2').select2({
                dropdownParent: $('#modalTambahMatkul')
            });

            $('#table-matkul-kurikulum').DataTable({
                responsive: false,
                scrollX: true,
                dom: '<"row"<"col-sm-12 col-md-6"l><"col-sm-12 col-md-6 d-flex justify-content-center justify-content-md-end"f>>t<"row"<"col-sm-12 col-md-6"i><"col-sm-12 col-md-6"p>>',
                language: {
                    search: '',
                    searchPlaceholder: 'Cari Mata Kuliah...',
                    paginate: {
                        next: '<i class="ri-arrow-right-s-line"></i>',
                        previous: '<i class="ri-arrow-left-s-line"></i>'
                    }
                }
            });
        });
    </script>
@endpush
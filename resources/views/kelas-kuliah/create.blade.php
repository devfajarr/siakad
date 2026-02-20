@extends('layouts.app')

@section('title', 'Tambah Kelas Kuliah')

@push('styles')
    <link rel="stylesheet" href="{{ asset('assets/vendor/libs/select2/select2.css') }}" />
@endpush

@section('content')
    <h4 class="fw-bold py-3 mb-4">
        <span class="text-muted fw-light">Perkuliahan / Kelas Kuliah /</span> Tambah Data
    </h4>

    <div class="card mb-4">
        <div class="card-header d-flex justify-content-between align-items-center">
            <h5 class="mb-0">Kelas Kuliah</h5>
            <small class="text-muted float-end">Mengatur Kelas Kuliah per Semester</small>
        </div>
        <div class="card-body">
            @if(session('error'))
                <div class="alert alert-danger alert-dismissible fade show" role="alert">
                    <strong>Error!</strong> {{ session('error') }}
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
            @endif

            <form action="{{ route('admin.kelas-kuliah.store') }}" method="POST">
                @csrf

                {{-- Row 1: Program Studi & Semester (Feeder: id_prodi, id_semester) --}}
                <div class="row mb-3">
                    <div class="col-md-6">
                        <label class="form-label" for="id_prodi">Program Studi <span class="text-danger">*</span></label>
                        <select class="form-select @error('id_prodi') is-invalid @enderror" id="id_prodi" name="id_prodi" required>
                            <option value="">-- Pilih Program Studi --</option>
                            @foreach($prodis as $prodi)
                                <option value="{{ $prodi->id_prodi }}" {{ old('id_prodi') == $prodi->id_prodi ? 'selected' : '' }}>
                                    {{ $prodi->nama_program_studi }} ({{ $prodi->nama_jenjang_pendidikan }})
                                </option>
                            @endforeach
                        </select>
                        @error('id_prodi')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                    <div class="col-md-6">
                        <label class="form-label" for="id_semester">Semester <span class="text-danger">*</span></label>
                        <select class="form-select @error('id_semester') is-invalid @enderror" id="id_semester" name="id_semester" required>
                            <option value="">-- Pilih Semester --</option>
                            @foreach($semesters as $semester)
                                <option value="{{ $semester->id_semester }}" {{ old('id_semester') == $semester->id_semester ? 'selected' : '' }}>
                                    {{ $semester->nama_semester }} {{ $semester->a_periode_aktif == 1 ? '(Aktif)' : '' }}
                                </option>
                            @endforeach
                        </select>
                        @error('id_semester')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                </div>

                {{-- Row 2: Mata Kuliah & Nama Kelas (Feeder: id_matkul, nama_kelas_kuliah) --}}
                <div class="row mb-3">
                    <div class="col-md-6">
                        <label class="form-label" for="mata_kuliah_id">Mata Kuliah <span class="text-danger">*</span></label>
                        <select class="form-select @error('id_matkul') is-invalid @enderror" id="mata_kuliah_id" name="id_matkul" required>
                            <option value="">-- Pilih Mata Kuliah --</option>
                            @foreach($mataKuliahs as $mataKuliah)
                                <option value="{{ $mataKuliah->id_matkul }}" {{ old('id_matkul') == $mataKuliah->id_matkul ? 'selected' : '' }}>
                                    {{ $mataKuliah->kode_mk }} - {{ $mataKuliah->nama_mk }}
                                </option>
                            @endforeach
                        </select>
                        @error('id_matkul')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                    <div class="col-md-6">
                        <label class="form-label" for="nama_kelas_kuliah">Nama Kelas <span class="text-danger">*</span></label>
                        <input type="text"
                               class="form-control @error('nama_kelas_kuliah') is-invalid @enderror"
                               id="nama_kelas_kuliah"
                               name="nama_kelas_kuliah"
                               value="{{ old('nama_kelas_kuliah') }}"
                               maxlength="5"
                               required
                               placeholder="Maks. 5 karakter (contoh: A, B1, R01)">
                        @error('nama_kelas_kuliah')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                        <small class="text-muted">Sesuai aturan Feeder: maksimum 5 karakter.</small>
                    </div>
                </div>

                {{-- Row 3: Lingkup & Mode Kuliah (Feeder: lingkup, mode) --}}
                <div class="row mb-3">
                    <div class="col-md-6">
                        <label class="form-label" for="lingkup">Lingkup</label>
                        <select class="form-select @error('lingkup') is-invalid @enderror" id="lingkup" name="lingkup">
                            <option value="">-- Pilih Lingkup --</option>
                            <option value="1" {{ old('lingkup') == '1' ? 'selected' : '' }}>Internal</option>
                            <option value="2" {{ old('lingkup') == '2' ? 'selected' : '' }}>External</option>
                            <option value="3" {{ old('lingkup') == '3' ? 'selected' : '' }}>Campuran</option>
                        </select>
                        @error('lingkup')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                    <div class="col-md-6">
                        <label class="form-label" for="mode">Mode Kuliah</label>
                        <select class="form-select @error('mode') is-invalid @enderror" id="mode" name="mode">
                            <option value="">-- Pilih Mode Kuliah --</option>
                            <option value="O" {{ old('mode') == 'O' ? 'selected' : '' }}>Online</option>
                            <option value="F" {{ old('mode') == 'F' ? 'selected' : '' }}>Offline</option>
                            <option value="M" {{ old('mode') == 'M' ? 'selected' : '' }}>Campuran</option>
                        </select>
                        @error('mode')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                </div>

                {{-- Row 4: Tanggal Mulai & Akhir Efektif (Feeder: tanggal_mulai_efektif, tanggal_akhir_efektif) --}}
                <div class="row mb-3">
                    <div class="col-md-6">
                        <label class="form-label" for="tanggal_mulai_efektif">Tanggal Mulai Efektif</label>
                        <input type="date"
                               class="form-control @error('tanggal_mulai_efektif') is-invalid @enderror"
                               id="tanggal_mulai_efektif"
                               name="tanggal_mulai_efektif"
                               value="{{ old('tanggal_mulai_efektif') }}">
                        @error('tanggal_mulai_efektif')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                    <div class="col-md-6">
                        <label class="form-label" for="tanggal_akhir_efektif">Tanggal Akhir Efektif</label>
                        <input type="date"
                               class="form-control @error('tanggal_akhir_efektif') is-invalid @enderror"
                               id="tanggal_akhir_efektif"
                               name="tanggal_akhir_efektif"
                               value="{{ old('tanggal_akhir_efektif') }}">
                        @error('tanggal_akhir_efektif')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                </div>
                {{-- Buttons --}}
                <div class="mt-4 d-flex justify-content-end gap-2">
                    <a href="{{ route('admin.kelas-kuliah.index') }}" class="btn btn-outline-secondary">
                        <i class="ri-arrow-left-line me-1"></i> Kembali
                    </a>
                    <button type="submit" class="btn btn-primary">
                        <i class="ri-save-line me-1"></i> Simpan
                    </button>
                </div>
            </form>
        </div>
    </div>
@endsection

@push('scripts')
    <script src="{{ asset('assets/vendor/libs/select2/select2.js') }}"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const mataKuliahSelect = $('#mata_kuliah_id');

            if (mataKuliahSelect.length) {
                mataKuliahSelect.select2({
                    placeholder: 'Pilih Mata Kuliah',
                    allowClear: true,
                    width: '100%'
                });
            }
        });
    </script>
@endpush

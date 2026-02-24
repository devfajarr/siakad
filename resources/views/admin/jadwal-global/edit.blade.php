@extends('layouts.app')

@section('title', 'Edit Jadwal Kuliah')

@push('styles')
    <link rel="stylesheet" href="{{ asset('assets/vendor/libs/select2/select2.css') }}" />
@endpush

@section('content')
    <h4 class="fw-bold py-3 mb-4">
        <span class="text-muted fw-light">Jadwal Terpadu /</span> Edit Jadwal
    </h4>

    <div class="row">
        <div class="col-md-8">
            <div class="card mb-4 shadow-sm border-0">
                <div class="card-header bg-label-warning d-flex justify-content-between align-items-center p-4">
                    <h5 class="mb-0 text-warning fw-bold">
                        <i class="ri-pencil-line me-2"></i> Formulir Perubahan Jadwal
                    </h5>
                    <span class="badge bg-warning rounded-pill px-3">
                        Semester: {{ $jadwal->kelasKuliah->semester->nama_semester }}
                    </span>
                </div>
                <div class="card-body p-4 pt-5">
                    @if(session('error'))
                        <div class="alert alert-danger alert-dismissible fade show mb-4" role="alert">
                            <i class="ri-error-warning-line me-2"></i> {{ session('error') }}
                            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                        </div>
                    @endif

                    <form action="{{ route('admin.jadwal-global.update', $jadwal->id) }}" method="POST">
                        @csrf
                        @method('PUT')

                        <div class="row g-4">
                            {{-- Section: Informasi Kelas & Dosen (Read Only) --}}
                            <div class="col-12 mb-2">
                                <div class="p-3 bg-label-secondary rounded border-start border-4 border-primary">
                                    <div class="row align-items-center">
                                        <div class="col-md-2 text-center border-end">
                                            <h6 class="mb-0 text-muted small uppercase">Kelas</h6>
                                            <h4 class="mb-0 fw-bold text-primary">{{ $jadwal->kelasKuliah->nama_kelas_kuliah }}</h4>
                                        </div>
                                        <div class="col-md-6 ps-4">
                                            <h5 class="mb-1 fw-bold">{{ $jadwal->kelasKuliah->mataKuliah->nama_mk }}</h5>
                                            <p class="mb-0 text-muted small">
                                                <i class="ri-code-box-line me-1"></i> {{ $jadwal->kelasKuliah->mataKuliah->kode_mk }} 
                                                <span class="mx-2">|</span>
                                                <i class="ri-book-open-line me-1"></i> {{ $jadwal->kelasKuliah->mataKuliah->sks }} SKS
                                            </p>
                                        </div>
                                        <div class="col-md-4 text-end">
                                            <a href="{{ route('admin.kelas-kuliah.show', $jadwal->kelas_kuliah_id) }}" class="btn btn-sm btn-outline-primary">
                                                <i class="ri-settings-4-line me-1"></i> Manajemen Kelas
                                            </a>
                                        </div>
                                    </div>
                                    <div class="mt-3 pt-3 border-top">
                                        <h6 class="text-muted small mb-2"><i class="ri-user-voice-line me-1"></i> Tim Dosen Pengajar:</h6>
                                        <div class="d-flex flex-wrap gap-2">
                                            @foreach($dosenPengajars as $dp)
                                                <span class="badge bg-white text-dark border shadow-sm px-3 py-2">
                                                    {{ $dp->dosen->nama }}
                                                </span>
                                            @endforeach
                                        </div>
                                        <small class="text-muted d-block mt-2">
                                            <i class="ri-information-line me-1"></i> Daftar dosen di atas bersifat <b>Read-Only</b>. Update pengampu melalui menu Manajemen Kelas.
                                        </small>
                                    </div>
                                </div>
                            </div>

                            {{-- Form Fields (Editable) --}}
                            <div class="col-md-6">
                                <label class="form-label fw-bold" for="hari">Hari <span class="text-danger">*</span></label>
                                <select id="hari" name="hari" class="form-select select2-no-search" required>
                                    @php
                                        $hariMap = [1 => 'Senin', 2 => 'Selasa', 3 => 'Rabu', 4 => 'Kamis', 5 => 'Jumat', 6 => 'Sabtu', 7 => 'Minggu'];
                                    @endphp
                                    @foreach($hariMap as $val => $label)
                                        <option value="{{ $val }}" {{ old('hari', $jadwal->hari) == $val ? 'selected' : '' }}>
                                            {{ $label }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>

                            <div class="col-md-6">
                                <label class="form-label fw-bold" for="ruang_id">Ruangan <span
                                        class="text-danger">*</span></label>
                                <select id="ruang_id" name="ruang_id" class="form-select select2" required>
                                    @foreach($ruangs as $ruang)
                                        <option value="{{ $ruang->id }}" {{ old('ruang_id', $jadwal->ruang_id) == $ruang->id ? 'selected' : '' }}>
                                            {{ $ruang->kode_ruang }} - {{ $ruang->nama_ruang }} (Kap: {{ $ruang->kapasitas }})
                                        </option>
                                    @endforeach
                                </select>
                            </div>

                            <div class="col-md-6">
                                <label class="form-label fw-bold" for="jam_mulai">Jam Mulai <span
                                        class="text-danger">*</span></label>
                                <div class="input-group">
                                    <span class="input-group-text"><i class="ri-time-line"></i></span>
                                    <input type="time" id="jam_mulai" name="jam_mulai" class="form-control"
                                        value="{{ old('jam_mulai', \Carbon\Carbon::parse($jadwal->jam_mulai)->format('H:i')) }}"
                                        required>
                                </div>
                            </div>

                            <div class="col-md-6">
                                <label class="form-label fw-bold" for="jam_selesai">Jam Selesai <span
                                        class="text-danger">*</span></label>
                                <div class="input-group">
                                    <span class="input-group-text"><i class="ri-time-line"></i></span>
                                    <input type="time" id="jam_selesai" name="jam_selesai" class="form-control"
                                        value="{{ old('jam_selesai', \Carbon\Carbon::parse($jadwal->jam_selesai)->format('H:i')) }}"
                                        required>
                                </div>
                            </div>

                            {{-- Removed Dosen Pengajar Select2 --}}

                            <div class="col-12">
                                <label class="form-label fw-bold" for="jenis_pertemuan">Jenis Pertemuan <span
                                        class="text-muted">(Opsional)</span></label>
                                <input type="text" id="jenis_pertemuan" name="jenis_pertemuan" class="form-control"
                                    placeholder="Contoh: Teori, Praktikum, Tutorial"
                                    value="{{ old('jenis_pertemuan', $jadwal->jenis_pertemuan) }}">
                            </div>

                            <div class="col-12 mt-4 pt-3 border-top">
                                <div class="d-flex justify-content-between">
                                    <a href="{{ route('admin.jadwal-global.index', ['hari' => $jadwal->hari]) }}"
                                        class="btn btn-outline-secondary">
                                        <i class="ri-arrow-left-line me-1"></i> Batal & Kembali
                                    </a>
                                    <button type="submit" class="btn btn-warning px-5">
                                        <i class="ri-save-line me-1"></i> Simpan Perubahan Jadwal
                                    </button>
                                </div>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <div class="col-md-4">
            {{-- Card: Panduan & Validasi --}}
            <div class="card mb-4 border-0 shadow-sm">
                <div class="card-header bg-label-primary p-3">
                    <h6 class="mb-0 fw-bold border-bottom pb-2">Aturan Validasi Jadwal</h6>
                </div>
                <div class="card-body p-3">
                    <div class="d-flex mb-3 align-items-start">
                        <div class="avatar avatar-sm bg-label-info me-3 flex-shrink-0">
                            <span class="avatar-initial rounded"><i class="ri-shield-check-line"></i></span>
                        </div>
                        <div>
                            <p class="mb-0 small fw-bold">Pemeriksaan Bentrok</p>
                            <small class="text-muted">Sistem akan secara otomatis memeriksa ketersediaan ruangan dan waktu
                                mengajar dosen pengajar.</small>
                        </div>
                    </div>
                    <div class="d-flex mb-3 align-items-start">
                        <div class="avatar avatar-sm bg-label-warning me-3 flex-shrink-0">
                            <span class="avatar-initial rounded"><i class="ri-history-line"></i></span>
                        </div>
                        <div>
                            <p class="mb-0 small fw-bold">Dampak Presensi</p>
                            <small class="text-muted text-warning">Mengubah jadwal pada kelas yang <b>sudah mulai
                                    berjalan</b> dapat menyebabkan ketidaksesuaian data pada jurnal perkuliahan.</small>
                        </div>
                    </div>
                    <div class="d-flex align-items-start">
                        <div class="avatar avatar-sm bg-label-success me-3 flex-shrink-0">
                            <span class="avatar-initial rounded"><i class="ri-exchange-line"></i></span>
                        </div>
                        <div>
                            <p class="mb-0 small fw-bold">Sinkronisasi Lokal</p>
                            <small class="text-muted">Perubahan ini hanya tersimpan di database lokal siakad dan perlu
                                di-push ke Feeder jika diperlukan.</small>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection

@push('scripts')
    <script src="{{ asset('assets/vendor/libs/select2/select2.js') }}"></script>
    <script>
        $(document).ready(function () {
            $('.select2').select2({
                placeholder: "-- Pilih Data --",
                width: '100%'
            });
            $('.select2-no-search').select2({
                placeholder: "-- Pilih Data --",
                width: '100%',
                minimumResultsForSearch: Infinity
            });
        });
    </script>
@endpush
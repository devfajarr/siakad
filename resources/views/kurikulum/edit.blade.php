@extends('layouts.app')

@section('title', 'Edit Kurikulum')

@section('content')
    <h4 class="fw-bold py-3 mb-4">
        <span class="text-muted fw-light">Perkuliahan / Kurikulum /</span> Edit Data
    </h4>

    <div class="card mb-4">
        <div class="card-header d-flex justify-content-between align-items-center">
            <h5 class="mb-0">Edit Kurikulum Kuliah</h5>
            <small class="text-muted float-end">Mengatur Kurikulum per Program Studi</small>
        </div>
        <div class="card-body">
            <form action="{{ route('admin.kurikulum.update', $kurikulum->id) }}" method="POST">
                @csrf
                @method('PUT')

                {{-- Row 1: Nama Kurikulum & SKS Pilihan --}}
                <div class="row mb-3">
                    <div class="col-md-6">
                        <label class="form-label" for="nama_kurikulum">Nama Kurikulum <span
                                class="text-danger">*</span></label>
                        <input type="text" class="form-control @error('nama_kurikulum') is-invalid @enderror"
                            id="nama_kurikulum" name="nama_kurikulum"
                            value="{{ old('nama_kurikulum', $kurikulum->nama_kurikulum) }}" required
                            placeholder="Contoh: Kurikulum 2024">
                        @error('nama_kurikulum')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                    <div class="col-md-6">
                        <label class="form-label" for="jumlah_sks_pilihan">Jumlah Bobot Mata Kuliah Pilihan <span
                                class="text-danger">*</span></label>
                        <div class="input-group">
                            <input type="number"
                                class="form-control sks-input @error('jumlah_sks_pilihan') is-invalid @enderror"
                                id="jumlah_sks_pilihan" name="jumlah_sks_pilihan"
                                value="{{ old('jumlah_sks_pilihan', $kurikulum->jumlah_sks_pilihan) }}" min="0" required>
                            <span class="input-group-text">sks</span>
                            @error('jumlah_sks_pilihan')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>
                </div>

                {{-- Row 2: Prodi & Mulai Berlaku --}}
                <div class="row mb-3">
                    <div class="col-md-6">
                        <label class="form-label" for="id_prodi">Program Studi <span class="text-danger">*</span></label>
                        <select class="form-select @error('id_prodi') is-invalid @enderror" id="id_prodi" name="id_prodi"
                            required>
                            <option value="">-- Pilih Program Studi --</option>
                            @foreach($prodis as $prodi)
                                <option value="{{ $prodi->id_prodi }}" {{ old('id_prodi', $kurikulum->id_prodi) == $prodi->id_prodi ? 'selected' : '' }}>
                                    {{ $prodi->nama_program_studi }} ({{ $prodi->nama_jenjang_pendidikan }})
                                </option>
                            @endforeach
                        </select>
                        @error('id_prodi')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                    <div class="col-md-6">
                        <label class="form-label" for="id_semester">Mulai Berlaku <span class="text-danger">*</span></label>
                        <select class="form-select @error('id_semester') is-invalid @enderror" id="id_semester"
                            name="id_semester" required>
                            <option value="">-- Pilih Semester --</option>
                            @foreach($semesters as $semester)
                                <option value="{{ $semester->id_semester }}" {{ old('id_semester', $kurikulum->id_semester) == $semester->id_semester ? 'selected' : '' }}>
                                    {{ $semester->nama_semester }} {{ $semester->a_periode_aktif == 1 ? '(Aktif)' : '' }}
                                </option>
                            @endforeach
                        </select>
                        @error('id_semester')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                </div>

                {{-- Row 3: Total SKS & SKS Wajib --}}
                <div class="row mb-3">
                    <div class="col-md-6">
                        <label class="form-label" for="jumlah_sks_lulus">Jumlah SKS</label>
                        <div class="input-group">
                            <input type="number" class="form-control bg-light" id="jumlah_sks_lulus" name="jumlah_sks_lulus"
                                value="{{ old('jumlah_sks_lulus', $kurikulum->jumlah_sks_lulus) }}" readonly>
                            <span class="input-group-text text-muted">( sks Wajib + sks Pilihan )</span>
                        </div>
                        <small class="text-muted">Otomatis terhitung.</small>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label" for="jumlah_sks_wajib">Jumlah Bobot Mata Kuliah Wajib <span
                                class="text-danger">*</span></label>
                        <div class="input-group">
                            <input type="number"
                                class="form-control sks-input @error('jumlah_sks_wajib') is-invalid @enderror"
                                id="jumlah_sks_wajib" name="jumlah_sks_wajib"
                                value="{{ old('jumlah_sks_wajib', $kurikulum->jumlah_sks_wajib) }}" min="0" required>
                            <span class="input-group-text">sks</span>
                            @error('jumlah_sks_wajib')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>
                </div>

                {{-- Buttons --}}
                <div class="mt-4 d-flex justify-content-end gap-2">
                    <a href="{{ route('admin.kurikulum.index') }}" class="btn btn-outline-secondary">
                        <i class="ri-arrow-left-line me-1"></i> Kembali
                    </a>
                    <button type="submit" class="btn btn-primary">
                        <i class="ri-save-line me-1"></i> Simpan Perubahan
                    </button>
                </div>
            </form>
        </div>
    </div>
@endsection

@push('scripts')
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const sksWajib = document.getElementById('jumlah_sks_wajib');
            const sksPilihan = document.getElementById('jumlah_sks_pilihan');
            const sksTotal = document.getElementById('jumlah_sks_lulus');

            function calculateTotal() {
                const wajib = parseInt(sksWajib.value) || 0;
                const pilihan = parseInt(sksPilihan.value) || 0;
                sksTotal.value = wajib + pilihan;
            }

            sksWajib.addEventListener('input', calculateTotal);
            sksPilihan.addEventListener('input', calculateTotal);

            // Initial calculation
            calculateTotal();
        });
    </script>
@endpush
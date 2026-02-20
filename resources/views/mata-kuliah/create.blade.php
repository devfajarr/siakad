@extends('layouts.app')

@section('title', 'Tambah Mata Kuliah')

@section('content')
    <h4 class="fw-bold py-3 mb-4">
        <span class="text-muted fw-light">Perkuliahan / Mata Kuliah /</span> Tambah
    </h4>

    <div class="card shadow-sm">
        <div class="card-header border-bottom d-flex justify-content-between align-items-center">
            <h5 class="card-title mb-0">Form Tambah Mata Kuliah</h5>
            @if(app()->isLocal())
                <button type="button" class="btn btn-sm btn-outline-warning" id="btnAutofill">
                    <i class="ri-magic-line me-1"></i> Autofill
                </button>
            @endif
        </div>

        <form action="{{ route('admin.mata-kuliah.store') }}" method="POST">
            @csrf

            <div class="card-body">
                {{-- Alert Info --}}
                <div class="alert alert-info d-flex align-items-center" role="alert">
                    <i class="ri-information-line me-2"></i>
                    <div>
                        Data yang ditambahkan akan berstatus <strong>Lokal</strong> dan akan dikirim ke server PDDikti
                        saat sinkronisasi.
                    </div>
                </div>

                <div class="row g-4">
                    {{-- Kode MK & Nama MK --}}
                    <div class="col-md-6">
                        <label class="form-label required" for="kode_mk">Kode Mata Kuliah</label>
                        <input type="text" id="kode_mk" name="kode_mk"
                            class="form-control @error('kode_mk') is-invalid @enderror" value="{{ old('kode_mk') }}"
                            placeholder="Contoh: TIF101" required>
                        @error('kode_mk')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="col-md-6">
                        <label class="form-label required" for="nama_mk">Nama Mata Kuliah</label>
                        <input type="text" id="nama_mk" name="nama_mk"
                            class="form-control @error('nama_mk') is-invalid @enderror" value="{{ old('nama_mk') }}"
                            placeholder="Contoh: Algoritma Pemrograman" required>
                        @error('nama_mk')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    {{-- Prodi & Jenis MK --}}
                    <div class="col-md-6">
                        <label class="form-label required" for="id_prodi">Program Studi Pengampu</label>
                        <select id="id_prodi" name="id_prodi" class="form-select @error('id_prodi') is-invalid @enderror"
                            required>
                            <option value="">-- Pilih Program Studi --</option>
                            @foreach($prodi as $p)
                                <option value="{{ $p->id_prodi }}" {{ old('id_prodi') == $p->id_prodi ? 'selected' : '' }}>
                                    {{ $p->nama_program_studi }} ({{ $p->kode_program_studi }})
                                </option>
                            @endforeach
                        </select>
                        @error('id_prodi')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="col-md-6">
                        <label class="form-label required" for="jenis_mk">Jenis Mata Kuliah</label>
                        <select id="jenis_mk" name="jenis_mk" class="form-select @error('jenis_mk') is-invalid @enderror"
                            required>
                            <option value="">-- Pilih Jenis --</option>
                            <option value="A" {{ old('jenis_mk') == 'A' ? 'selected' : '' }}>A - Wajib</option>
                            <option value="B" {{ old('jenis_mk') == 'B' ? 'selected' : '' }}>B - Pilihan</option>
                            <option value="C" {{ old('jenis_mk') == 'C' ? 'selected' : '' }}>C - Wajib Peminatan</option>
                            <option value="D" {{ old('jenis_mk') == 'D' ? 'selected' : '' }}>D - Pilihan Peminatan</option>
                            <option value="S" {{ old('jenis_mk') == 'S' ? 'selected' : '' }}>S - Tugas Akhir/Skripsi/Tesis
                            </option>
                        </select>
                        @error('jenis_mk')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    {{-- Kelompok MK --}}
                    <div class="col-md-12">
                        <label class="form-label required" for="kelompok_mk">Kelompok Mata Kuliah</label>
                        <select id="kelompok_mk" name="kelompok_mk"
                            class="form-select @error('kelompok_mk') is-invalid @enderror" required>
                            <option value="">-- Pilih Kelompok --</option>
                            <option value="A" {{ old('kelompok_mk') == 'A' ? 'selected' : '' }}>A - MPK</option>
                            <option value="B" {{ old('kelompok_mk') == 'B' ? 'selected' : '' }}>B - MKK</option>
                            <option value="C" {{ old('kelompok_mk') == 'C' ? 'selected' : '' }}>C - MKB</option>
                            <option value="D" {{ old('kelompok_mk') == 'D' ? 'selected' : '' }}>D - MPB</option>
                            <option value="E" {{ old('kelompok_mk') == 'E' ? 'selected' : '' }}>E - MBB</option>
                            <option value="F" {{ old('kelompok_mk') == 'F' ? 'selected' : '' }}>F - MKU/MKDU</option>
                            <option value="G" {{ old('kelompok_mk') == 'G' ? 'selected' : '' }}>G - MKDK</option>
                            <option value="H" {{ old('kelompok_mk') == 'H' ? 'selected' : '' }}>H - MKK</option>
                        </select>
                        @error('kelompok_mk')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <hr class="my-0 mt-5">
                    <h6 class="mb-0 fw-bold">Bobot & SKS</h6>

                    {{-- SKS Fields --}}
                    <div class="col-md-12">
                        <div class="alert alert-secondary mb-0 p-2">
                            <small><i class="ri-information-line me-1"></i> Total SKS = Tatap Muka + Praktikum + Lapangan +
                                Simulasi</small>
                        </div>
                    </div>

                    <div class="col-md-4">
                        <label class="form-label required" for="sks">SKS Total</label>
                        <div class="input-group">
                            <input type="number" step="0.01" id="sks" name="sks"
                                class="form-control @error('sks') is-invalid @enderror" value="{{ old('sks', 0) }}" required
                                readonly>
                            <span class="input-group-text">SKS</span>
                        </div>
                        @error('sks')
                            <div class="invalid-feedback d-block">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="col-md-4">
                        <label class="form-label" for="sks_tatap_muka">SKS Tatap Muka</label>
                        <div class="input-group">
                            <input type="number" step="0.01" id="sks_tatap_muka" name="sks_tatap_muka"
                                class="form-control @error('sks_tatap_muka') is-invalid @enderror"
                                value="{{ old('sks_tatap_muka', 0) }}">
                            <span class="input-group-text">SKS</span>
                        </div>
                    </div>

                    <div class="col-md-4">
                        <label class="form-label" for="sks_praktek">SKS Praktikum</label>
                        <div class="input-group">
                            <input type="number" step="0.01" id="sks_praktek" name="sks_praktek"
                                class="form-control @error('sks_praktek') is-invalid @enderror"
                                value="{{ old('sks_praktek', 0) }}">
                            <span class="input-group-text">SKS</span>
                        </div>
                    </div>

                    <div class="col-md-4">
                        <label class="form-label" for="sks_praktek_lapangan">SKS Praktek Lapangan</label>
                        <div class="input-group">
                            <input type="number" step="0.01" id="sks_praktek_lapangan" name="sks_praktek_lapangan"
                                class="form-control @error('sks_praktek_lapangan') is-invalid @enderror"
                                value="{{ old('sks_praktek_lapangan', 0) }}">
                            <span class="input-group-text">SKS</span>
                        </div>
                    </div>

                    <div class="col-md-4">
                        <label class="form-label" for="sks_simulasi">SKS Simulasi</label>
                        <div class="input-group">
                            <input type="number" step="0.01" id="sks_simulasi" name="sks_simulasi"
                                class="form-control @error('sks_simulasi') is-invalid @enderror"
                                value="{{ old('sks_simulasi', 0) }}">
                            <span class="input-group-text">SKS</span>
                        </div>
                    </div>

                    <hr class="my-0 mt-5">
                    <h6 class="mb-0 fw-bold">Detail Tambahan</h6>

                    {{-- Metode & Tanggal --}}
                    <div class="col-md-12">
                        <label class="form-label" for="metode_kuliah">Metode Pembelajaran</label>
                        <input type="text" id="metode_kuliah" name="metode_kuliah"
                            class="form-control @error('metode_kuliah') is-invalid @enderror"
                            value="{{ old('metode_kuliah') }}" placeholder="Contoh: Kuliah Mimbar, Seminar, Praktikum">
                    </div>

                    <div class="col-md-6">
                        <label class="form-label" for="tanggal_mulai_efektif">Tanggal Mulai Efektif</label>
                        <input type="date" id="tanggal_mulai_efektif" name="tanggal_mulai_efektif"
                            class="form-control @error('tanggal_mulai_efektif') is-invalid @enderror"
                            value="{{ old('tanggal_mulai_efektif') }}">
                        @error('tanggal_mulai_efektif')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="col-md-6">
                        <label class="form-label" for="tanggal_akhir_efektif">Tanggal Akhir Efektif</label>
                        <input type="date" id="tanggal_akhir_efektif" name="tanggal_akhir_efektif"
                            class="form-control @error('tanggal_akhir_efektif') is-invalid @enderror"
                            value="{{ old('tanggal_akhir_efektif') }}">
                        @error('tanggal_akhir_efektif')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                </div>
            </div>

            <div class="card-footer border-top d-flex justify-content-end gap-2 py-3">
                <a href="{{ route('admin.mata-kuliah.index') }}" class="btn btn-outline-secondary">
                    <i class="ri-arrow-left-line me-1"></i> Kembali
                </a>
                <button type="submit" class="btn btn-primary">
                    <i class="ri-save-line me-1"></i> Simpan
                </button>
            </div>
        </form>
    </div>
@endsection

@push('scripts')
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const sksTotalInput = document.getElementById('sks');
            const sksFields = [
                'sks_tatap_muka',
                'sks_praktek',
                'sks_praktek_lapangan',
                'sks_simulasi'
            ];

            function calculateTotalSks() {
                let total = 0;
                sksFields.forEach(id => {
                    const element = document.getElementById(id);
                    if (element && element.value) {
                        total += parseFloat(element.value);
                    }
                });
                sksTotalInput.value = total;
            }

            sksFields.forEach(id => {
                const element = document.getElementById(id);
                if (element) {
                    element.addEventListener('input', calculateTotalSks);
                }
            });

            // Autofill Logic
            const btnAutofill = document.getElementById('btnAutofill');
            if (btnAutofill) {
                btnAutofill.addEventListener('click', function () {
                    const randomId = Math.floor(Math.random() * 9999);
                    document.getElementById('kode_mk').value = 'TEST-' + randomId;
                    document.getElementById('nama_mk').value = 'Mata Kuliah Test ' + randomId;

                    // Select first available option for prodi
                    const prodiSelect = document.getElementById('id_prodi');
                    if (prodiSelect.options.length > 1) {
                        prodiSelect.selectedIndex = 1;
                    }

                    document.getElementById('jenis_mk').value = 'A';
                    document.getElementById('kelompok_mk').value = 'A';

                    document.getElementById('sks_tatap_muka').value = 2;
                    document.getElementById('sks_praktek').value = 1;
                    document.getElementById('sks_praktek_lapangan').value = 0;
                    document.getElementById('sks_simulasi').value = 0;

                    // Trigger calculation
                    calculateTotalSks();

                    document.getElementById('metode_kuliah').value = 'Kuliah Mimbar';
                    const todayDate = new Date();
                    const today = todayDate.toISOString().split('T')[0];
                    document.getElementById('tanggal_mulai_efektif').value = today;

                    // Calculate 14 weeks later (14 * 7 days)
                    const endDate = new Date(todayDate);
                    endDate.setDate(todayDate.getDate() + (14 * 7));
                    const end = endDate.toISOString().split('T')[0];
                    document.getElementById('tanggal_akhir_efektif').value = end;
                });
            }
        });
    </script>
@endpush
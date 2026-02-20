<div class="modal fade" id="modalDosen" tabindex="-1" aria-labelledby="modalDosenLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered">
        <div class="modal-content">
            <form method="POST" action="{{ route('kelas.dosen.store') }}">
                @csrf
                <input type="hidden" name="kelas_kuliah_id" value="{{ old('kelas_kuliah_id', $kelasKuliah->id) }}">

                <div class="modal-header">
                    <h5 class="modal-title" id="modalDosenLabel">Tambah Aktivitas Mengajar Dosen</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>

                <div class="modal-body">
                    <div class="row g-3">
                        <div class="col-md-12">
                            <label for="dosen_id" class="form-label">Dosen <span class="text-danger">*</span></label>
                            <select id="dosen_id" name="dosen_id" class="form-select @error('dosen_id') is-invalid @enderror" required>
                                <option value="">Pilih Dosen</option>
                                @foreach ($daftarDosen as $dosen)
                                    <option value="{{ $dosen->id }}" {{ old('dosen_id') == $dosen->id ? 'selected' : '' }}>
                                        {{ $dosen->nama }} {{ $dosen->nidn ? '(' . $dosen->nidn . ')' : '' }}
                                    </option>
                                @endforeach
                            </select>
                            @error('dosen_id')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="col-md-12">
                            <label for="id_substansi" class="form-label">Substansi Perkuliahan</label>
                            <input
                                type="text"
                                id="id_substansi"
                                name="id_substansi"
                                class="form-control"
                                value="{{ old('id_substansi') }}"
                                placeholder="Opsional (belum digunakan pada mode lokal)">
                        </div>

                        <div class="col-md-6">
                            <label for="bobot_sks" class="form-label">Bobot SKS <span class="text-danger">*</span></label>
                            <input
                                type="number"
                                step="0.01"
                                min="0"
                                id="bobot_sks"
                                name="bobot_sks"
                                class="form-control @error('bobot_sks') is-invalid @enderror"
                                value="{{ old('bobot_sks') }}"
                                required>
                            @error('bobot_sks')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="col-md-6">
                            <label for="jenis_evaluasi" class="form-label">Jenis Evaluasi <span class="text-danger">*</span></label>
                            <select
                                id="jenis_evaluasi"
                                name="jenis_evaluasi"
                                class="form-select @error('jenis_evaluasi') is-invalid @enderror"
                                required>
                                <option value="">Pilih Jenis Evaluasi</option>
                                @foreach ($jenisEvaluasiOptions as $value => $label)
                                    <option value="{{ $value }}" {{ old('jenis_evaluasi') == (string) $value ? 'selected' : '' }}>
                                        {{ $label }}
                                    </option>
                                @endforeach
                            </select>
                            @error('jenis_evaluasi')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="col-md-6">
                            <label for="jumlah_rencana_pertemuan" class="form-label">Jumlah Rencana Pertemuan <span class="text-danger">*</span></label>
                            <input
                                type="number"
                                min="0"
                                id="jumlah_rencana_pertemuan"
                                name="jumlah_rencana_pertemuan"
                                class="form-control @error('jumlah_rencana_pertemuan') is-invalid @enderror"
                                value="{{ old('jumlah_rencana_pertemuan') }}"
                                required>
                            @error('jumlah_rencana_pertemuan')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="col-md-6">
                            <label for="jumlah_realisasi_pertemuan" class="form-label">Jumlah Realisasi Pertemuan</label>
                            <input
                                type="number"
                                min="0"
                                id="jumlah_realisasi_pertemuan"
                                name="jumlah_realisasi_pertemuan"
                                class="form-control @error('jumlah_realisasi_pertemuan') is-invalid @enderror"
                                value="{{ old('jumlah_realisasi_pertemuan') }}">
                            @error('jumlah_realisasi_pertemuan')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>
                </div>

                <div class="modal-footer">
                    <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-primary">
                        <i class="ri-save-line me-1"></i> Simpan
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

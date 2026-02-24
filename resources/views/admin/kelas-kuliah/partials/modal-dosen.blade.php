<div class="modal fade" id="modalDosen" tabindex="-1" aria-labelledby="modalDosenLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered">
        <div class="modal-content">
            @php
                $jenisEvaluasiOptions = \App\Models\DosenPengajarKelasKuliah::JENIS_EVALUASI;
            @endphp

            <div class="modal-header">
                <h5 class="modal-title" id="modalDosenLabel">Tambah Dosen Pengajar</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form action="{{ route('kelas.dosen.store') }}" method="POST">
                @csrf
                <input type="hidden" name="kelas_kuliah_id" value="{{ $kelasKuliah->id }}">

                <div class="modal-body">
                    <div class="row g-3">
                        <div class="col-12">
                            <label for="dosen_id" class="form-label">Dosen Penugasan (Pusat) <span
                                    class="text-danger">*</span></label>
                            <select name="dosen_id" id="dosen_id"
                                class="form-select select2-modal @error('dosen_id') is-invalid @enderror" required>
                                <option value="">Pilih Dosen</option>
                                @foreach($daftarDosen as $d)
                                    <option value="{{ $d->id }}" {{ old('dosen_id') == $d->id ? 'selected' : '' }}>
                                        {{ $d->nidn ? '[' . $d->nidn . '] ' : '' }}{{ $d->nama_admin_display }}
                                    </option>
                                @endforeach
                            </select>
                            <div class="form-text text-primary">
                                <i class="ri-information-line me-1"></i> Dosen yang memiliki penugasan resmi (NIDN/NIDK)
                                untuk dilaporkan ke pusat.
                            </div>
                            @error('dosen_id') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>

                        <div class="col-12">
                            <div class="p-3 border rounded bg-label-secondary">
                                <label for="id_dosen_alias_lokal" class="form-label fw-bold">Dosen Pengajar Asli
                                    (Alias)</label>
                                <select name="id_dosen_alias_lokal" id="id_dosen_alias_lokal"
                                    class="form-select select2-modal @error('id_dosen_alias_lokal') is-invalid @enderror">
                                    <option value="">-- Tetap Gunakan Dosen Penugasan --</option>
                                    @foreach($daftarDosenLokal as $d)
                                        <option value="{{ $d->id }}" {{ old('id_dosen_alias_lokal') == $d->id ? 'selected' : '' }}>
                                            {{ $d->nama_admin_display }}
                                        </option>
                                    @endforeach
                                </select>
                                <div class="form-text mt-2">
                                    <i class="ri-lightbulb-line me-1"></i> Gunakan jika pengajar di lapangan berbeda
                                    dengan dosen resmi. Data ini hanya untuk jadwal & laporan lokal.
                                </div>
                                @error('id_dosen_alias_lokal') <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>

                        <div class="col-md-6">
                            <label for="bobot_sks" class="form-label">Bobot SKS <span
                                    class="text-danger">*</span></label>
                            <input type="number" step="0.01" name="bobot_sks" id="bobot_sks"
                                class="form-control @error('bobot_sks') is-invalid @enderror"
                                value="{{ old('bobot_sks', 0) }}" required>
                            <div class="form-text mt-1 text-muted">
                                Bobot SKS maksimal: <strong>{{ number_format($kelasKuliah->sks_mk, 2) }}</strong>
                            </div>
                            @error('bobot_sks') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>

                        <div class="col-md-6">
                            <label for="jenis_evaluasi" class="form-label">Jenis Evaluasi <span
                                    class="text-danger">*</span></label>
                            <select name="jenis_evaluasi" id="jenis_evaluasi"
                                class="form-select @error('jenis_evaluasi') is-invalid @enderror" required>
                                <option value="">Pilih Jenis</option>
                                @foreach($jenisEvaluasiOptions as $val => $text)
                                    <option value="{{ $val }}" {{ old('jenis_evaluasi') == $val ? 'selected' : '' }}>
                                        {{ $text }}
                                    </option>
                                @endforeach
                            </select>
                            @error('jenis_evaluasi') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>

                        <div class="col-md-6">
                            <label for="jumlah_rencana_pertemuan" class="form-label">Rencana Pertemuan <span
                                    class="text-danger">*</span></label>
                            <input type="number" min="0" name="jumlah_rencana_pertemuan" id="jumlah_rencana_pertemuan"
                                class="form-control @error('jumlah_rencana_pertemuan') is-invalid @enderror"
                                value="{{ old('jumlah_rencana_pertemuan', 0) }}" required>
                            @error('jumlah_rencana_pertemuan') <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="col-md-6">
                            <label for="jumlah_realisasi_pertemuan" class="form-label">Realisasi Pertemuan</label>
                            <input type="number" min="0" name="jumlah_realisasi_pertemuan"
                                id="jumlah_realisasi_pertemuan"
                                class="form-control @error('jumlah_realisasi_pertemuan') is-invalid @enderror"
                                value="{{ old('jumlah_realisasi_pertemuan') }}">
                            @error('jumlah_realisasi_pertemuan') <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-primary">
                        <i class="ri-save-line me-1"></i> Simpan Dosen
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

@push('scripts')
    <script>
        $(function () {
            const modalElement = $('#modalDosen');

            // Initialize Select2 when modal is shown
            modalElement.on('shown.bs.modal', function () {
                $('.select2-modal').select2({
                    dropdownParent: modalElement,
                    width: '100%',
                    placeholder: 'Pilih Dosen'
                });
            });
        });
    </script>
@endpush
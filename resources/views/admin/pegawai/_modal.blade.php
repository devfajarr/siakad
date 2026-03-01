<!-- Modal Form Pegawai -->
<div class="modal fade" id="pegawaiModal" tabindex="-1" aria-labelledby="pegawaiModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="pegawaiModalLabel"><span>Tambah Pegawai Baru</span></h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form id="formPegawai" action="" method="POST">
                @csrf
                <div id="methodField"></div>
                <div class="modal-body">
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label for="pegawaiNip" class="form-label fw-bold">NIP / ID Kepegawaian <span
                                    class="text-danger">*</span></label>
                            <input type="text" id="pegawaiNip" name="nip" class="form-control"
                                placeholder="Contoh: P00123" required>
                        </div>
                        <div class="col-md-6">
                            <label for="pegawaiNamaLengkap" class="form-label fw-bold">Nama Lengkap <span
                                    class="text-danger">*</span></label>
                            <input type="text" id="pegawaiNamaLengkap" name="nama_lengkap" class="form-control"
                                placeholder="Nama dengan gelar" required>
                        </div>
                        <div class="col-md-6">
                            <label for="pegawaiUnitKerja" class="form-label fw-bold">Unit Kerja</label>
                            <input type="text" id="pegawaiUnitKerja" name="unit_kerja" class="form-control"
                                placeholder="Contoh: Administrasi Akademik">
                        </div>
                        <div class="col-md-6">
                            <label for="pegawaiJabatan" class="form-label fw-bold">Jabatan / Posisi</label>
                            <input type="text" id="pegawaiJabatan" name="jabatan" class="form-control"
                                placeholder="Contoh: Staf IT / Satpam">
                        </div>
                        <div class="col-md-6">
                            <label for="pegawaiNoHp" class="form-label fw-bold">No WhatsApp <span
                                    class="text-danger">*</span></label>
                            <input type="text" id="pegawaiNoHp" name="no_hp" class="form-control"
                                placeholder="08123456789" required>
                            <small class="text-muted">Akan diformat otomatis ke standar (62...)</small>
                        </div>
                        <div class="col-md-6">
                            <label for="pegawaiEmail" class="form-label fw-bold">Email Aktif</label>
                            <input type="email" id="pegawaiEmail" name="email" class="form-control"
                                placeholder="email@domain.com">
                        </div>
                        <div class="col-md-6">
                            <label for="pegawaiIsActive" class="form-label fw-bold">Status Kepegawaian <span
                                    class="text-danger">*</span></label>
                            <select id="pegawaiIsActive" name="is_active" class="form-select" required>
                                <option value="1">Aktif</option>
                                <option value="0">Tidak Aktif</option>
                            </select>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-primary d-flex align-items-center gap-1">
                        <i class="ri-save-line"></i> Simpan Data
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
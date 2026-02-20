<div class="modal fade" id="modalTambahMatkul" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="modalCenterTitle">Matakuliah untuk Kurikulum</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form action="{{ route('admin.kurikulum.matkul.store', $kurikulum->id) }}" method="POST">
                @csrf
                <div class="modal-body">
                    <div class="row">
                        <div class="col mb-3">
                            <label for="id_matkul" class="form-label">Matakuliah <span
                                    class="text-danger">*</span></label>
                            <select id="id_matkul" name="id_matkul" class="form-select select2" required>
                                <option value="">-- Pilih Matakuliah --</option>
                                @foreach($mataKuliahs as $mk)
                                    <option value="{{ $mk->id_matkul }}">
                                        {{ $mk->kode_mk }} - {{ $mk->nama_mk }} ({{ $mk->sks }} SKS)
                                    </option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col mb-3">
                            <label for="semester" class="form-label">Semester <span class="text-danger">*</span></label>
                            <input type="number" id="semester" name="semester" class="form-control"
                                placeholder="Semester (Ex: 1)" required min="1" max="14">
                        </div>
                    </div>
                    <div class="row">
                        <div class="col mb-0">
                            <div class="form-check mt-3">
                                <input class="form-check-input" type="checkbox" value="1" id="apakah_wajib"
                                    name="apakah_wajib" checked>
                                <label class="form-check-label" for="apakah_wajib"> Wajib </label>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Tutup</button>
                    <button type="submit" class="btn btn-primary">Simpan Mata Kuliah Kurikulum</button>
                </div>
            </form>
        </div>
    </div>
</div>
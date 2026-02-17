{{-- Modal Form Input Riwayat Pendidikan Mahasiswa --}}
{{-- Field names match InsertRiwayatPendidikanMahasiswa GetDictionary schema --}}
<div class="modal fade" id="modalRiwayatPendidikan" tabindex="-1" aria-labelledby="modalRiwayatPendidikanLabel"
    aria-hidden="true" data-bs-backdrop="static" data-bs-keyboard="false">
    <div class="modal-dialog modal-lg modal-dialog-centered modal-dialog-scrollable">
        <div class="modal-content">
            {{-- Header --}}
            <div class="modal-header">
                <h5 class="modal-title" id="modalRiwayatPendidikanLabel">
                    <i class="ri-history-line me-2"></i><span id="modalRiwayatTitle">Tambah Histori Pendidikan</span>
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>

            {{-- Body --}}
            <div class="modal-body">
                {{-- Alert Catatan --}}
                <div class="alert alert-warning d-flex align-items-start mb-4" role="alert">
                    <i class="ri-information-line me-2 mt-1"></i>
                    <div>
                        <strong>Catatan :</strong><br>
                        Mulai tahun ajar 2022 / 2023 Genap untuk pendataan mahasiswa dengan Jenis Pendaftaran
                        Rekognisi Pembelajaran Lampau (RPL), Program Studi yang dipilih harus terdata terlebih
                        dahulu di aplikasi SIERRA
                        (<a href="https://sierra.kemdiktisaintek.go.id/" target="_blank"
                            rel="noopener noreferrer">https://sierra.kemdiktisaintek.go.id/</a>).
                    </div>
                </div>

                {{-- Form --}}
                <form id="formRiwayatPendidikan" method="POST"
                    action="{{ route('admin.riwayat-pendidikan.store') }}">
                    @csrf
                    <input type="hidden" name="_method" id="formRiwayatMethod" value="POST">
                    <input type="hidden" name="id_mahasiswa" value="{{ $mahasiswa->id }}">

                    <div class="row g-3">
                        {{-- NIM --}}
                        <div class="col-md-6">
                            <label for="rp_nim" class="form-label">NIM <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" id="rp_nim" name="nim"
                                placeholder="NIM" value="{{ old('nim', $mahasiswa->nim ?? '') }}">
                        </div>

                        {{-- Jenis Pendaftaran (id_jenis_daftar) --}}
                        <div class="col-md-6">
                            <label for="rp_id_jenis_daftar" class="form-label">Jenis Pendaftaran <span
                                    class="text-danger">*</span></label>
                            <select class="form-select" id="rp_id_jenis_daftar" name="id_jenis_daftar">
                                <option value="" disabled {{ old('id_jenis_daftar') ? '' : 'selected' }}>Pilih Jenis Pendaftaran</option>
                                @isset($jenisPendaftaran)
                                    @foreach($jenisPendaftaran as $jenis)
                                        <option value="{{ $jenis->id_jenis_daftar }}"
                                            {{ old('id_jenis_daftar') == $jenis->id_jenis_daftar ? 'selected' : '' }}>
                                            {{ $jenis->nama_jenis_daftar }}
                                        </option>
                                    @endforeach
                                @endisset
                            </select>
                        </div>

                        {{-- Jalur Pendaftaran (id_jalur_daftar) --}}
                        <div class="col-md-6">
                            <label for="rp_id_jalur_daftar" class="form-label">Jalur Pendaftaran</label>
                            <select class="form-select" id="rp_id_jalur_daftar" name="id_jalur_daftar">
                                <option value="" {{ old('id_jalur_daftar') ? '' : 'selected' }}>Pilih Jalur Pendaftaran</option>
                                @isset($jalurPendaftaran)
                                    @foreach($jalurPendaftaran as $jalur)
                                        <option value="{{ $jalur->id_jalur_daftar }}"
                                            {{ old('id_jalur_daftar') == $jalur->id_jalur_daftar ? 'selected' : '' }}>
                                            {{ $jalur->nama_jalur_daftar }}
                                        </option>
                                    @endforeach
                                @endisset
                            </select>
                        </div>

                        {{-- Periode Pendaftaran (id_periode_masuk) — only a_periode_aktif = 1 --}}
                        <div class="col-md-6">
                            <label for="rp_id_periode_masuk" class="form-label">Periode Pendaftaran <span
                                    class="text-danger">*</span></label>
                            <select class="form-select" id="rp_id_periode_masuk" name="id_periode_masuk">
                                <option value="" disabled {{ old('id_periode_masuk') ? '' : 'selected' }}>Pilih Periode</option>
                                @isset($semesters)
                                    @foreach($semesters as $semester)
                                        <option value="{{ $semester->id_semester }}"
                                            {{ old('id_periode_masuk') == $semester->id_semester ? 'selected' : '' }}>
                                            {{ $semester->nama_semester }}
                                        </option>
                                    @endforeach
                                @endisset
                            </select>
                            <small class="form-text text-muted">Hanya menampilkan periode aktif.</small>
                        </div>

                        {{-- Tanggal Masuk (tanggal_daftar) --}}
                        <div class="col-md-6">
                            <label for="rp_tanggal_daftar" class="form-label">Tanggal Masuk <span
                                    class="text-danger">*</span></label>
                            <div class="input-group input-group-merge">
                                <input type="text" class="form-control flatpickr-input" id="rp_tanggal_daftar"
                                    name="tanggal_daftar" placeholder="Tanggal Masuk"
                                    value="{{ old('tanggal_daftar') }}" readonly="readonly">
                                <span class="input-group-text cursor-pointer" id="rp_tanggal_daftar_icon">
                                    <i class="ri-calendar-line"></i>
                                </span>
                            </div>
                        </div>

                        {{-- Pembiayaan Awal (id_pembiayaan) --}}
                        <div class="col-md-6">
                            <label for="rp_id_pembiayaan" class="form-label">Pembiayaan Awal</label>
                            <select class="form-select" id="rp_id_pembiayaan" name="id_pembiayaan">
                                <option value="" {{ old('id_pembiayaan') ? '' : 'selected' }}>Pilih Pembiayaan</option>
                                @isset($pembiayaans)
                                    @foreach($pembiayaans as $biaya)
                                        <option value="{{ $biaya->id_pembiayaan }}"
                                            {{ old('id_pembiayaan') == $biaya->id_pembiayaan ? 'selected' : '' }}>
                                            {{ $biaya->nama_pembiayaan }}
                                        </option>
                                    @endforeach
                                @endisset
                            </select>
                        </div>

                        {{-- Biaya Masuk (biaya_masuk) --}}
                        <div class="col-md-6">
                            <label for="rp_biaya_masuk" class="form-label">Biaya Masuk</label>
                            <input type="number" class="form-control" id="rp_biaya_masuk" name="biaya_masuk"
                                placeholder="Biaya Masuk" min="0" step="0.01"
                                value="{{ old('biaya_masuk') }}">
                        </div>

                        {{-- Perguruan Tinggi (id_perguruan_tinggi) --}}
                        <div class="col-md-6">
                            <label for="rp_perguruan_tinggi" class="form-label">Perguruan Tinggi</label>
                            <input type="text" class="form-control" id="rp_perguruan_tinggi"
                                placeholder="Perguruan Tinggi" readonly
                                value="{{ isset($profilPT) ? $profilPT->nama_perguruan_tinggi : '-' }}">
                            <input type="hidden" name="id_perguruan_tinggi" id="rp_id_perguruan_tinggi"
                                value="{{ old('id_perguruan_tinggi', isset($profilPT) ? $profilPT->id_perguruan_tinggi : '') }}">
                        </div>

                        {{-- Fakultas / Program Studi (id_prodi) --}}
                        <div class="col-md-6">
                            <label for="rp_id_prodi" class="form-label">Fakultas / Program Studi</label>
                            <select class="form-select" id="rp_id_prodi" name="id_prodi">
                                <option value="" {{ old('id_prodi') ? '' : 'selected' }}>Pilih Program Studi</option>
                                @isset($programStudis)
                                    @foreach($programStudis as $prodi)
                                        <option value="{{ $prodi->id_prodi }}"
                                            {{ old('id_prodi') == $prodi->id_prodi ? 'selected' : '' }}>
                                            {{ $prodi->nama_jenjang_pendidikan }} - {{ $prodi->nama_program_studi }}
                                        </option>
                                    @endforeach
                                @endisset
                            </select>
                        </div>

                        {{-- Peminatan (id_bidang_minat) --}}
                        <div class="col-md-6">
                            <label for="rp_id_bidang_minat" class="form-label">Peminatan</label>
                            <select class="form-select" id="rp_id_bidang_minat" name="id_bidang_minat">
                                <option value="" {{ old('id_bidang_minat') ? '' : 'selected' }}>Pilih Peminatan</option>
                            </select>
                        </div>

                        {{-- ============================================================= --}}
                        {{-- Conditional: Prodi Asal & PT Asal                             --}}
                        {{-- Tampil jika id_jenis_daftar ≠ 1 (bukan Peserta Didik Baru)    --}}
                        {{-- Field names: id_perguruan_tinggi_asal, id_prodi_asal           --}}
                        {{-- ============================================================= --}}
                        <div id="asalWrapper"
                            class="col-12 {{ old('id_jenis_daftar') && old('id_jenis_daftar') != '1' ? '' : 'd-none' }}"
                            style="transition: all 0.3s ease;">

                            <hr class="my-2">
                            <p class="text-muted small mb-3">
                                <i class="ri-information-line me-1"></i>
                                Field berikut wajib diisi untuk jenis pendaftaran selain <strong>Peserta Didik Baru</strong>.
                            </p>

                            <div class="row g-3">
                                {{-- Perguruan Tinggi Asal (id_perguruan_tinggi_asal) --}}
                                <div class="col-md-6">
                                    <label for="rp_id_perguruan_tinggi_asal" class="form-label">
                                        Perguruan Tinggi Asal <span class="text-danger">*</span>
                                    </label>
                                    <select class="form-select" id="rp_id_perguruan_tinggi_asal"
                                        name="id_perguruan_tinggi_asal">
                                        <option value="" {{ old('id_perguruan_tinggi_asal') ? '' : 'selected' }}>
                                            -- Pilih Perguruan Tinggi Asal --
                                        </option>
                                        @isset($perguruanTinggiList)
                                            @foreach($perguruanTinggiList as $pt)
                                                <option value="{{ $pt->id_perguruan_tinggi }}"
                                                    {{ old('id_perguruan_tinggi_asal') == $pt->id_perguruan_tinggi ? 'selected' : '' }}>
                                                    {{ $pt->kode_perguruan_tinggi }} - {{ $pt->nama_perguruan_tinggi }}
                                                </option>
                                            @endforeach
                                        @endisset
                                    </select>
                                </div>

                                {{-- Program Studi Asal (id_prodi_asal) --}}
                                <div class="col-md-6">
                                    <label for="rp_id_prodi_asal" class="form-label">
                                        Program Studi Asal <span class="text-danger">*</span>
                                    </label>
                                    <select class="form-select" id="rp_id_prodi_asal" name="id_prodi_asal" disabled>
                                        <option value="" selected>-- Pilih Perguruan Tinggi Terlebih Dahulu --</option>
                                    </select>
                                </div>
                            </div>
                        </div>
                    </div>
                </form>
            </div>

            {{-- Footer --}}
            <div class="modal-footer">
                <button type="button" class="btn btn-outline-danger" data-bs-dismiss="modal">
                    <i class="ri-close-line me-1"></i> Tutup
                </button>
                <button type="submit" class="btn btn-primary" form="formRiwayatPendidikan" id="btnSimpanRiwayat">
                    <i class="ri-save-line me-1"></i> Simpan
                </button>
            </div>
        </div>
    </div>
</div>

@push('partial_scripts')
<script>
    document.addEventListener('DOMContentLoaded', function () {
        var modalEl = document.getElementById('modalRiwayatPendidikan');
        var form = document.getElementById('formRiwayatPendidikan');
        var methodInput = document.getElementById('formRiwayatMethod');
        var titleSpan = document.getElementById('modalRiwayatTitle');
        var storeUrl = "{{ route('admin.riwayat-pendidikan.store') }}";

        /** ID Jenis Pendaftaran: Peserta Didik Baru */
        var ID_PESERTA_DIDIK_BARU = '1';

        var jenisDaftarSelect = document.getElementById('rp_id_jenis_daftar');
        var asalWrapper = document.getElementById('asalWrapper');
        var ptAsalSelect = document.getElementById('rp_id_perguruan_tinggi_asal');
        var prodiAsalSelect = document.getElementById('rp_id_prodi_asal');

        var fpTanggalMasuk = null;

        // --- Toggle conditional fields (Prodi Asal & PT Asal) ---
        function toggleAsalFields() {
            var selectedValue = jenisDaftarSelect.value;
            if (selectedValue && selectedValue !== ID_PESERTA_DIDIK_BARU) {
                asalWrapper.classList.remove('d-none');
                asalWrapper.style.opacity = '1';
            } else {
                asalWrapper.classList.add('d-none');
                asalWrapper.style.opacity = '0';
                // Reset fields when hidden
                ptAsalSelect.value = '';
                prodiAsalSelect.innerHTML = '<option value="" selected>-- Pilih Perguruan Tinggi Terlebih Dahulu --</option>';
                prodiAsalSelect.disabled = true;
            }
        }

        jenisDaftarSelect.addEventListener('change', toggleAsalFields);
        toggleAsalFields();

        // --- AJAX: Load Prodi Asal on PT Asal Change ---
        function loadProdiAsal(ptId, selectedProdiId = null) {
            if (!ptId) {
                prodiAsalSelect.innerHTML = '<option value="" selected>-- Pilih Perguruan Tinggi Terlebih Dahulu --</option>';
                prodiAsalSelect.disabled = true;
                return;
            }

            prodiAsalSelect.innerHTML = '<option value="" selected>Loading...</option>';
            prodiAsalSelect.disabled = true;

            fetch("{{ url('api/prodi-by-pt') }}/" + ptId)
                .then(response => response.json())
                .then(json => {
                    if (json.success) {
                        var options = '<option value="">-- Pilih Program Studi Asal --</option>';
                        json.data.forEach(function (prodi) {
                            var isSelected = (selectedProdiId && selectedProdiId == prodi.id_prodi) ? 'selected' : '';
                            options += `<option value="${prodi.id_prodi}" ${isSelected}>
                                ${prodi.kode_program_studi ? prodi.kode_program_studi + ' - ' : ''} ${prodi.nama_jenjang_pendidikan} - ${prodi.nama_program_studi}
                            </option>`;
                        });
                        prodiAsalSelect.innerHTML = options;
                        prodiAsalSelect.disabled = false;
                    } else {
                        prodiAsalSelect.innerHTML = '<option value="" selected>Gagal memuat data</option>';
                    }
                })
                .catch(err => {
                    console.error('Error fetching prodi:', err);
                    prodiAsalSelect.innerHTML = '<option value="" selected>Error memuat data</option>';
                });
        }

        ptAsalSelect.addEventListener('change', function () {
            loadProdiAsal(this.value);
        });

        // --- TAMBAH mode ---
        document.getElementById('btnTambahRiwayat').addEventListener('click', function () {
            form.reset();
            form.action = storeUrl;
            methodInput.value = 'POST';
            titleSpan.textContent = 'Tambah Histori Pendidikan';
            document.getElementById('rp_nim').value = "{{ $mahasiswa->nim ?? '' }}";
            @isset($profilPT)
            document.getElementById('rp_id_perguruan_tinggi').value = "{{ $profilPT->id_perguruan_tinggi }}";
            @endisset
            toggleAsalFields();
            
            // Handle OLD values if validation failed
            var oldPtAsal = "{{ old('id_perguruan_tinggi_asal') }}";
            var oldProdiAsal = "{{ old('id_prodi_asal') }}";
            
            if (oldPtAsal) {
                // Manually trigger change to load prodi
                loadProdiAsal(oldPtAsal, oldProdiAsal);
            }
        });

        // --- EDIT mode ---
        document.querySelectorAll('.btn-edit-riwayat').forEach(function (btn) {
            btn.addEventListener('click', function () {
                var editUrl = this.dataset.url;
                var id = this.dataset.id;

                fetch(editUrl, {
                    headers: { 'X-Requested-With': 'XMLHttpRequest' }
                })
                .then(response => response.json())
                .then(json => {
                    if (json.success) {
                        var data = json.data;

                        form.action = "{{ url('admin/riwayat-pendidikan') }}/" + id;
                        methodInput.value = 'PUT';
                        titleSpan.textContent = 'Edit Histori Pendidikan';

                        document.getElementById('rp_nim').value = data.nim || '';
                        jenisDaftarSelect.value = data.id_jenis_daftar || '';
                        document.getElementById('rp_id_jalur_daftar').value = data.id_jalur_daftar || '';
                        document.getElementById('rp_id_periode_masuk').value = data.id_periode_masuk || '';
                        document.getElementById('rp_biaya_masuk').value = data.biaya_masuk || '';
                        document.getElementById('rp_id_perguruan_tinggi').value = data.id_perguruan_tinggi || '';
                        document.getElementById('rp_id_prodi').value = data.id_prodi || '';
                        document.getElementById('rp_id_bidang_minat').value = data.id_bidang_minat || '';
                        document.getElementById('rp_id_pembiayaan').value = data.id_pembiayaan || '';
                        
                        // Set PT Asal
                        if (data.id_perguruan_tinggi_asal) {
                            ptAsalSelect.value = data.id_perguruan_tinggi_asal;
                            // Trigger AJAX load for Prodi Asal
                            loadProdiAsal(data.id_perguruan_tinggi_asal, data.id_prodi_asal);
                        } else {
                            ptAsalSelect.value = '';
                            prodiAsalSelect.innerHTML = '<option value="" selected>-- Pilih Perguruan Tinggi Terlebih Dahulu --</option>';
                            prodiAsalSelect.disabled = true;
                        }

                        if (fpTanggalMasuk && data.tanggal_daftar) {
                            fpTanggalMasuk.setDate(data.tanggal_daftar, true);
                        }

                        toggleAsalFields();
                        new bootstrap.Modal(modalEl).show();
                    }
                })
                .catch(err => {
                    console.error('Error:', err);
                    alert('Gagal memuat data.');
                });
            });
        });

        // --- Modal shown event ---
        modalEl.addEventListener('shown.bs.modal', function () {
            if (!fpTanggalMasuk) {
                fpTanggalMasuk = flatpickr('#rp_tanggal_daftar', {
                    dateFormat: 'Y-m-d',
                    monthSelectorType: 'static',
                    allowInput: false
                });
            }
            document.getElementById('rp_tanggal_daftar_icon').addEventListener('click', () => fpTanggalMasuk.open());
            
            // Auto focus
            if (!jenisDaftarSelect.value) jenisDaftarSelect.focus();
        });

        // --- Auto open modal on validation error ---
        @if($errors->any())
            var oldPtAsal = "{{ old('id_perguruan_tinggi_asal') }}";
            var oldProdiAsal = "{{ old('id_prodi_asal') }}";
            if (oldPtAsal) {
                loadProdiAsal(oldPtAsal, oldProdiAsal);
            }
            new bootstrap.Modal(modalEl).show();
        @endif
    });
</script>
@endpush
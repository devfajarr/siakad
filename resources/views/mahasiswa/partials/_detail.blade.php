    <form action="{{ route('admin.mahasiswa.update', $mahasiswa->id) }}" method="POST">
        @csrf
        @method('PUT')

        <!-- Card 1: Data Utama -->
        <div class="card shadow-sm mb-4">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h5 class="card-title mb-0">Data Mahasiswa</h5>
                <div class="d-flex gap-2">
                </div>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-6">
                        <x-forms.input name="nama_mahasiswa" label="Nama Lengkap" required="true"
                            :value="$mahasiswa->nama_mahasiswa" />
                    </div>

                    <div class="col-md-6">
                        <x-forms.input name="tempat_lahir" label="Tempat Lahir" required="true"
                            :value="$mahasiswa->tempat_lahir" />
                    </div>
                    <div class="col-md-6">
                        <x-forms.datepicker name="tanggal_lahir" label="Tanggal Lahir" required="true"
                            :value="$mahasiswa->tanggal_lahir" />
                    </div>
                    <div class="col-md-6">
                        <x-forms.radio-group name="jenis_kelamin" label="Jenis Kelamin" :options="['L' => 'Laki-laki', 'P' => 'Perempuan']" required="true" :value="$mahasiswa->jenis_kelamin" />
                    </div>
                    <div class="col-md-6">
                        <x-forms.select name="id_agama" label="Agama" :options="$agamas" required="true"
                            :value="$mahasiswa->id_agama" />
                    </div>
                    <div class="col-md-6">
                        <x-forms.input name="nama_ibu_kandung" label="Nama Ibu Kandung" required="true"
                            :value="$mahasiswa->nama_ibu_kandung" />
                    </div>
                </div>
            </div>
        </div>

        <!-- Card 2: Informasi Detail (Tabs) -->
        <div class="card shadow-sm mb-4">
            <div class="card-header">
                <h5 class="mb-4">Informasi Detail Mahasiswa</h5>
                <ul class="nav nav-pills mb-3" id="pills-tab" role="tablist">
                    <li class="nav-item" role="presentation">
                        <button class="nav-link active" id="pills-alamat-tab" data-bs-toggle="pill"
                            data-bs-target="#pills-alamat" type="button" role="tab" aria-controls="pills-alamat"
                            aria-selected="true">ALAMAT</button>
                    </li>
                    <li class="nav-item" role="presentation">
                        <button class="nav-link" id="pills-orangtua-tab" data-bs-toggle="pill"
                            data-bs-target="#pills-orangtua" type="button" role="tab" aria-controls="pills-orangtua"
                            aria-selected="false">ORANG TUA</button>
                    </li>
                    <li class="nav-item" role="presentation">
                        <button class="nav-link" id="pills-wali-tab" data-bs-toggle="pill" data-bs-target="#pills-wali"
                            type="button" role="tab" aria-controls="pills-wali" aria-selected="false">WALI</button>
                    </li>
                    <li class="nav-item" role="presentation">
                        <button class="nav-link" id="pills-kebutuhan-tab" data-bs-toggle="pill"
                            data-bs-target="#pills-kebutuhan" type="button" role="tab" aria-controls="pills-kebutuhan"
                            aria-selected="false" disabled>KEBUTUHAN KHUSUS</button>
                    </li>
                </ul>
            </div>
            <div class="card-body">
                <div class="tab-content pt-0" id="pills-tabContent">

                    <!-- Tab Alamat -->
                    <div class="tab-pane fade show active" id="pills-alamat" role="tabpanel"
                        aria-labelledby="pills-alamat-tab">
                        <div class="row">
                            <div class="col-md-6">
                                <x-forms.select name="kewarganegaraan" label="Kewarganegaraan"
                                    :options="$negaras" required="true"
                                    :value="$mahasiswa->kewarganegaraan" />
                            </div>
                            <div class="col-md-6">
                                <x-forms.input name="nik" label="NIK" required="true" type="number"
                                    :value="$mahasiswa->nik" />
                            </div>
                            <div class="col-md-6">
                                <x-forms.input name="nisn" label="NISN" required="true" type="number"
                                    :value="$mahasiswa->nisn" />
                            </div>
                            <div class="col-md-6">
                                <x-forms.input name="npwp" label="NPWP" :value="$mahasiswa->npwp" />
                            </div>
                            <div class="col-md-12">
                                <x-forms.input name="jalan" label="Jalan" placeholder="Nama Jalan"
                                    :value="$mahasiswa->jalan" />
                            </div>
                            <div class="col-md-12">
                                <x-forms.input name="handphone" label="Telephone/HP" required="true" type="tel"
                                    :value="$mahasiswa->handphone" helper="Gunakan format : 0811..." />
                            </div>
                            <div class="col-md-6">
                                <x-forms.input name="dusun" label="Dusun" :value="$mahasiswa->dusun" />
                            </div>
                            <div class="col-md-3">
                                <x-forms.input name="rt" label="RT" type="number" :value="$mahasiswa->rt" />
                            </div>
                            <div class="col-md-3">
                                <x-forms.input name="rw" label="RW" type="number" :value="$mahasiswa->rw" />
                            </div>
                            <div class="col-md-6">
                                <x-forms.input name="kelurahan" label="Kelurahan" required="true"
                                    :value="$mahasiswa->kelurahan" />
                            </div>
                            <div class="col-md-6">
                                <x-forms.input name="kode_pos" label="Kode Pos" type="number"
                                    :value="$mahasiswa->kode_pos" />
                            </div>
                            <div class="col-md-12">
                                <x-forms.input name="email" label="Email" type="email" required="true"
                                    :value="$mahasiswa->email" />
                            </div>

                            <div class="col-md-12 mt-3">
                                <x-forms.select name="penerima_kps" label="Penerima KPS" :options="[0 => 'Tidak', 1 => 'Ya']" :value="$mahasiswa->penerima_kps" />
                            </div>

                            {{-- Wilayah selects: data resolved by the Controller via resolveHierarchy() --}}
                            <div class="col-md-4 mt-3">
                                <x-forms.select name="provinsi_id" label="Provinsi" :options="$provinsis"
                                    :value="$wilayahData['provinsi'] ? trim($wilayahData['provinsi']->id_wilayah) : ''" />
                            </div>
                            <div class="col-md-4 mt-3">
                                <x-forms.select name="kabupaten_id" label="Kabupaten" :options="$kabupatenOptions"
                                    :value="$wilayahData['kabupaten'] ? trim($wilayahData['kabupaten']->id_wilayah) : ''" />
                            </div>
                            <div class="col-md-4 mt-3">
                                <x-forms.select name="id_wilayah" label="Kecamatan" :options="$kecamatanOptions"
                                    required="true"
                                    :value="$wilayahData['kecamatan'] ? trim($wilayahData['kecamatan']->id_wilayah) : ''" />
                            </div>

                            <div class="col-md-6 mt-3">
                                <x-forms.select name="id_jenis_tinggal" label="Jenis Tinggal" :options="$jenisTinggals"
                                    :value="$mahasiswa->id_jenis_tinggal" />
                            </div>
                            <div class="col-md-6 mt-3">
                                <x-forms.select name="id_alat_transportasi" label="Alat Transportasi"
                                    :options="$alatTransportasis" :value="$mahasiswa->id_alat_transportasi" />
                            </div>
                        </div>
                    </div>

                    <!-- Tab Orang Tua -->
                    <div class="tab-pane fade" id="pills-orangtua" role="tabpanel" aria-labelledby="pills-orangtua-tab">
                        <div class="row">
                            <!-- Ayah -->
                            <div class="col-12 mb-3">
                                <h6 class="text-primary fw-bold">Data Ayah</h6>
                                <hr class="mt-1">
                            </div>
                            <div class="col-md-6">
                                <x-forms.input name="nama_ayah" label="Nama Ayah" :value="$mahasiswa->nama_ayah" />
                            </div>
                            <div class="col-md-6">
                                <x-forms.input name="nik_ayah" label="NIK Ayah" type="number"
                                    :value="$mahasiswa->nik_ayah" />
                            </div>
                            <div class="col-md-6">
                                <x-forms.datepicker name="tgl_lahir_ayah" label="Tanggal Lahir Ayah"
                                    :value="$mahasiswa->tgl_lahir_ayah" />
                            </div>
                            <div class="col-md-6">
                                <x-forms.select name="id_pendidikan_ayah" label="Pendidikan Ayah"
                                    :options="$jenjangPendidikans" :value="$mahasiswa->id_pendidikan_ayah" />
                            </div>
                            <div class="col-md-6">
                                <x-forms.select name="id_pekerjaan_ayah" label="Pekerjaan Ayah" :options="$pekerjaans"
                                    :value="$mahasiswa->id_pekerjaan_ayah" />
                            </div>
                            <div class="col-md-6">
                                <x-forms.select name="id_penghasilan_ayah" label="Penghasilan Ayah"
                                    :options="$penghasilans" :value="$mahasiswa->id_penghasilan_ayah" />
                            </div>

                            <!-- Ibu -->
                            <div class="col-12 mt-4 mb-3">
                                <h6 class="text-danger fw-bold">Data Ibu</h6>
                                <hr class="mt-1">
                            </div>
                            <div class="col-md-6">
                                <x-forms.input name="nik_ibu" label="NIK Ibu" type="number"
                                    :value="$mahasiswa->nik_ibu" />
                            </div>
                            <div class="col-md-6">
                                <x-forms.datepicker name="tgl_lahir_ibu" label="Tanggal Lahir Ibu"
                                    :value="$mahasiswa->tgl_lahir_ibu" />
                            </div>
                            <div class="col-md-6">
                                <x-forms.select name="id_pendidikan_ibu" label="Pendidikan Ibu"
                                    :options="$jenjangPendidikans" :value="$mahasiswa->id_pendidikan_ibu" />
                            </div>
                            <div class="col-md-6">
                                <x-forms.select name="id_pekerjaan_ibu" label="Pekerjaan Ibu" :options="$pekerjaans"
                                    :value="$mahasiswa->id_pekerjaan_ibu" />
                            </div>
                            <div class="col-md-6">
                                <x-forms.select name="id_penghasilan_ibu" label="Penghasilan Ibu"
                                    :options="$penghasilans" :value="$mahasiswa->id_penghasilan_ibu" />
                            </div>
                        </div>
                    </div>

                    <!-- Tab Wali -->
                    <div class="tab-pane fade" id="pills-wali" role="tabpanel" aria-labelledby="pills-wali-tab">
                        <div class="row">
                            <div class="col-md-6">
                                <x-forms.input name="nama_wali" label="Nama Wali" :value="$mahasiswa->nama_wali" />
                            </div>
                            <div class="col-md-6">
                                <x-forms.datepicker name="tgl_lahir_wali" label="Tanggal Lahir Wali"
                                    :value="$mahasiswa->tgl_lahir_wali" />
                            </div>
                            <div class="col-md-6">
                                <x-forms.select name="id_pendidikan_wali" label="Pendidikan Wali"
                                    :options="$jenjangPendidikans" :value="$mahasiswa->id_pendidikan_wali" />
                            </div>
                            <div class="col-md-6">
                                <x-forms.select name="id_pekerjaan_wali" label="Pekerjaan Wali" :options="$pekerjaans"
                                    :value="$mahasiswa->id_pekerjaan_wali" />
                            </div>
                            <div class="col-md-6">
                                <x-forms.select name="id_penghasilan_wali" label="Penghasilan Wali"
                                    :options="$penghasilans" :value="$mahasiswa->id_penghasilan_wali" />
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Actions -->
            <div class="card-footer d-flex justify-content-end gap-2">
                <button type="submit" class="btn btn-warning"><i class="ri-edit-box-line me-1"></i> UBAH</button>
                <button type="button" class="btn btn-danger"><i class="ri-delete-bin-line me-1"></i> HAPUS</button>
                <a href="{{ route('admin.mahasiswa.index') }}" class="btn btn-success"><i class="ri-file-list-3-line me-1"></i> DAFTAR</a>
            </div>
        </div>

        <div class="alert alert-info mt-3">
            <strong>Keterangan :</strong>
            <ul class="mb-0 ps-3">
                <li>Fitur ini digunakan untuk menampilkan dan mengelola data mahasiswa, baik itu mahasiswa baru maupun
                    perubahan data mahasiswa</li>
                <li>Perubahan Nama Mahasiswa, Tanggal Lahir dan Nama Ibu Kandung hanya dapat di lakukan pada laman
                    https://pddikti.kemdikbud.go.id</li>
            </ul>
        </div>
    </form>

@push('partial_scripts')
    <script>
        $(function () {
            // Cascading Wilayah (native selects, no Select2)
            const provinsiSelect = $('select[name="provinsi_id"]');
            const kabupatenSelect = $('select[name="kabupaten_id"]');
            const kecamatanSelect = $('select[name="id_wilayah"]');

            // On Provinsi Change
            provinsiSelect.on('change', function () {
                const provinsiId = $(this).val();
                // Reset & Disable Child Selects
                kabupatenSelect.empty().append('<option value="" disabled selected>Pilih Kabupaten</option>').attr('disabled', true);
                kecamatanSelect.empty().append('<option value="" disabled selected>Pilih Kecamatan</option>').attr('disabled', true);

                if (provinsiId) {
                    $.ajax({
                        url: "{{ route('admin.wilayah.kabupaten', ':id') }}".replace(':id', provinsiId),
                        type: 'GET',
                        success: function (data) {
                            kabupatenSelect.removeAttr('disabled');
                            $.each(data, function (key, item) {
                                kabupatenSelect.append(new Option(item.text, item.id));
                            });
                        }
                    });
                }
            });

            // On Kabupaten Change
            kabupatenSelect.on('change', function () {
                const kabupatenId = $(this).val();
                // Reset & Disable Child Selects
                kecamatanSelect.empty().append('<option value="" disabled selected>Pilih Kecamatan</option>').attr('disabled', true);

                if (kabupatenId) {
                    $.ajax({
                        url: "{{ route('admin.wilayah.kecamatan', ':id') }}".replace(':id', kabupatenId),
                        type: 'GET',
                        success: function (data) {
                            kecamatanSelect.removeAttr('disabled');
                            $.each(data, function (key, item) {
                                kecamatanSelect.append(new Option(item.text, item.id));
                            });
                        }
                    });
                }
            });
        });
    </script>
@endpush
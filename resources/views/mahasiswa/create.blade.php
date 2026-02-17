@extends('layouts.app')

@section('title', 'Tambah Data Mahasiswa')

@section('content')
    <h4 class="mb-4">Tambah Data Mahasiswa</h4>

    <form action="{{ route('admin.mahasiswa.store') }}" method="POST">
        @csrf

        <div class="row">
            <!-- Card 1: Data Utama -->
            <div class="col-12 mb-4">
                <div class="card shadow-sm">
                    <div class="card-header d-flex justify-content-between align-items-center">
                        <h5 class="card-title mb-0">Data Utama Mahasiswa</h5>
                        <button type="button" id="fillRandom" class="btn btn-outline-secondary btn-sm">
                            <i class="ri-shuffle-line me-1"></i> Isi Data Random
                        </button>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-6">
                                <x-forms.input name="nama_mahasiswa" label="Nama Lengkap" required="true"
                                    placeholder="Sesuai Ijazah" />
                            </div>

                            <div class="col-md-6">
                                <x-forms.input name="tempat_lahir" label="Tempat Lahir" required="true" />
                            </div>
                            <div class="col-md-6">
                                <x-forms.datepicker name="tanggal_lahir" label="Tanggal Lahir" required="true" />
                            </div>
                            <div class="col-md-6">
                                <x-forms.radio-group name="jenis_kelamin" label="Jenis Kelamin" :options="['L' => 'Laki-laki', 'P' => 'Perempuan']" required="true" />
                            </div>
                            <div class="col-md-6">
                                <x-forms.select name="id_agama" label="Agama" :options="$agamas" required="true" />
                            </div>
                            <div class="col-md-6">
                                <x-forms.input name="nik" label="NIK" required="true" type="number"
                                    helper="16 digit angka sesuai KTP" />
                            </div>
                            <div class="col-md-6">
                                <x-forms.input name="nisn" label="NISN" required="true" type="number"
                                    helper="10 digit angka" />
                            </div>
                            <div class="col-md-6">
                                <x-forms.input name="email" label="Email" type="email" required="true" />
                            </div>
                            <div class="col-md-6">
                                <x-forms.input name="handphone" label="No. HP" required="true" type="tel" />
                            </div>
                            <div class="col-md-6">
                                <x-forms.input name="nama_ibu_kandung" label="Nama Ibu Kandung" required="true" />
                            </div>
                            <div class="col-md-6">
                                <x-forms.select name="kewarganegaraan" label="Kewarganegaraan" :options="$negaras"
                                    required="true" placeholder="Pilih Negara" />
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Card 2: Informasi Detail (Tabs) -->
            <div class="col-12 mb-4">
                <div class="card shadow-sm">
                    <div class="card-header">
                        <ul class="nav nav-tabs card-header-tabs" id="myTab" role="tablist">
                            <li class="nav-item" role="presentation">
                                <button class="nav-link active" id="alamat-tab" data-bs-toggle="tab"
                                    data-bs-target="#alamat" type="button" role="tab" aria-controls="alamat"
                                    aria-selected="true">Alamat</button>
                            </li>
                            <li class="nav-item" role="presentation">
                                <button class="nav-link" id="orangtua-tab" data-bs-toggle="tab" data-bs-target="#orangtua"
                                    type="button" role="tab" aria-controls="orangtua" aria-selected="false">Orang
                                    Tua</button>
                            </li>
                            <li class="nav-item" role="presentation">
                                <button class="nav-link" id="wali-tab" data-bs-toggle="tab" data-bs-target="#wali"
                                    type="button" role="tab" aria-controls="wali" aria-selected="false">Wali</button>
                            </li>
                        </ul>
                    </div>
                    <div class="card-body">
                        <div class="tab-content" id="myTabContent">

                            <!-- Tab Alamat -->
                            <div class="tab-pane fade show active" id="alamat" role="tabpanel" aria-labelledby="alamat-tab">
                                <div class="row">
                                    <div class="col-md-6">
                                        <x-forms.input name="jalan" label="Jalan" placeholder="Nama Jalan" />
                                    </div>
                                    <div class="col-md-6">
                                        <x-forms.input name="dusun" label="Dusun" />
                                    </div>
                                    <div class="col-md-2">
                                        <x-forms.input name="rt" label="RT" type="number" />
                                    </div>
                                    <div class="col-md-2">
                                        <x-forms.input name="rw" label="RW" type="number" />
                                    </div>
                                    <div class="col-md-4">
                                        <x-forms.input name="kelurahan" label="Kelurahan" required="true" />
                                    </div>
                                    <div class="col-md-4">
                                        <x-forms.input name="kode_pos" label="Kode Pos" type="number" />
                                    </div>
                                    <div class="col-md-4">
                                        <x-forms.select name="provinsi_id" label="Provinsi" :options="$provinsis"
                                            placeholder="Pilih Provinsi" />
                                    </div>
                                    <div class="col-md-4">
                                        <x-forms.select name="kabupaten_id" label="Kabupaten" :options="[]"
                                            placeholder="Pilih Kabupaten" disabled />
                                    </div>
                                    <div class="col-md-4">
                                        <x-forms.select name="id_wilayah" label="Kecamatan" :options="[]" required="true"
                                            placeholder="Pilih Kecamatan" disabled />
                                    </div>
                                    <div class="col-md-6">
                                        <x-forms.select name="id_jenis_tinggal" label="Jenis Tinggal"
                                            :options="$jenisTinggals" />
                                    </div>
                                    <div class="col-md-6">
                                        <x-forms.select name="id_alat_transportasi" label="Alat Transportasi"
                                            :options="$alatTransportasis" />
                                    </div>
                                </div>
                            </div>

                            <!-- Tab Orang Tua -->
                            <div class="tab-pane fade" id="orangtua" role="tabpanel" aria-labelledby="orangtua-tab">
                                <div class="row">
                                    <!-- Ayah -->
                                    <div class="col-12 mb-3">
                                        <h6 class="text-primary fw-bold"><i class="ri-men-line me-1"></i> Data Ayah</h6>
                                        <hr class="mt-1">
                                    </div>
                                    <div class="col-md-6">
                                        <x-forms.input name="nama_ayah" label="Nama Ayah" />
                                    </div>
                                    <div class="col-md-6">
                                        <x-forms.input name="nik_ayah" label="NIK Ayah" type="number" />
                                    </div>
                                    <div class="col-md-6">
                                        <x-forms.datepicker name="tgl_lahir_ayah" label="Tanggal Lahir Ayah" />
                                    </div>
                                    <div class="col-md-6">
                                        <x-forms.select name="id_pendidikan_ayah" label="Pendidikan Ayah"
                                            :options="$jenjangPendidikans" />
                                    </div>
                                    <div class="col-md-6">
                                        <x-forms.select name="id_pekerjaan_ayah" label="Pekerjaan Ayah"
                                            :options="$pekerjaans" />
                                    </div>
                                    <div class="col-md-6">
                                        <x-forms.select name="id_penghasilan_ayah" label="Penghasilan Ayah"
                                            :options="$penghasilans" />
                                    </div>

                                    <!-- Ibu -->
                                    <div class="col-12 mt-4 mb-3">
                                        <h6 class="text-danger fw-bold"><i class="ri-women-line me-1"></i> Data Ibu</h6>
                                        <hr class="mt-1">
                                    </div>
                                    {{-- nama_ibu_kandung already in Data Utama --}}
                                    <div class="col-md-6">
                                        <x-forms.input name="nik_ibu" label="NIK Ibu" type="number" />
                                    </div>
                                    <div class="col-md-6">
                                        <x-forms.datepicker name="tgl_lahir_ibu" label="Tanggal Lahir Ibu" />
                                    </div>
                                    <div class="col-md-6">
                                        <x-forms.select name="id_pendidikan_ibu" label="Pendidikan Ibu"
                                            :options="$jenjangPendidikans" />
                                    </div>
                                    <div class="col-md-6">
                                        <x-forms.select name="id_pekerjaan_ibu" label="Pekerjaan Ibu"
                                            :options="$pekerjaans" />
                                    </div>
                                    <div class="col-md-6">
                                        <x-forms.select name="id_penghasilan_ibu" label="Penghasilan Ibu"
                                            :options="$penghasilans" />
                                    </div>
                                </div>
                            </div>

                            <!-- Tab Wali -->
                            <div class="tab-pane fade" id="wali" role="tabpanel" aria-labelledby="wali-tab">
                                <div class="row">
                                    <div class="col-12 mb-3">
                                        <div class="alert alert-warning py-2 mb-0">
                                            <i class="ri-information-line me-1"></i> Pengisian data wali bersifat opsional.
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <x-forms.input name="nama_wali" label="Nama Wali" />
                                    </div>
                                    <div class="col-md-6">
                                        <x-forms.datepicker name="tgl_lahir_wali" label="Tanggal Lahir Wali" />
                                    </div>
                                    <div class="col-md-6">
                                        <x-forms.select name="id_pendidikan_wali" label="Pendidikan Wali"
                                            :options="$jenjangPendidikans" />
                                    </div>
                                    <div class="col-md-6">
                                        <x-forms.select name="id_pekerjaan_wali" label="Pekerjaan Wali"
                                            :options="$pekerjaans" />
                                    </div>
                                    <div class="col-md-6">
                                        <x-forms.select name="id_penghasilan_wali" label="Penghasilan Wali"
                                            :options="$penghasilans" />
                                    </div>
                                </div>
                            </div>

                        </div>
                    </div>
                </div>
            </div>

            <!-- Actions -->
            <div class="col-12 text-end">
                <a href="{{ route('admin.mahasiswa.index') }}" class="btn btn-secondary me-2">Batal</a>
                <button type="submit" class="btn btn-primary">Simpan Data</button>
            </div>
        </div>
    </form>
@endsection

@push('scripts')
    <script>
        $(function () {
            // Cascading Wilayah (native selects, no Select2)
            const provinsiSelect = $('select[name="provinsi_id"]');
            const kabupatenSelect = $('select[name="kabupaten_id"]');
            const kecamatanSelect = $('select[name="id_wilayah"]');

            var isRandomFilling = false;

            // On Provinsi Change
            provinsiSelect.on('change', function () {
                if (isRandomFilling) return;

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
                if (isRandomFilling) return;

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

            // Fill Random Data
            $('#fillRandom').on('click', function () {
                var $btn = $(this);
                $btn.prop('disabled', true).html('<i class="ri-loader-4-line ri-spin me-1"></i> Generating...');
                isRandomFilling = true; // Block event listeners

                $.get("{{ route('admin.mahasiswa.random') }}")
                    .done(function (data) {
                        console.log('✅ Random Data Received:', data);

                        // 1. Populate Standard Fields (excluding Wilayah Cascade)
                        Object.keys(data).forEach(function (key) {
                            if (['provinsi_id', 'kabupaten_id', 'id_wilayah'].includes(key)) return;

                            var $field = $('[name="' + key + '"]');
                            var value = data[key];
                            if (typeof value === 'string') value = value.trim();

                            if ($field.length && value !== null) {
                                if ($field.is('select')) {
                                    $field.val(value);
                                } else if ($field.attr('type') === 'radio') {
                                    $('[name="' + key + '"][value="' + value + '"]').prop('checked', true);
                                } else if ($field.hasClass('flatpickr-input')) {
                                    if ($field[0]._flatpickr) {
                                        $field[0]._flatpickr.setDate(value);
                                    } else {
                                        $field.val(value);
                                    }
                                } else {
                                    $field.val(value);
                                }
                            }
                        });

                        // 2. Handle Wilayah Cascade with Chained AJAX
                        var provinsiId = data.provinsi_id ? data.provinsi_id.trim() : null;
                        var kabupatenId = data.kabupaten_id ? data.kabupaten_id.trim() : null;
                        var kecamatanId = data.id_wilayah ? data.id_wilayah.trim() : null;

                        if (provinsiId) {
                            console.log('1. Setting Provinsi:', provinsiId);
                            provinsiSelect.val(provinsiId);

                            // Fetch Kabupaten Manually
                            $.ajax({
                                url: "{{ route('admin.wilayah.kabupaten', ':id') }}".replace(':id', provinsiId),
                                type: 'GET',
                                success: function (kabData) {
                                    console.log('2. Fetched Kabupatens:', kabData.length);

                                    kabupatenSelect.empty().append('<option value="" disabled>Pilih Kabupaten</option>');
                                    $.each(kabData, function (key, item) {
                                        kabupatenSelect.append(new Option(item.text, item.id));
                                    });
                                    kabupatenSelect.removeAttr('disabled');

                                    if (kabupatenId) {
                                        console.log('   Setting Kabupaten Val:', kabupatenId);
                                        kabupatenSelect.val(kabupatenId);

                                        // Fetch Kecamatan Manually
                                        $.ajax({
                                            url: "{{ route('admin.wilayah.kecamatan', ':id') }}".replace(':id', kabupatenId),
                                            type: 'GET',
                                            success: function (kecData) {
                                                console.log('3. Fetched Kecamatans:', kecData.length);

                                                kecamatanSelect.empty().append('<option value="" disabled>Pilih Kecamatan</option>');
                                                $.each(kecData, function (key, item) {
                                                    kecamatanSelect.append(new Option(item.text, item.id));
                                                });
                                                kecamatanSelect.removeAttr('disabled');

                                                if (kecamatanId) {
                                                    console.log('   Setting Kecamatan Val:', kecamatanId);
                                                    kecamatanSelect.val(kecamatanId);
                                                }
                                                isRandomFilling = false;
                                            }
                                        });
                                    } else {
                                        isRandomFilling = false;
                                    }
                                }
                            });
                        } else {
                            isRandomFilling = false;
                        }

                        // Success Feedback
                        $btn.removeClass('btn-outline-secondary').addClass('btn-success').html('<i class="ri-check-line me-1"></i> Terisi!');
                        setTimeout(() => {
                            $btn.prop('disabled', false).removeClass('btn-success').addClass('btn-outline-secondary').html('<i class="ri-shuffle-line me-1"></i> Isi Data Random');
                        }, 2000);

                    })
                    .fail(function (xhr) {
                        console.error('Random Data Error:', xhr);
                        alert('Gagal mengambil data random.');
                        $btn.prop('disabled', false).html('<i class="ri-shuffle-line me-1"></i> Isi Data Random');
                        isRandomFilling = false;
                    });
            });
        });
    </script>
@endpush
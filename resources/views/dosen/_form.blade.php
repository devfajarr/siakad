<div class="row g-3">
    <div class="col-md-6">
        <label class="form-label" for="dosenNama">Nama <span class="text-danger">*</span></label>
        <input type="text" name="nama" id="dosenNama" class="form-control" placeholder="Nama lengkap"
            value="{{ old('nama', $dosen->nama ?? '') }}" {{ $readonly ? 'disabled' : '' }} required />
    </div>
    <div class="col-md-6">
        <label class="form-label" for="dosenNidn">NIDN</label>
        <input type="text" name="nidn" id="dosenNidn" class="form-control" placeholder="Nomor Induk Dosen Nasional"
            value="{{ old('nidn', $dosen->nidn ?? '') }}" {{ $readonly ? 'disabled' : '' }} />
    </div>
    <div class="col-md-6">
        <label class="form-label" for="dosenNip">NIP</label>
        <input type="text" name="nip" id="dosenNip" class="form-control" placeholder="Nomor Induk Pegawai"
            value="{{ old('nip', $dosen->nip ?? '') }}" {{ $readonly ? 'disabled' : '' }} />
    </div>
    <div class="col-md-6">
        <label class="form-label" for="dosenEmail">Email</label>
        <input type="email" name="email" id="dosenEmail" class="form-control" placeholder="email@domain.com"
            value="{{ old('email', $dosen->email ?? '') }}" {{ $readonly ? 'disabled' : '' }} />
    </div>
    <div class="col-md-6">
        <label class="form-label" for="dosenJk">Jenis Kelamin <span class="text-danger">*</span></label>
        <select name="jenis_kelamin" id="dosenJk" class="form-select" {{ $readonly ? 'disabled' : '' }} required>
            <option value="">-- Pilih --</option>
            <option value="L" {{ (old('jenis_kelamin', $dosen->jenis_kelamin ?? '') == 'L') ? 'selected' : '' }}>
                Laki - Laki</option>
            <option value="P" {{ (old('jenis_kelamin', $dosen->jenis_kelamin ?? '') == 'P') ? 'selected' : '' }}>
                Perempuan</option>
        </select>
    </div>
    <div class="col-md-6">
        <label class="form-label" for="dosenAgama">Agama <span class="text-danger">*</span></label>
        <select name="id_agama" id="dosenAgama" class="form-select" {{ $readonly ? 'disabled' : '' }} required>
            <option value="">-- Pilih --</option>
            @foreach ($agamaList as $agama)
                <option value="{{ $agama->id_agama }}" {{ (old('id_agama', $dosen->id_agama ?? '') == $agama->id_agama) ? 'selected' : '' }}>
                    {{ $agama->nama_agama }}
                </option>
            @endforeach
        </select>
    </div>
    <div class="col-md-6">
        <label class="form-label" for="dosenTglLahir">Tanggal Lahir <span class="text-danger">*</span></label>
        <input type="date" name="tanggal_lahir" id="dosenTglLahir" class="form-control"
            value="{{ old('tanggal_lahir', isset($dosen->tanggal_lahir) ? $dosen->tanggal_lahir->format('Y-m-d') : '') }}"
            {{ $readonly ? 'disabled' : '' }} required />
    </div>
    <div class="col-md-6">
        <label class="form-label" for="dosenTempatLahir">Tempat Lahir</label>
        <input type="text" name="tempat_lahir" id="dosenTempatLahir" class="form-control" placeholder="Kota Kelahiran"
            value="{{ old('tempat_lahir', $dosen->tempat_lahir ?? '') }}" {{ $readonly ? 'disabled' : '' }} />
    </div>
    <div class="col-md-6">
        <label class="form-label" for="dosenStatus">Status <span class="text-danger">*</span></label>
        <select name="is_active" id="dosenStatus" class="form-select" {{ $readonly ? 'disabled' : '' }} required>
            <option value="1" {{ (old('is_active', $dosen->is_active ?? true) == 1) ? 'selected' : '' }}>Aktif</option>
            <option value="0" {{ (old('is_active', $dosen->is_active ?? true) == 0) ? 'selected' : '' }}>Tidak Aktif
            </option>
        </select>
    </div>
</div>
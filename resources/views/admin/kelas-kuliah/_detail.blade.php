@php
    /** @var \App\Models\KelasKuliah $kelasKuliah */
    $isEditMode = $isEditMode ?? false;

    $inputAttrs = static function (bool $isEdit) {
        return $isEdit ? [] : ['readonly' => true, 'disabled' => true];
    };

    $kelasDosenRows = collect($kelasKuliah->dosenPengajar ?? [])
        ->where('is_deleted_server', false)
        ->map(function ($row) {
            return (object) [
                'id' => $row->id,
                'status_sinkronisasi' => $row->status_sinkronisasi,
                'is_deleted_server' => (bool) $row->is_deleted_server,
                'is_deleted_local' => (bool) $row->is_deleted_local,
                'dosen' => $row->dosen,
                'dosen_alias' => $row->dosen_alias,
                'dosen_alias_lokal' => $row->dosenAliasLokal,
                'bobot_sks' => $row->sks_substansi,
                'jumlah_rencana_pertemuan' => $row->rencana_minggu_pertemuan,
                'jumlah_realisasi_pertemuan' => $row->realisasi_minggu_pertemuan,
                'jenis_evaluasi' => $row->jenis_evaluasi,
                'can_delete' => !$row->is_deleted_local,
            ];
        });

    // Calculate effective total SKS for validation/display (excluding deleted_local)
    $activeTotalSks = $kelasDosenRows->where('is_deleted_local', false)->sum('bobot_sks');
    $jenisEvaluasiOptions = $jenisEvaluasiOptions ?? [
        '1' => 'Evaluasi Akademik',
        '2' => 'Aktivitas Partisipatif',
        '3' => 'Hasil Proyek',
        '4' => 'Kognitif / Pengetahuan',
    ];

    $modalErrorFields = [
        'kelas_kuliah_id',
        'dosen_id',
        'bobot_sks',
        'jumlah_rencana_pertemuan',
        'jumlah_realisasi_pertemuan',
        'jenis_evaluasi',
    ];
    $hasDosenModalErrors = $errors->hasAny($modalErrorFields);

    // Note: daftarMahasiswa is passed from the controller for the Select dropdown
@endphp

{{-- SECTION 1: Informasi Kelas Kuliah --}}
<div class="card mb-4">
    <div class="card-header d-flex justify-content-between align-items-center">
        <h5 class="mb-0">Kelas Kuliah</h5>
        <div class="d-flex gap-2">
            @if (!$isEditMode)
                <a href="{{ route('admin.kelas-kuliah.edit', $kelasKuliah->id) }}" class="btn btn-warning btn-sm">
                    <i class="ri-pencil-line me-1"></i> Edit
                </a>
                <form action="{{ route('admin.kelas-kuliah.destroy', $kelasKuliah->id) }}" method="POST"
                    class="d-inline" onsubmit="return confirm('Yakin ingin menghapus kelas kuliah ini?')">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="btn btn-danger btn-sm">
                        <i class="ri-delete-bin-line me-1"></i> Hapus
                    </button>
                </form>
                <a href="{{ route('admin.kelas-kuliah.index') }}" class="btn btn-outline-secondary btn-sm">
                    <i class="ri-list-check me-1"></i> Daftar
                </a>
            @else
                <button type="submit" form="form-kelas-kuliah" class="btn btn-primary btn-sm">
                    <i class="ri-save-line me-1"></i> Simpan
                </button>
                <a href="{{ route('admin.kelas-kuliah.show', $kelasKuliah->id) }}"
                    class="btn btn-outline-secondary btn-sm">
                    <i class="ri-close-line me-1"></i> Batal
                </a>
            @endif
        </div>
    </div>
    <div class="card-body">
        @if (session('error'))
            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                <strong>Error!</strong> {{ session('error') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        @endif

        @if (session('success'))
            <div class="alert alert-success alert-dismissible fade show" role="alert">
                <strong>Success!</strong> {{ session('success') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        @endif

        {{-- <div class="alert alert-info d-flex align-items-center mb-4" role="alert">
            <span class="alert-icon text-info me-2">
                <i class="ri-information-line"></i>
            </span>
            <span>Menyimpankan Jadwal Perkuliahan Setiap Periode</span>
        </div> --}}

        @if ($isEditMode)
            <form id="form-kelas-kuliah" action="{{ route('admin.kelas-kuliah.update', $kelasKuliah->id) }}"
                method="POST">
                @csrf
                @method('PUT')
        @endif

        {{-- Row 1: Program Studi & Semester --}}
        <div class="row mb-3">
            <div class="col-md-6 mb-3">
                <label class="form-label" for="id_prodi">Program Studi <span class="text-danger">*</span></label>
                @if ($isEditMode)
                    <select id="id_prodi" name="id_prodi" class="form-select select2 @error('id_prodi') is-invalid @enderror" required>
                        <option value="">-- Pilih Program Studi --</option>
                        @foreach($prodis as $prodi)
                            <option value="{{ $prodi->id_prodi }}" {{ old('id_prodi', $kelasKuliah->id_prodi) == $prodi->id_prodi ? 'selected' : '' }}>
                                {{ $prodi->nama_program_studi }} ({{ $prodi->nama_jenjang_pendidikan }})
                            </option>
                        @endforeach
                    </select>
                @else
                    <input type="text" class="form-control" value="{{ $kelasKuliah->programStudi->nama_program_studi ?? '-' }}" readonly disabled>
                @endif
                @error('id_prodi')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>

            <div class="col-md-6 mb-3">
                <label class="form-label" for="id_semester">Semester <span class="text-danger">*</span></label>
                @if ($isEditMode)
                    <select id="id_semester" name="id_semester" class="form-select @error('id_semester') is-invalid @enderror" required>
                        <option value="">-- Pilih Semester --</option>
                        @foreach($semesters as $semester)
                            <option value="{{ $semester->id_semester }}" {{ old('id_semester', $kelasKuliah->id_semester) == $semester->id_semester ? 'selected' : '' }}>
                                {{ $semester->nama_semester }}
                            </option>
                        @endforeach
                    </select>
                @else
                    <input type="text" class="form-control" value="{{ $kelasKuliah->semester->nama_semester ?? '-' }}" readonly disabled>
                @endif
                @error('id_semester')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>
        </div>

        {{-- Row 2: Mata Kuliah & Nama Kelas --}}
        <div class="row mb-3">
            <div class="col-md-6 mb-3">
                <label class="form-label" for="id_matkul">Mata Kuliah <span class="text-danger">*</span></label>
                @if ($isEditMode)
                    <select id="id_matkul" name="id_matkul" class="form-select select2 @error('id_matkul') is-invalid @enderror" required>
                        <option value="">-- Pilih Mata Kuliah --</option>
                        @foreach($mataKuliahs as $mk)
                            <option value="{{ $mk->id_matkul }}" {{ old('id_matkul', $kelasKuliah->id_matkul) == $mk->id_matkul ? 'selected' : '' }}>
                                {{ $mk->kode_mk }} - {{ $mk->nama_mk }} ({{ $mk->sks }} sks)
                            </option>
                        @endforeach
                    </select>
                @else
                    <input type="text" class="form-control"
                        value="{{ $kelasKuliah->mataKuliah ? $kelasKuliah->mataKuliah->kode_mk . ' - ' . $kelasKuliah->mataKuliah->nama_mk : '-' }}"
                        readonly disabled>
                @endif
                @error('id_matkul')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>

            <div class="col-md-6 mb-3">
                <label class="form-label" for="nama_kelas_kuliah">Nama Kelas <span class="text-danger">*</span></label>
                <input type="text" id="nama_kelas_kuliah" name="nama_kelas_kuliah"
                    value="{{ old('nama_kelas_kuliah', $kelasKuliah->nama_kelas_kuliah) }}" maxlength="5"
                    class="form-control @error('nama_kelas_kuliah') is-invalid @enderror"
                    @unless ($isEditMode) readonly disabled @endunless required>
                @error('nama_kelas_kuliah')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>
        </div>

        {{-- Row 3: Bobot MK & Bobot Tatap Muka --}}
        <div class="row mb-3">
            <div class="col-md-6 mb-3">
                <label class="form-label">Bobot Mata Kuliah <span class="text-danger">*</span></label>
                <div class="input-group">
                    <input type="number" step="0.01" class="form-control bg-light" value="{{ $kelasKuliah->sks_mk }}" readonly disabled>
                </div>
                <div class="form-text mt-1 text-primary small">
                    ( sks Tatap Muka + sks Praktikum + sks Praktek Lapangan + sks Simulasi )
                </div>
            </div>
            <div class="col-md-6 mb-3">
                <label class="form-label" for="sks_tm">Bobot Tatap Muka</label>
                <div class="input-group">
                    <input type="number" step="0.01" id="sks_tm" name="sks_tm" class="form-control @error('sks_tm') is-invalid @enderror" 
                        value="{{ old('sks_tm', $kelasKuliah->sks_tm) }}" @unless ($isEditMode) readonly disabled @endunless>
                    <span class="input-group-text">sks</span>
                </div>
                @error('sks_tm')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>
        </div>

        {{-- Row 4: Bobot Praktikum & Bobot Praktek Lapangan --}}
        <div class="row mb-3">
            <div class="col-md-6 mb-3">
                <label class="form-label" for="sks_prak">Bobot Praktikum</label>
                <div class="input-group">
                    <input type="number" step="0.01" id="sks_prak" name="sks_prak" class="form-control @error('sks_prak') is-invalid @enderror" 
                        value="{{ old('sks_prak', $kelasKuliah->sks_prak) }}" @unless ($isEditMode) readonly disabled @endunless>
                    <span class="input-group-text">sks</span>
                </div>
                @error('sks_prak')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>
            <div class="col-md-6 mb-3">
                <label class="form-label" for="sks_prak_lap">Bobot Praktek Lapangan</label>
                <div class="input-group">
                    <input type="number" step="0.01" id="sks_prak_lap" name="sks_prak_lap" class="form-control @error('sks_prak_lap') is-invalid @enderror" 
                        value="{{ old('sks_prak_lap', $kelasKuliah->sks_prak_lap) }}" @unless ($isEditMode) readonly disabled @endunless>
                    <span class="input-group-text">sks</span>
                </div>
                @error('sks_prak_lap')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>
        </div>

        {{-- Row 5: Bahasan & Bobot Simulasi --}}
        <div class="row mb-3">
            <div class="col-md-6 mb-3">
                <label class="form-label" for="bahasan">Bahasan</label>
                <textarea id="bahasan" name="bahasan" rows="3" class="form-control @error('bahasan') is-invalid @enderror" 
                    @unless ($isEditMode) readonly disabled @endunless>{{ old('bahasan', $kelasKuliah->bahasan) }}</textarea>
                @error('bahasan')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>
            <div class="col-md-6 mb-3">
                <div class="mb-3">
                    <label class="form-label" for="sks_sim">Bobot Simulasi</label>
                    <div class="input-group">
                        <input type="number" step="0.01" id="sks_sim" name="sks_sim" class="form-control @error('sks_sim') is-invalid @enderror" 
                            value="{{ old('sks_sim', $kelasKuliah->sks_sim) }}" @unless ($isEditMode) readonly disabled @endunless>
                        <span class="input-group-text">sks</span>
                    </div>
                    @error('sks_sim')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>
                <div>
                    <label class="form-label" for="kapasitas">Kapasitas</label>
                    <input type="number" id="kapasitas" name="kapasitas" class="form-control @error('kapasitas') is-invalid @enderror" 
                        value="{{ old('kapasitas', $kelasKuliah->kapasitas) }}" @unless ($isEditMode) readonly disabled @endunless>
                    @error('kapasitas')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>
            </div>
        </div>

        {{-- Row 6: Lingkup & Mode Kuliah --}}
        <div class="row mb-3">
            <div class="col-md-6 mb-3">
                <label class="form-label" for="lingkup">Lingkup</label>
                <select id="lingkup" name="lingkup" class="form-select @error('lingkup') is-invalid @enderror"
                    @unless ($isEditMode) disabled @endunless>
                    <option value="">-- Pilih Lingkup --</option>
                    <option value="1" {{ old('lingkup', $kelasKuliah->lingkup) == '1' ? 'selected' : '' }}>Internal</option>
                    <option value="2" {{ old('lingkup', $kelasKuliah->lingkup) == '2' ? 'selected' : '' }}>External</option>
                    <option value="3" {{ old('lingkup', $kelasKuliah->lingkup) == '3' ? 'selected' : '' }}>Campuran</option>
                </select>
                @error('lingkup')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>
            <div class="col-md-6 mb-3">
                <label class="form-label" for="mode">Mode Kuliah</label>
                <select id="mode" name="mode" class="form-select @error('mode') is-invalid @enderror"
                    @unless ($isEditMode) disabled @endunless>
                    <option value="">-- Pilih Mode Kuliah --</option>
                    <option value="O" {{ old('mode', $kelasKuliah->mode) == 'O' ? 'selected' : '' }}>Online</option>
                    <option value="F" {{ old('mode', $kelasKuliah->mode) == 'F' ? 'selected' : '' }}>Offline</option>
                    <option value="M" {{ old('mode', $kelasKuliah->mode) == 'M' ? 'selected' : '' }}>Campuran</option>
                </select>
                @error('mode')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>
        </div>

        {{-- Row 7: Tanggal Mulai & Akhir Efektif --}}
        <div class="row mb-3">
            <div class="col-md-6 mb-3">
                <label class="form-label" for="tanggal_mulai_efektif">Tanggal Mulai Efektif</label>
                <input type="date" id="tanggal_mulai_efektif" name="tanggal_mulai_efektif"
                    class="form-control @error('tanggal_mulai_efektif') is-invalid @enderror"
                    value="{{ old('tanggal_mulai_efektif', optional($kelasKuliah->tanggal_mulai_efektif)->format('Y-m-d')) }}"
                    @unless ($isEditMode) readonly disabled @endunless>
                @error('tanggal_mulai_efektif')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>
            <div class="col-md-6 mb-3">
                <label class="form-label" for="tanggal_akhir_efektif">Tanggal Akhir Efektif</label>
                <input type="date" id="tanggal_akhir_efektif" name="tanggal_akhir_efektif"
                    class="form-control @error('tanggal_akhir_efektif') is-invalid @enderror"
                    value="{{ old('tanggal_akhir_efektif', optional($kelasKuliah->tanggal_akhir_efektif)->format('Y-m-d')) }}"
                    @unless ($isEditMode) readonly disabled @endunless>
                @error('tanggal_akhir_efektif')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>
        </div>

        @if ($isEditMode)
            </form>
        @endif
    </div>

</div>

@include('admin.kelas-kuliah.partials.modal-dosen', [
    'kelasKuliah' => $kelasKuliah,
    'daftarDosen' => $daftarDosen ?? collect(),
    'daftarDosenLokal' => $daftarDosenLokal ?? collect(),
    'jenisEvaluasiOptions' => $jenisEvaluasiOptions,
])


@include('admin.kelas-kuliah.partials.modal-peserta-kolektif', [
    'kelasKuliah' => $kelasKuliah,
    'daftarMahasiswa' => $daftarMahasiswa ?? collect(),
])


{{-- SECTION 2: Tabs Dosen Pengajar & Mahasiswa KRS --}}
<div class="card">
    <div class="card-header">
        <ul class="nav nav-tabs card-header-tabs nav-fill" role="tablist">
            <li class="nav-item" role="presentation">
                <button class="nav-link active fw-medium" id="tab-dosen-pengajar" data-bs-toggle="tab"
                    data-bs-target="#tab-pane-dosen-pengajar" type="button" role="tab"
                    aria-controls="tab-pane-dosen-pengajar" aria-selected="true">
                    <i class="ri-user-voice-line me-1"></i> Dosen Pengajar
                </button>
            </li>
            <li class="nav-item" role="presentation">
                <button class="nav-link fw-medium" id="tab-mahasiswa-krs" data-bs-toggle="tab"
                    data-bs-target="#tab-pane-mahasiswa-krs" type="button" role="tab"
                    aria-controls="tab-pane-mahasiswa-krs" aria-selected="false">
                    <i class="ri-group-line me-1"></i> Mahasiswa KRS / Peserta Kelas
                </button>
            </li>
            <li class="nav-item" role="presentation">
                <button class="nav-link fw-medium" id="tab-jadwal-ruangan" data-bs-toggle="tab"
                    data-bs-target="#tab-pane-jadwal-ruangan" type="button" role="tab"
                    aria-controls="tab-pane-jadwal-ruangan" aria-selected="false">
                    <i class="ri-calendar-event-line me-1"></i> Jadwal & Ruangan
                </button>
            </li>
        </ul>
    </div>
    <div class="card-body">
        <div class="tab-content">
            {{-- TAB 1: Dosen Pengajar --}}
            <div class="tab-pane fade show active" id="tab-pane-dosen-pengajar" role="tabpanel"
                aria-labelledby="tab-dosen-pengajar">
                <div class="d-flex justify-content-between align-items-center mb-3 flex-wrap gap-2">
                    <div class="fw-semibold text-muted">
                        Daftar dosen pengajar untuk kelas ini.
                        <span class="ms-2 badge bg-label-secondary">
                            SKS Terisi: {{ number_format($activeTotalSks, 2) }} / {{ number_format($kelasKuliah->sks_mk, 2) }}
                        </span>
                    </div>
                    <div class="d-flex gap-2">
                        <button type="button" class="btn btn-primary btn-sm" data-bs-toggle="modal"
                            data-bs-target="#modalDosen">
                            <i class="ri-add-line me-1"></i> Tambah Aktivitas Mengajar Dosen
                        </button>
                    </div>
                </div>

                <div class="table-responsive text-nowrap">
                    <table class="table table-bordered table-striped table-hover align-middle">
                        <thead class="table-light">
                            <tr class="text-nowrap text-center align-middle">
                                <th rowspan="2">Status</th>
                                <th rowspan="2">No.</th>
                                <th rowspan="2">NIDN</th>
                                <th rowspan="2">NUPTK</th>
                                <th rowspan="2">Nama Dosen</th>
                                <th rowspan="2" class="px-3">Bobot (sks)</th>
                                <th colspan="2">Pertemuan</th>
                                <th rowspan="2">Jenis Evaluasi</th>
                                <th rowspan="2">Aksi</th>
                            </tr>
                            <tr class="text-nowrap text-center align-middle">
                                <th>Rencana</th>
                                <th>Realisasi</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($kelasDosenRows as $index => $row)
                                <tr>
                                    <td class="text-center">
                                        @php
                                            $badgeClass = 'bg-label-secondary';
                                            $icon = '';
                                            $label = $row->status_sinkronisasi;

                                            if (in_array($row->status_sinkronisasi, ['pending', 'created_local'], true)) {
                                                $badgeClass = 'bg-label-warning';
                                                $label = 'lokal';
                                            } elseif ($row->status_sinkronisasi === 'synced') {
                                                $badgeClass = 'bg-label-success';
                                                $label = 'sudah sync';
                                                $icon = '<i class="ri-check-line me-1"></i>';
                                            } elseif ($row->status_sinkronisasi === 'updated_local') {
                                                $badgeClass = 'bg-label-primary';
                                                $label = 'updated_local';
                                            } elseif ($row->status_sinkronisasi === 'deleted_local') {
                                                $badgeClass = 'bg-label-danger';
                                                $label = 'deleted_local';
                                            } elseif ($row->status_sinkronisasi === 'push_failed') {
                                                $badgeClass = 'bg-label-danger';
                                                $label = 'push_failed';
                                            }
                                        @endphp
                                        <span class="badge {{ $badgeClass }} rounded-pill text-lowercase">
                                            {!! $icon !!}{{ $label }}
                                        </span>
                                    </td>
                                    <td class="text-center">{{ $index + 1 }}</td>
                                    <td class="text-center">{{ $row->dosen->nidn ?? '-' }}</td>
                                    <td class="text-center">{{ $row->dosen->nip ?? '-' }}</td>
                                    <td>
                                        {{ $row->dosen->nama ?? '-' }}
                                        @if($row->dosen_alias_lokal)
                                            <br><small class="text-primary fw-bold">Alias: {{ $row->dosen_alias_lokal->nama }}</small>
                                        @elseif($row->dosen_alias)
                                            <br><small class="text-primary fw-bold">Alias: {{ $row->dosen_alias }}</small>
                                        @endif
                                    </td>
                                    <td class="text-center">{{ number_format((float) $row->bobot_sks, 2) }}</td>
                                    <td class="text-center">{{ $row->jumlah_rencana_pertemuan }}</td>
                                    <td class="text-center">{{ $row->jumlah_realisasi_pertemuan ?? '-' }}</td>
                                    <td>
                                        @if ($row->jenis_evaluasi !== null)
                                            {{ $jenisEvaluasiOptions[(string) $row->jenis_evaluasi] ?? $row->jenis_evaluasi }}
                                        @else
                                            -
                                        @endif
                                    </td>
                                    <td class="text-center">
                                        <div class="d-flex justify-content-center gap-1">
                                            @if ($row->can_delete)
                                                {{-- Placeholder Edit - Yellow in screenshot --}}
                                                <button type="button" class="btn btn-icon btn-sm btn-outline-warning disabled" disabled>
                                                    <i class="ri-edit-2-line"></i>
                                                </button>
                                                <form action="{{ route('kelas.dosen.destroy', $row->id) }}"
                                                    method="POST" class="d-inline">
                                                    @csrf
                                                    @method('DELETE')
                                                    <button type="submit" class="btn btn-icon btn-sm btn-outline-danger"
                                                        onclick="return confirm('Yakin menghapus dosen pengajar ini?')">
                                                        <i class="ri-delete-bin-line"></i>
                                                    </button>
                                                </form>
                                            @else
                                                <span class="text-muted">-</span>
                                            @endif
                                        </div>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="10" class="text-center text-muted py-4">
                                        Belum ada data dosen pengajar untuk kelas ini.
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                        @if($kelasDosenRows->isNotEmpty())
                            <tfoot class="fw-bold bg-white">
                                <tr>
                                    <td colspan="5" class="text-end text-uppercase py-3 pe-4">Total SKS</td>
                                    <td class="text-center py-3">{{ number_format($activeTotalSks, 2) }}</td>
                                    <td colspan="4" class="bg-white border-0"></td>
                                </tr>
                            </tfoot>
                        @endif

                    </table>
                </div>

                {{-- Section Keterangan --}}
                <div class="mt-4 p-4 border rounded shadow-sm bg-label-secondary" style="border-left: 5px solid #28a745 !important;">
                    <h6 class="fw-bold mb-3 text-dark">Keterangan :</h6>
                    <div class="small text-dark">
                        <div class="mb-3">
                            <span class="fw-bold">- Perkuliahan Reguler</span>
                            <ul class="list-unstyled ms-3 mt-1">
                                <li class="mb-1">
                                    <i class="ri-checkbox-blank-circle-fill me-1" style="font-size: 6px; vertical-align: middle;"></i>
                                    Tanggal mulai efektif = Tanggal mulai perkuliahan dalam satu semester
                                </li>
                                <li>
                                    <i class="ri-checkbox-blank-circle-fill me-1" style="font-size: 6px; vertical-align: middle;"></i>
                                    Tanggal akhir efektif = Tanggal akhir perkuliahan dalam satu semester
                                </li>
                            </ul>
                        </div>
                        <p class="mb-2">- Fasilitas hitung sks disediakan untuk membantu perhitungan sks dosen secara otomatis.</p>
                        <p class="mb-0">- Rumus Perhitungan sks dosen = ( Rencana Pertemuan : Jumlah Seluruh Rencana Pertemuan Seluruh Dosen ) x sks Matakuliah.</p>
                    </div>
                </div>
            </div>

            {{-- TAB 2: Mahasiswa KRS / Peserta Kelas --}}
            <div class="tab-pane fade" id="tab-pane-mahasiswa-krs" role="tabpanel" aria-labelledby="tab-mahasiswa-krs">
                
                <div class="d-flex justify-content-between align-items-center mb-3">
                    <div class="fw-semibold text-muted">
                        Daftar mahasiswa yang telah didaftarkan dalam kelas ini.
                    </div>
                </div>

                {{-- Form Tambah Individu & Tombol Kolektif --}}
                <div class="card mb-4">
    <div class="card-body">

        <div class="row align-items-end g-3">

            {{-- FORM INDIVIDU --}}
            <div class="col-12">

                <label class="form-label mb-2">
                    <i class="ri-user-add-line me-1"></i>
                    Tambah Peserta Individu
                    <span class="text-danger">*</span>
                </label>

                <form action="{{ route('admin.peserta-kelas-kuliah.store') }}" 
                      method="POST" 
                      id="form-tambah-peserta">
                    @csrf

                    <input type="hidden" 
                           name="id_kelas_kuliah" 
                           value="{{ $kelasKuliah->id_kelas_kuliah }}">

                    <div class="d-flex gap-2">

                        <div class="flex-grow-1">
                            <div class="input-group">

                                <select class="form-select select2-mahasiswa"
                                        name="riwayat_pendidikan_id"
                                        required
                                        data-placeholder="Cari berdasarkan NIM atau Nama Mahasiswa...">
                                    <option></option>

                                    @foreach($daftarMahasiswa as $mhsRiwayat)
                                        <option value="{{ $mhsRiwayat->id }}">
                                            {{ $mhsRiwayat->nim ?? '-' }} -
                                            {{ $mhsRiwayat->mahasiswa->nama_mahasiswa ?? 'Unknown' }}
                                            ({{ $mhsRiwayat->programStudi->nama_program_studi ?? '-' }})
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                        </div>

                        <button type="submit"
                                class="btn btn-primary px-4">
                            <i class="ri-save-line me-1"></i>
                            Simpan
                        </button>

                        <button type="button"
                        class="btn btn-outline-primary px-4"
                        data-bs-toggle="modal"
                        data-bs-target="#modalKolektifPeserta">
                    <i class="ri-group-add-line me-2"></i>
                    Input Kolektif Peserta
                </button>

                    </div>
                </form>
            </div>

        </div>

    </div>
</div>

                <div class="table-responsive text-nowrap mt-2">
                    <table class="table table-bordered table-striped table-hover align-middle">
                        <thead class="table-light">
                            <tr class="text-nowrap text-center">
                                <th>Status Sync</th>
                                <th>No</th>
                                <th>NIM</th>
                                <th class="text-start">Nama Mahasiswa</th>
                                <th>L/P</th>
                                <th class="text-start">Program Studi</th>
                                <th>Periode Masuk</th>
                                <th>Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse ($kelasKuliah->pesertaKelasKuliah as $index => $peserta)
                                @php
                                    $mhs = $peserta->riwayatPendidikan?->mahasiswa;
                                    $prodi = $peserta->riwayatPendidikan?->programStudi;
                                @endphp
                                <tr class="text-center">
                                    <td>
                                        @php
                                            $badgeClass = 'bg-label-secondary';
                                            $icon = '';
                                            $label = $peserta->status_sinkronisasi;

                                            if (in_array($peserta->status_sinkronisasi, ['pending', 'created_local'], true)) {
                                                $badgeClass = 'bg-label-warning';
                                                $label = 'lokal';
                                            } elseif ($peserta->status_sinkronisasi === 'synced') {
                                                $badgeClass = 'bg-label-success';
                                                $label = 'sudah sync';
                                                $icon = '<i class="ri-check-line me-1"></i>';
                                            } elseif ($peserta->status_sinkronisasi === 'updated_local') {
                                                $badgeClass = 'bg-label-primary';
                                                $label = 'updated_local';
                                            } elseif ($peserta->status_sinkronisasi === 'deleted_local') {
                                                $badgeClass = 'bg-label-danger';
                                                $label = 'deleted_local';
                                            } elseif ($peserta->status_sinkronisasi === 'push_failed') {
                                                $badgeClass = 'bg-label-danger';
                                                $label = 'push_failed';
                                            }
                                        @endphp
                                        <span class="badge {{ $badgeClass }} rounded-pill text-lowercase">
                                            {!! $icon !!}{{ $label }}
                                        </span>
                                    </td>
                                    <td>{{ $index + 1 }}</td>
                                    <td><span class="fw-semibold text-primary w-100 d-block">{{ $peserta->riwayatPendidikan->nim ?? '-' }}</span></td>
                                    <td class="text-start fw-medium">{{ $mhs->nama ?? 'Unknown' }}</td>
                                    <td>{{ $mhs->jenis_kelamin ?? '-' }}</td>
                                    <td class="text-start">
                                        @if($prodi)
                                            {{ $prodi->nama_program_studi }} <span class="text-muted small">({{ $prodi->nama_jenjang_pendidikan }})</span>
                                        @else
                                            -
                                        @endif
                                    </td>
                                    <td>{{ $peserta->riwayatPendidikan->id_periode_masuk ?? '-' }}</td>
                                    <td>
                                        <form action="{{ route('admin.peserta-kelas-kuliah.destroy', $peserta->id) }}" method="POST" class="d-inline">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="btn btn-icon btn-sm btn-outline-danger" title="Keluarkan Peserta" onclick="return confirm('Yakin ingin mengeluarkan {{ $mhs->nama ?? 'Mahasiswa ini' }} dari kelas?')">
                                                <i class="ri-delete-bin-line"></i>
                                            </button>
                                        </form>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="8" class="text-center text-muted py-4">
                                        Belum ada peserta yang didaftarkan di kelas ini.
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>

            {{-- TAB 3: Jadwal & Ruangan --}}
            <div class="tab-pane fade" id="tab-pane-jadwal-ruangan" role="tabpanel" aria-labelledby="tab-jadwal-ruangan">
                @include('admin.kelas-kuliah.partials._tab_jadwal')
            </div>

        </div>
    </div>
</div>

@push('styles')
    <link rel="stylesheet" href="{{ asset('assets/vendor/libs/select2/select2.css') }}" />
    <link rel="stylesheet" href="{{ asset('assets/vendor/libs/datatables-bs5/datatables.bootstrap5.css') }}" />
    <link rel="stylesheet" href="{{ asset('assets/vendor/libs/datatables-responsive-bs5/responsive.bootstrap5.css') }}" />
    <link rel="stylesheet" href="{{ asset('assets/vendor/libs/datatables-checkboxes-jquery/datatables.checkboxes.css') }}" />
    <link rel="stylesheet" href="{{ asset('assets/vendor/libs/datatables-buttons-bs5/buttons.bootstrap5.css') }}" />
    <link rel="stylesheet" href="{{ asset('assets/vendor/libs/datatables-rowgroup-bs5/rowgroup.bootstrap5.css') }}" />
@endpush

@push('scripts')
    <script src="{{ asset('assets/vendor/libs/select2/select2.js') }}"></script>
    <script src="{{ asset('assets/vendor/libs/datatables-bs5/datatables-bootstrap5.js') }}"></script>
    <script>
        $(function() {
            // Initialize Select2 for Program Studi and Mata Kuliah
            $('.select2').each(function() {
                var $this = $(this);
                $this.select2({
                    placeholder: $this.attr('placeholder') || 'Pilih data',
                    dropdownParent: $this.parent(),
                    width: '100%'
                });
            });

            // Initialize Select2 for Mahasiswa (Peserta Kelas tab)
            $('.select2-mahasiswa').select2({
                placeholder: 'Cari berdasarkan NIM atau Nama Mahasiswa...',
                width: '100%',
                allowClear: true
            });

            // Initialize Select2 for Ruangan (Modal Jadwal Kuliah)
            $('.select2-ruangan').select2({
                dropdownParent: $('#modalJadwal'),
                placeholder: '-- Cari Ruangan --',
                width: '100%'
            });

            // Specific initialization for Modal Dosen (if present)
            const dosenSelect = $('#dosen_id');
            const modalElement = document.getElementById('modalDosen');
            const hasModalError = @json($hasDosenModalErrors ?? false);

            if (dosenSelect.length) {
                dosenSelect.select2({
                    dropdownParent: $('#modalDosen'),
                    width: '100%',
                    placeholder: 'Pilih Dosen'
                });
            }

            if (hasModalError && modalElement) {
                const tabTrigger = document.querySelector('#tab-dosen-pengajar');
                if (tabTrigger) {
                    const tab = new bootstrap.Tab(tabTrigger);
                    tab.show();
                }

                const modal = new bootstrap.Modal(modalElement);
                modal.show();
            }
        });
    </script>
@endpush

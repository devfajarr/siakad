@php
/** @var \App\Models\KelasKuliah $kelasKuliah */
$isEditMode = $isEditMode ?? false;

$inputAttrs = static function (bool $isEdit) {
    return $isEdit ? [] : ['readonly' => true, 'disabled' => true];
};

$kelasDosenRows = $kelasKuliah->kelasDosen ?? collect();
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

// Dummy data untuk tab Mahasiswa KRS / Peserta Kelas
$dummyMahasiswa = [
    [
        'status' => 'synced',
        'nim' => '322241001',
        'nama' => 'MUHAMMAD FARUK NURWAWI',
        'jk' => 'L',
        'prodi' => 'D3 - Teknik Informatika',
        'angkatan' => 2024,
    ],
    [
        'status' => 'local',
        'nim' => '322241002',
        'nama' => 'NADHIF SETYA MUFADDA',
        'jk' => 'P',
        'prodi' => 'D3 - Teknik Informatika',
        'angkatan' => 2024,
    ],
    [
        'status' => 'pending',
        'nim' => '322241003',
        'nama' => 'MAHASISWA PENDING PUSH',
        'jk' => 'L',
        'prodi' => 'D3 - Teknik Informatika',
        'angkatan' => 2025,
    ],
];
@endphp

{{-- SECTION 1: Informasi Kelas Kuliah --}}
<div class="card mb-4">
    <div class="card-header d-flex justify-content-between align-items-center">
        <h5 class="mb-0">Kelas Kuliah</h5>
        <div class="d-flex gap-2">
            @if (!$isEditMode)
                @if($kelasKuliah->sumber_data === 'lokal')
                    <a href="{{ route('admin.kelas-kuliah.edit', $kelasKuliah->id) }}" class="btn btn-warning btn-sm">
                        <i class="ri-pencil-line me-1"></i> Edit
                    </a>
                    <form action="{{ route('admin.kelas-kuliah.destroy', $kelasKuliah->id) }}" method="POST" class="d-inline">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="btn btn-danger btn-sm">
                            <i class="ri-delete-bin-line me-1"></i> Hapus
                        </button>
                    </form>
                @endif
                <a href="{{ route('admin.kelas-kuliah.index') }}" class="btn btn-outline-secondary btn-sm">
                    <i class="ri-list-check me-1"></i> Daftar
                </a>
            @else
                <button type="submit" form="form-kelas-kuliah" class="btn btn-primary btn-sm">
                    <i class="ri-save-line me-1"></i> Simpan
                </button>
                <a href="{{ route('admin.kelas-kuliah.show', $kelasKuliah->id) }}" class="btn btn-outline-secondary btn-sm">
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

        @if($isEditMode)
            <form id="form-kelas-kuliah" action="{{ route('admin.kelas-kuliah.update', $kelasKuliah->id) }}" method="POST">
                @csrf
                @method('PUT')
        @endif

        <div class="row mb-3">
            {{-- Program Studi --}}
            <div class="col-md-6 mb-3">
                <label class="form-label">Program Studi</label>
                <input type="text"
                       class="form-control"
                       value="{{ $kelasKuliah->programStudi->nama_program_studi ?? '-' }}"
                       readonly
                       disabled>
            </div>

            {{-- Semester --}}
            <div class="col-md-6 mb-3">
                <label class="form-label">Semester</label>
                <input type="text"
                       class="form-control"
                       value="{{ $kelasKuliah->semester->nama_semester ?? '-' }}"
                       readonly
                       disabled>
            </div>
        </div>

        <div class="row mb-3">
            {{-- Mata Kuliah --}}
            <div class="col-md-6 mb-3">
                <label class="form-label">Mata Kuliah</label>
                <input type="text"
                       class="form-control"
                       value="{{ $kelasKuliah->mataKuliah ? $kelasKuliah->mataKuliah->kode_mk . ' - ' . $kelasKuliah->mataKuliah->nama_mk : '-' }}"
                       readonly
                       disabled>
            </div>

            {{-- Nama Kelas --}}
            <div class="col-md-6 mb-3">
                <label class="form-label" for="nama_kelas_kuliah">Nama Kelas</label>
                <input type="text"
                       id="nama_kelas_kuliah"
                       name="nama_kelas_kuliah"
                       value="{{ old('nama_kelas_kuliah', $kelasKuliah->nama_kelas_kuliah) }}"
                       maxlength="5"
                       class="form-control @error('nama_kelas_kuliah') is-invalid @enderror"
                       @unless($isEditMode) readonly disabled @endunless>
                @error('nama_kelas_kuliah')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>
        </div>

        {{-- Bobot SKS --}}
        <div class="row mb-3">
            <div class="col-md-4 mb-3">
                <label class="form-label">Bobot MK</label>
                <div class="input-group">
                    <input type="number" class="form-control" value="{{ $kelasKuliah->sks_mk }}" readonly disabled>
                    <span class="input-group-text">sks</span>
                </div>
            </div>
            <div class="col-md-4 mb-3">
                <label class="form-label">Bobot Tatap Muka</label>
                <div class="input-group">
                    <input type="number" class="form-control" value="{{ $kelasKuliah->sks_tm }}" readonly disabled>
                    <span class="input-group-text">sks</span>
                </div>
            </div>
            <div class="col-md-4 mb-3">
                <label class="form-label">Bobot Praktikum</label>
                <div class="input-group">
                    <input type="number" class="form-control" value="{{ $kelasKuliah->sks_prak }}" readonly disabled>
                    <span class="input-group-text">sks</span>
                </div>
            </div>
        </div>

        <div class="row mb-3">
            <div class="col-md-4 mb-3">
                <label class="form-label">Bobot Praktek Lapangan</label>
                <div class="input-group">
                    <input type="number" class="form-control" value="{{ $kelasKuliah->sks_prak_lap }}" readonly disabled>
                    <span class="input-group-text">sks</span>
                </div>
            </div>
            <div class="col-md-4 mb-3">
                <label class="form-label">Bobot Simulasi</label>
                <div class="input-group">
                    <input type="number" class="form-control" value="{{ $kelasKuliah->sks_sim }}" readonly disabled>
                    <span class="input-group-text">sks</span>
                </div>
            </div>
        </div>

        {{-- Lingkup & Mode --}}
        <div class="row mb-3">
            <div class="col-md-6 mb-3">
                <label class="form-label" for="lingkup">Lingkup</label>
                <select id="lingkup"
                        name="lingkup"
                        class="form-select @error('lingkup') is-invalid @enderror"
                        @unless($isEditMode) disabled @endunless>
                    <option value="">-- Pilih Lingkup --</option>
                    <option value="1" {{ old('lingkup', $kelasKuliah->lingkup) == '1' ? 'selected' : '' }}>1 - Internal</option>
                    <option value="2" {{ old('lingkup', $kelasKuliah->lingkup) == '2' ? 'selected' : '' }}>2 - External</option>
                    <option value="3" {{ old('lingkup', $kelasKuliah->lingkup) == '3' ? 'selected' : '' }}>3 - Campuran</option>
                </select>
                @error('lingkup')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>
            <div class="col-md-6 mb-3">
                <label class="form-label" for="mode">Mode Kuliah</label>
                <select id="mode"
                        name="mode"
                        class="form-select @error('mode') is-invalid @enderror"
                        @unless($isEditMode) disabled @endunless>
                    <option value="">-- Pilih Mode Kuliah --</option>
                    <option value="O" {{ old('mode', $kelasKuliah->mode) == 'O' ? 'selected' : '' }}>O - Online</option>
                    <option value="F" {{ old('mode', $kelasKuliah->mode) == 'F' ? 'selected' : '' }}>F - Offline</option>
                    <option value="M" {{ old('mode', $kelasKuliah->mode) == 'M' ? 'selected' : '' }}>M - Campuran</option>
                </select>
                @error('mode')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>
        </div>

        {{-- Tanggal Efektif --}}
        <div class="row mb-3">
            <div class="col-md-6 mb-3">
                <label class="form-label" for="tanggal_mulai_efektif">Tanggal Mulai Efektif</label>
                <input type="date"
                       id="tanggal_mulai_efektif"
                       name="tanggal_mulai_efektif"
                       class="form-control @error('tanggal_mulai_efektif') is-invalid @enderror"
                       value="{{ old('tanggal_mulai_efektif', optional($kelasKuliah->tanggal_mulai_efektif)->format('Y-m-d')) }}"
                       @unless($isEditMode) readonly disabled @endunless>
                @error('tanggal_mulai_efektif')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>
            <div class="col-md-6 mb-3">
                <label class="form-label" for="tanggal_akhir_efektif">Tanggal Akhir Efektif</label>
                <input type="date"
                       id="tanggal_akhir_efektif"
                       name="tanggal_akhir_efektif"
                       class="form-control @error('tanggal_akhir_efektif') is-invalid @enderror"
                       value="{{ old('tanggal_akhir_efektif', optional($kelasKuliah->tanggal_akhir_efektif)->format('Y-m-d')) }}"
                       @unless($isEditMode) readonly disabled @endunless>
                @error('tanggal_akhir_efektif')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>
        </div>

        @if($isEditMode)
            </form>
        @endif
    </div>
</div>

@include('kelas-kuliah.partials.modal-dosen', [
    'kelasKuliah' => $kelasKuliah,
    'daftarDosen' => $daftarDosen ?? collect(),
    'jenisEvaluasiOptions' => $jenisEvaluasiOptions,
])

@push('styles')
    <link rel="stylesheet" href="{{ asset('assets/vendor/libs/select2/select2.css') }}" />
@endpush

@push('scripts')
    <script src="{{ asset('assets/vendor/libs/select2/select2.js') }}"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const dosenSelect = $('#dosen_id');
            const modalElement = document.getElementById('modalDosen');
            const hasModalError = @json($hasDosenModalErrors);

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

{{-- SECTION 2: Tabs Dosen Pengajar & Mahasiswa KRS --}}
<div class="card">
    <div class="card-header pb-0">
        <ul class="nav nav-tabs card-header-tabs border-bottom" role="tablist"
            style="display: flex; width: 100%; overflow: hidden;">
            <li class="nav-item" style="flex: 1; text-align: center;">
                <button class="nav-link active" id="tab-dosen-pengajar" data-bs-toggle="tab"
                    data-bs-target="#tab-pane-dosen-pengajar" type="button" role="tab"
                    aria-controls="tab-pane-dosen-pengajar" aria-selected="true">
                    Dosen Pengajar
                </button>
            </li>
            <li class="nav-item" style="flex: 1; text-align: center;">
                <button class="nav-link" id="tab-mahasiswa-krs" data-bs-toggle="tab"
                    data-bs-target="#tab-pane-mahasiswa-krs" type="button" role="tab" aria-controls="tab-pane-mahasiswa-krs"
                    aria-selected="false">
                    Mahasiswa KRS / Peserta Kelas
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
                    </div>
                    <div class="d-flex gap-2">
                        <button type="button" class="btn btn-outline-primary btn-sm disabled" disabled>
                            <i class="ri-percent-line me-1"></i> Hitung Persentase Bobot (SKS)
                        </button>
                        <button type="button" class="btn btn-primary btn-sm" data-bs-toggle="modal" data-bs-target="#modalDosen">
                            <i class="ri-add-line me-1"></i> Tambah Aktivitas Mengajar Dosen
                        </button>
                    </div>
                </div>

                <div class="table-responsive">
                    <table class="table table-striped table-hover align-middle">
                        <thead class="table-light">
                        <tr>
                            <th>Status</th>
                            <th>No</th>
                            <th>NIDN</th>
                            <th>NUPTK</th>
                            <th>Nama Dosen</th>
                            <th>Bobot (sks)</th>
                            <th>Rencana Pertemuan</th>
                            <th>Realisasi Pertemuan</th>
                            <th>Jenis Evaluasi</th>
                            <th>Aksi</th>
                        </tr>
                        </thead>
                        <tbody>
                        @forelse($kelasDosenRows as $index => $row)
                            <tr>
                                <td>
                                    @php
                                        $badgeClass = 'bg-label-secondary';
                                        $label = $row->status_sinkronisasi;

                                        if ($row->status_sinkronisasi === 'pending') {
                                            $badgeClass = 'bg-label-warning';
                                            $label = 'pending';
                                        } elseif ($row->status_sinkronisasi === 'synced') {
                                            $badgeClass = 'bg-label-success';
                                            $label = 'synced';
                                        } elseif ($row->status_sinkronisasi === 'updated_local') {
                                            $badgeClass = 'bg-label-primary';
                                            $label = 'updated_local';
                                        } elseif ($row->status_sinkronisasi === 'deleted_local') {
                                            $badgeClass = 'bg-label-danger';
                                            $label = 'deleted_local';
                                        }
                                    @endphp
                                    <span class="badge {{ $badgeClass }} rounded-pill text-lowercase">{{ $label }}</span>
                                </td>
                                <td>{{ $index + 1 }}</td>
                                <td>{{ $row->dosen->nidn ?? '-' }}</td>
                                <td>{{ $row->dosen->nip ?? '-' }}</td>
                                <td>{{ $row->dosen->nama ?? '-' }}</td>
                                <td>{{ number_format((float) $row->bobot_sks, 2) }}</td>
                                <td>{{ $row->jumlah_rencana_pertemuan }}</td>
                                <td>{{ $row->jumlah_realisasi_pertemuan ?? '-' }}</td>
                                <td>{{ $jenisEvaluasiOptions[(string) $row->jenis_evaluasi] ?? $row->jenis_evaluasi }}</td>
                                <td>
                                    @if($row->status_sinkronisasi !== 'deleted_local')
                                        <form action="{{ route('kelas.dosen.destroy', $row->id) }}" method="POST" class="d-inline">
                                            @csrf
                                            @method('DELETE')
                                            <button
                                                type="submit"
                                                class="btn btn-icon btn-sm btn-outline-danger"
                                                onclick="return confirm('Yakin menghapus dosen pengajar ini?')">
                                                <i class="ri-delete-bin-line"></i>
                                            </button>
                                        </form>
                                    @else
                                        <span class="text-muted">-</span>
                                    @endif
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
                    </table>
                </div>
            </div>

            {{-- TAB 2: Mahasiswa KRS / Peserta Kelas --}}
            <div class="tab-pane fade" id="tab-pane-mahasiswa-krs" role="tabpanel"
                 aria-labelledby="tab-mahasiswa-krs">
                <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center mb-3 gap-2">
                    <div class="flex-grow-1">
                        <label class="form-label mb-1">Pilih NIM / Nama Mahasiswa</label>
                        <select class="form-select" disabled>
                            <option selected>Pilih NIM / Nama Mahasiswa</option>
                        </select>
                    </div>
                    <div class="d-flex gap-2 mt-2 mt-md-0">
                        <button type="button" class="btn btn-primary btn-sm disabled" disabled>
                            <i class="ri-user-add-line me-1"></i> Tambah Peserta Kelas
                        </button>
                        <button type="button" class="btn btn-outline-secondary btn-sm disabled" disabled>
                            <i class="ri-group-line me-1"></i> Input Kolektif Peserta
                        </button>
                    </div>
                </div>

                <div class="table-responsive">
                    <table class="table table-striped table-hover align-middle">
                        <thead class="table-light">
                        <tr>
                            <th>Status</th>
                            <th>No</th>
                            <th>NIM</th>
                            <th>Nama Mahasiswa</th>
                            <th>Jenis Kelamin</th>
                            <th>Program Studi</th>
                            <th>Angkatan</th>
                            <th>Aksi</th>
                        </tr>
                        </thead>
                        <tbody>
                        @foreach($dummyMahasiswa as $index => $mhs)
                            <tr>
                                <td>
                                    @php
    $badgeClass = 'bg-label-secondary';
    $label = 'Unknown';
    if ($mhs['status'] === 'synced') {
        $badgeClass = 'bg-label-success';
        $label = 'sudah sync';
    } elseif ($mhs['status'] === 'local') {
        $badgeClass = 'bg-label-warning';
        $label = 'lokal';
    } elseif ($mhs['status'] === 'pending') {
        $badgeClass = 'bg-label-info';
        $label = 'perlu push';
    }
                                    @endphp
                                    <span class="badge {{ $badgeClass }} rounded-pill text-capitalize">
                                        {{ $label }}
                                    </span>
                                </td>
                                <td>{{ $index + 1 }}</td>
                                <td>{{ $mhs['nim'] }}</td>
                                <td>{{ $mhs['nama'] }}</td>
                                <td>{{ $mhs['jk'] }}</td>
                                <td>{{ $mhs['prodi'] }}</td>
                                <td>{{ $mhs['angkatan'] }}</td>
                                <td>
                                    <button type="button" class="btn btn-icon btn-sm btn-outline-danger" disabled>
                                        <i class="ri-delete-bin-line"></i>
                                    </button>
                                </td>
                            </tr>
                        @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

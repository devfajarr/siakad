@extends('layouts.app')

@section('title', 'Manajemen Jadwal Terpadu')

@push('styles')
    <link rel="stylesheet" href="{{ asset('assets/vendor/libs/select2/select2.css') }}" />
@endpush

@section('content')
    <h4 class="fw-bold py-3 mb-4">
        <span class="text-muted fw-light">Persiapan Perkuliahan /</span> Manajemen Jadwal Terpadu
    </h4>

    {{-- Header Filter dan Aksi --}}
    <div class="card mb-4">
        <div class="card-body d-flex flex-column flex-md-row justify-content-between align-items-center gap-3">
            <form action="{{ route('admin.jadwal-global.index') }}" method="GET"
                class="d-flex align-items-center gap-3 w-100 w-md-auto" id="formFilterHari">
                <label for="hariFilter" class="form-label mb-0 fw-bold text-nowrap">Tampilkan Hari:</label>
                <select name="hari" id="hariFilter" class="form-select flex-grow-1"
                    onchange="document.getElementById('formFilterHari').submit();">
                    @php
                        $hariMap = [
                            1 => 'Senin',
                            2 => 'Selasa',
                            3 => 'Rabu',
                            4 => 'Kamis',
                            5 => 'Jumat',
                            6 => 'Sabtu',
                            7 => 'Minggu',
                        ];
                    @endphp
                    @foreach ($hariMap as $key => $label)
                        <option value="{{ $key }}" {{ $hariFilter == $key ? 'selected' : '' }}>
                            {{ $label }}
                        </option>
                    @endforeach
                </select>
            </form>

            <button type="button" class="btn btn-primary text-nowrap" data-bs-toggle="modal"
                data-bs-target="#modalJadwalGlobal">
                <i class="ri-add-line me-1"></i> Tambah Jadwal Baru
            </button>
        </div>
    </div>

    {{-- Notifikasi Error/Success --}}
    @if (session('success'))
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            <i class="ri-check-line me-2"></i>{{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif

    @if (session('error'))
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            <i class="ri-error-warning-line me-2"></i>{{ session('error') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif

    @if ($errors->any())
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            <ul class="mb-0">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif

    {{-- Matriks Visual Ruangan & Garis Waktu --}}
    <div class="row g-4">
        @forelse ($ruangs as $ruang)
            <div class="col-12">
                <div class="card h-100 border-0 shadow-sm">
                    <div class="row g-0 h-100 align-items-stretch">

                        {{-- Sisi Kiri: Label Ruangan --}}
                        <div
                            class="col-md-3 col-xl-2 bg-label-primary p-3 d-flex flex-column justify-content-center align-items-center text-center rounded-start">
                            <i class="ri-building-4-line ri-2x mb-2 text-primary"></i>
                            <h6 class="fw-bold mb-1 text-primary">{{ $ruang->kode_ruang }}</h6>
                            <span class="text-body fw-medium small">{{ $ruang->nama_ruang }}</span>
                            <span class="badge bg-white text-primary mt-2 shadow-sm rounded-pill"><i
                                    class="ri-group-line me-1"></i> {{ $ruang->kapasitas }} Kursi</span>
                        </div>

                        {{-- Sisi Kanan: Time Blocks (Jadwal) --}}
                        <div class="col-md-9 col-xl-10 p-3 bg-white rounded-end border position-relative">
                            @php
                                // Ambil collection jadwal untuk ruangan ini berdasarkan grouping dari Controller
                                $jadwalRuangIni = $jadwalku->get($ruang->id, collect());
                            @endphp

                            @if ($jadwalRuangIni->isEmpty())
                                <div class="d-flex h-100 justify-content-center align-items-center opacity-50 py-3">
                                    <div class="text-center">
                                        <i class="ri-calendar-close-line ri-2x mb-2 text-muted"></i>
                                        <p class="mb-0">Tidak ada jadwal pada hari ini (Kosong)</p>
                                    </div>
                                </div>
                            @else
                                {{-- Barisan Timeline Berbasis Flexbox --}}
                                <div class="d-flex flex-row overflow-auto gap-3 pb-2 w-100" style="scrollbar-width: thin;">
                                    @foreach ($jadwalRuangIni as $jdwl)
                                        @php
                                            // Format waktu (HH:mm)
                                            $mulai = \Carbon\Carbon::parse($jdwl->jam_mulai)->format('H:i');
                                            $selesai = \Carbon\Carbon::parse($jdwl->jam_selesai)->format('H:i');
                                            $kelas = $jdwl->kelasKuliah;
                                            $mk = $kelas->mataKuliah ?? null;

                                            // Kumpulkan nama dosen
                                            $dosenNames = [];
                                            if ($kelas->dosenPengajars) {
                                                foreach ($kelas->dosenPengajars as $pengajar) {
                                                    $dosenNames[] = $pengajar->nama_admin_display;
                                                }
                                            }
                                            $dosenString = !empty($dosenNames) ? implode(', ', $dosenNames) : 'Belum Ada Dosen';
                                        @endphp

                                        <div class="card border border-info shadow-none flex-shrink-0"
                                            style="width: 280px; min-width: 250px;">
                                            <div
                                                class="card-header bg-label-info py-2 px-3 d-flex justify-content-between align-items-center">
                                                <span class="fw-bold text-info"><i class="ri-time-line me-1"></i> {{ $mulai }} -
                                                    {{ $selesai }}</span>
                                                <span
                                                    class="badge bg-info bg-opacity-10 text-info fw-bold">{{ $kelas->nama_kelas_kuliah }}</span>
                                            </div>
                                            <div class="card-body p-3">
                                                <h6 class="mb-1 fw-bold text-truncate"
                                                    title="{{ $mk->nama_mk ?? 'Matkul Tidak Ditemukan' }}">
                                                    {{ $mk->nama_mk ?? 'Matkul Tidak Ditemukan' }}
                                                </h6>
                                                <div class="text-muted small text-truncate mb-2" title="{{ $dosenString }}">
                                                    <i class="ri-user-voice-line me-1"></i>{{ $dosenString }}
                                                </div>
                                                <div class="d-flex justify-content-between align-items-center mt-3">
                                                    <span class="badge rounded-pill bg-label-secondary"><i
                                                            class="ri-macbook-line me-1"></i>
                                                        {{ $jdwl->jenis_pertemuan ?? 'Tatapan Muka' }}</span>
                                                    <div class="d-flex gap-1">
                                                        <a href="{{ route('admin.jadwal-global.edit', $jdwl->id) }}"
                                                            class="btn btn-sm btn-icon btn-outline-warning" title="Edit Jadwal">
                                                            <i class="ri-pencil-line"></i>
                                                        </a>
                                                        <a href="{{ route('admin.kelas-kuliah.show', $kelas->id) }}"
                                                            class="btn btn-sm btn-icon btn-outline-primary" title="Lihat Detail Kelas">
                                                            <i class="ri-external-link-line"></i>
                                                        </a>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    @endforeach
                                </div>
                            @endif
                        </div>

                    </div>
                </div>
            </div>
        @empty
            <div class="col-12">
                <div class="alert alert-warning text-center">
                    Belum ada data Master Ruangan di database.
                </div>
            </div>
        @endforelse
    </div>

    {{-- MODAL TAMBAH JADWAL GLOBAL --}}
    <div class="modal fade" id="modalJadwalGlobal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-lg modal-dialog-centered" role="document">
            <div class="modal-content">
                <div class="modal-header bg-primary">
                    <h5 class="modal-title text-white"><i class="ri-calendar-event-line me-2"></i>Tambah Jadwal Terpadu</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"
                        aria-label="Close"></button>
                </div>

                <form action="{{ route('admin.jadwal-global.store') }}" method="POST">
                    @csrf
                    <div class="modal-body">

                        <div class="mb-3">
                            <label class="form-label text-primary">Informasi Penting:</label>
                            <ul class="text-muted small mb-0 ps-3">
                                <li>Jadwal baru akan otomatis disimpan untuk <span class="fw-bold text-dark">Hari
                                        {{ $hariMap[(int) $hariFilter] }}</span>.</li>
                                <li>Sistem menolak simpan jika terjadi <span class="text-danger fw-bold">Bentrok
                                        Ruangan</span> atau <span class="text-danger fw-bold">Bentrok Dosen</span> pada
                                    irisan waktu tersebut.</li>
                            </ul>
                        </div>

                        <div class="row g-3">
                            <!-- Disembunyikan karena nilainya diambil otomatis dari filter UI -->
                            <input type="hidden" name="hari" value="{{ $hariFilter }}">

                            <div class="col-md-12">
                                <label class="form-label" for="semester_id_filter">Pilih Periode Semester <span
                                        class="text-danger">*</span></label>
                                <select id="semester_id_filter" class="form-select select2-semester" required>
                                    <option value="">-- Pilih Periode Semester --</option>
                                    @php $activeSmtId = getActiveSemesterId(); @endphp
                                    @foreach($semesters as $smt)
                                        <option value="{{ $smt->id_semester }}" {{ $activeSmtId == $smt->id_semester ? 'selected' : '' }}>
                                            {{ $smt->nama_semester }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>

                            <div class="col-md-12">
                                <label class="form-label" for="kelas_kuliah_id">Pilih Kelas Perkuliahan Aktif <span
                                        class="text-danger">*</span></label>
                                <select id="kelas_kuliah_id" name="kelas_kuliah_id" class="form-select select2-kelas"
                                    required disabled>
                                    <option value="">-- Pilih Semester Terlebih Dahulu --</option>
                                </select>
                                <div class="form-text">Kelas Perkuliahan akan muncul setelah Periode Semester dipilih.</div>
                            </div>

                            <div class="col-md-12">
                                <label class="form-label" for="ruang_id">Lokasi Ruangan <span
                                        class="text-danger">*</span></label>
                                <select id="ruang_id" name="ruang_id" class="form-select select2-ruangan" required>
                                    <option value="">-- Cari Ruangan --</option>
                                    @foreach($ruangs as $ruang)
                                        <option value="{{ $ruang->id }}">
                                            {{ $ruang->kode_ruang }} - {{ $ruang->nama_ruang }} (Kapasitas:
                                            {{ $ruang->kapasitas }})
                                        </option>
                                    @endforeach
                                </select>
                            </div>

                            <div class="col-md-6">
                                <label class="form-label" for="jam_mulai">Jam Mulai <span
                                        class="text-danger">*</span></label>
                                <input type="time" id="jam_mulai" name="jam_mulai" class="form-control" required>
                            </div>

                            <div class="col-md-6">
                                <label class="form-label" for="jam_selesai">Jam Selesai <span
                                        class="text-danger">*</span></label>
                                <input type="time" id="jam_selesai" name="jam_selesai" class="form-control" required>
                            </div>

                            <div class="col-md-12">
                                <label class="form-label" for="jenis_pertemuan">Jenis Pertemuan <span
                                        class="text-muted">(opsional)</span></label>
                                <input type="text" id="jenis_pertemuan" name="jenis_pertemuan" class="form-control"
                                    placeholder="Contoh: Teori, Praktikum Terpadu">
                            </div>
                        </div>

                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Batal</button>
                        <button type="submit" class="btn btn-primary"><i class="ri-save-line me-1"></i> Simpan
                            Jadwal</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
@endsection

@push('scripts')
    <script src="{{ asset('assets/vendor/libs/select2/select2.js') }}"></script>
    <script>
        $(document).ready(function () {
            // Inisialisasi Select2 untuk Modal Global
            $('.select2-semester').select2({
                dropdownParent: $('#modalJadwalGlobal'),
                placeholder: '-- Pilih Periode Semester --',
                width: '100%',
                minimumResultsForSearch: 7 // Search muncul jika data > 7
            });

            $('.select2-kelas').select2({
                dropdownParent: $('#modalJadwalGlobal'),
                placeholder: '-- Cari Kelas Perkuliahan --',
                width: '100%'
            });

            $('.select2-ruangan').select2({
                dropdownParent: $('#modalJadwalGlobal'),
                placeholder: '-- Cari Ruangan --',
                width: '100%'
            });

            // AJAX Dependent Dropdown for Kelas Kuliah
            $('#semester_id_filter').on('change', function () {
                var semesterId = $(this).val();
                var kelasSelect = $('#kelas_kuliah_id');

                // Reset Dropdown
                kelasSelect.empty().append('<option value="">-- Sedang memuat data... --</option>');
                kelasSelect.prop('disabled', true);

                if (semesterId) {
                    $.ajax({
                        url: '{{ route('admin.jadwal-global.kelas-by-semester') }}',
                        type: 'GET',
                        data: { semester_id: semesterId },
                        success: function (response) {
                            kelasSelect.empty().append('<option value="">-- Cari Kelas Perkuliahan --</option>');

                            $.each(response, function (index, kelas) {
                                kelasSelect.append('<option value="' + kelas.id + '">' + kelas.text + '</option>');
                            });

                            kelasSelect.prop('disabled', false);
                            // Refresh Select2 view
                            kelasSelect.trigger('change');
                        },
                        error: function () {
                            kelasSelect.empty().append('<option value="">-- Gagal memuat data --</option>');
                        }
                    });
                } else {
                    kelasSelect.empty().append('<option value="">-- Pilih Semester Terlebih Dahulu --</option>');
                    kelasSelect.prop('disabled', true);
                    kelasSelect.trigger('change');
                }
            });

            // Auto-trigger load kelas saat modal dibuka (karena semester sudah default selected)
            $('#modalJadwalGlobal').on('shown.bs.modal', function () {
                var currentSmt = $('#semester_id_filter').val();
                if (currentSmt) {
                    $('#semester_id_filter').trigger('change');
                }
            });

            // Prevent Select2 causing focus trap bugs inside Bootstrap Modal
            $.fn.modal.Constructor.prototype.enforceFocus = function () { };

            // Auto open modal on Validation Errors
            @if ($errors->any())
                var validationModal = new bootstrap.Modal(document.getElementById('modalJadwalGlobal'));
                validationModal.show();
            @endif
                                    });
    </script>
@endpush
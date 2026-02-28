@extends('layouts.app')

@section('title', 'Manajemen Jadwal Ujian')

@section('content')
    <div class="container-xxl flex-grow-1 container-p-y">
        <div class="row mb-4 align-items-center">
            <div class="col-sm-6">
                <h4 class="mb-0 fw-bold">Manajemen Jadwal Ujian</h4>
                <p class="text-muted mb-0">Kelola jadwal UTS/UAS per kelas kuliah dan generate peserta ujian.</p>
            </div>
            <div class="col-sm-6 text-sm-end mt-3 mt-sm-0">
                <a href="{{ route('admin.ujian.permintaan-cetak', ['id_semester' => $idSemester]) }}"
                    class="btn btn-outline-warning">
                    <i class="ri-printer-line me-1"></i> Permintaan Cetak
                    @if($permintaanCetakCount > 0)
                        <span class="badge bg-danger ms-1">{{ $permintaanCetakCount }}</span>
                    @endif
                </a>
                <button type="button" class="btn btn-primary ms-2" data-bs-toggle="modal"
                    data-bs-target="#modalTambahJadwal">
                    <i class="ri-add-line me-1"></i> Tambah Jadwal
                </button>
            </div>
        </div>

        <!-- Alert Notifikasi -->
        @if(session('success'))
            <div class="alert alert-success alert-dismissible fade show" role="alert">
                <i class="ri-checkbox-circle-line me-2"></i>{{ session('success') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        @endif
        @if(session('error'))
            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                <i class="ri-error-warning-line me-2"></i>{{ session('error') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        @endif

        <!-- Info Kelayakan Ujian -->
        <div class="alert alert-info d-flex align-items-center mb-4 shadow-sm border-0" role="alert">
            <i class="ri-information-line fs-4 me-3 text-info"></i>
            <div>
                <strong>Syarat Kelayakan Peserta Ujian:</strong> Mahasiswa dinyatakan <b>Layak</b> mengikuti ujian jika
                status <b>KRS telah di-ACC</b> dan persentase kehadiran mencapai minimal
                <b>{{ config('academic.min_persentase_ujian', 75) }}%</b> dari target
                <b>{{ config('academic.target_pertemuan_per_blok', 7) }} pertemuan</b> per blok ujian (UTS mengacu pada
                pertemuan 1-7, UAS mengacu pada pertemuan 8-14).
            </div>
        </div>

        <!-- Filter Semester -->
        <div class="card mb-4 shadow-sm border-0">
            <div class="card-body py-3">
                <form action="{{ route('admin.ujian.index') }}" method="GET" class="row align-items-center">
                    <div class="col-auto">
                        <label class="form-label mb-0 fw-bold">Semester:</label>
                    </div>
                    <div class="col-auto">
                        <select name="id_semester" class="form-select" onchange="this.form.submit()">
                            @foreach($semesters as $sem)
                                <option value="{{ $sem->id_semester }}" {{ $idSemester == $sem->id_semester ? 'selected' : '' }}>
                                    {{ $sem->nama_semester }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                </form>
            </div>
        </div>

        <!-- Tabel Jadwal Ujian -->
        <div class="card shadow-sm border-0">
            <div class="card-body">
                <div class="table-responsive text-nowrap">
                    <table class="table table-hover table-striped" id="jadwalUjianTable">
                        <thead class="table-light">
                            <tr>
                                <th width="5%" class="text-center">No</th>
                                <th>Mata Kuliah</th>
                                <th>Kelas</th>
                                <th>Ruangan</th>
                                <th class="text-center">Tipe Ujian</th>
                                <th>Tanggal</th>
                                <th class="text-center">Jam</th>
                                <th class="text-center">Waktu</th>
                                <th class="text-center">Peserta</th>
                                <th class="text-center" width="15%">Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($jadwalUjians as $index => $jadwal)
                                <tr>
                                    <td class="text-center">{{ $index + 1 }}</td>
                                    <td>
                                        <div class="d-flex flex-column">
                                            <span class="fw-bold">{{ $jadwal->kelasKuliah->mataKuliah->nama_mk ?? '-' }}</span>
                                            <small
                                                class="text-muted">{{ $jadwal->kelasKuliah->mataKuliah->kode_mk ?? '' }}</small>
                                        </div>
                                    </td>
                                    <td>{{ $jadwal->kelasKuliah->nama_kelas_kuliah ?? '-' }}</td>
                                    <td>{{ $jadwal->ruang->nama_ruang ?? '-' }}</td>
                                    <td class="text-center">
                                        @if($jadwal->tipe_ujian === 'UTS')
                                            <span class="badge bg-info">UTS</span>
                                        @else
                                            <span class="badge bg-primary">UAS</span>
                                        @endif
                                    </td>
                                    <td>{{ $jadwal->tanggal_ujian->format('d M Y') }}</td>
                                    <td class="text-center">
                                        {{ \Carbon\Carbon::parse($jadwal->jam_mulai)->format('H:i') }} -
                                        {{ \Carbon\Carbon::parse($jadwal->jam_selesai)->format('H:i') }}
                                    </td>
                                    <td class="text-center">
                                        @if($jadwal->tipe_waktu === 'Pagi')
                                            <span class="badge bg-label-warning">Pagi</span>
                                        @elseif($jadwal->tipe_waktu === 'Sore')
                                            <span class="badge bg-label-info">Sore</span>
                                        @else
                                            <span class="badge bg-label-secondary">Universal</span>
                                        @endif
                                    </td>
                                    <td class="text-center">
                                        @if($jadwal->peserta_ujians_count > 0)
                                            <a href="{{ route('admin.ujian.peserta', $jadwal->id) }}" class="text-decoration-none">
                                                <span class="badge bg-success">{{ $jadwal->peserta_eligible_count }}</span> /
                                                <span class="badge bg-secondary">{{ $jadwal->peserta_ujians_count }}</span>
                                            </a>
                                        @else
                                            <span class="text-muted">-</span>
                                        @endif
                                    </td>
                                    <td class="text-center">
                                        <div class="btn-group btn-group-sm">
                                            <form action="{{ route('admin.ujian.generate-peserta', $jadwal->id) }}"
                                                method="POST" class="d-inline"
                                                onsubmit="return confirm('Generate/update peserta ujian untuk kelas ini?');">
                                                @csrf
                                                <button type="submit" class="btn btn-sm btn-outline-success"
                                                    data-bs-toggle="tooltip" title="Generate Peserta">
                                                    <i class="ri-user-add-line"></i>
                                                </button>
                                            </form>
                                            <button type="button" class="btn btn-sm btn-outline-primary btn-edit-jadwal"
                                                data-id="{{ $jadwal->id }}"
                                                data-kelas-kuliah-id="{{ $jadwal->kelas_kuliah_id }}"
                                                data-ruang-id="{{ $jadwal->ruang_id }}"
                                                data-id-semester="{{ $jadwal->id_semester }}"
                                                data-tipe-ujian="{{ $jadwal->tipe_ujian }}"
                                                data-tanggal-ujian="{{ $jadwal->tanggal_ujian->format('Y-m-d') }}"
                                                data-jam-mulai="{{ \Carbon\Carbon::parse($jadwal->jam_mulai)->format('H:i') }}"
                                                data-jam-selesai="{{ \Carbon\Carbon::parse($jadwal->jam_selesai)->format('H:i') }}"
                                                data-tipe-waktu="{{ $jadwal->tipe_waktu }}"
                                                data-keterangan="{{ $jadwal->keterangan }}" data-bs-toggle="tooltip"
                                                title="Edit">
                                                <i class="ri-pencil-line"></i>
                                            </button>
                                            <form action="{{ route('admin.ujian.destroy', $jadwal->id) }}" method="POST"
                                                class="d-inline"
                                                onsubmit="return confirm('Yakin hapus jadwal ini? Peserta ujian terkait juga akan dihapus.');">
                                                @csrf @method('DELETE')
                                                <button type="submit" class="btn btn-sm btn-outline-danger"
                                                    data-bs-toggle="tooltip" title="Hapus">
                                                    <i class="ri-delete-bin-line"></i>
                                                </button>
                                            </form>
                                        </div>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <!-- Modal Tambah Jadwal Ujian -->
    <div class="modal fade" id="modalTambahJadwal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog">
            <form action="{{ route('admin.ujian.store') }}" method="POST">
                @csrf
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title">Tambah Jadwal Ujian</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        <input type="hidden" name="id_semester" value="{{ $idSemester }}">
                        <div class="mb-3">
                            <label class="form-label">Kelas Kuliah <span class="text-danger">*</span></label>
                            <select name="kelas_kuliah_id" id="kelas_kuliah_id" class="form-select select2" required>
                                <option value="">-- Pilih Kelas --</option>
                                @foreach($kelasKuliahs as $kk)
                                    <option value="{{ $kk->id }}">
                                        {{ $kk->mataKuliah->kode_mk ?? '' }} - {{ $kk->mataKuliah->nama_mk ?? '' }}
                                        ({{ $kk->nama_kelas_kuliah }})
                                    </option>
                                @endforeach
                            </select>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Ruangan <span class="text-danger">*</span></label>
                            <select name="ruang_id" id="ruang_id" class="form-select select2" required>
                                <option value="">-- Pilih Ruangan --</option>
                                @foreach($ruangs as $ruang)
                                    <option value="{{ $ruang->id }}">
                                        {{ $ruang->nama_ruang }} (Kapasitas: {{ $ruang->kapasitas }})
                                    </option>
                                @endforeach
                            </select>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Tipe Ujian <span class="text-danger">*</span></label>
                            <select name="tipe_ujian" class="form-select" required>
                                <option value="UTS">UTS</option>
                                <option value="UAS">UAS</option>
                            </select>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Tanggal Ujian <span class="text-danger">*</span></label>
                            <input type="date" name="tanggal_ujian" class="form-control" required>
                        </div>
                        <div class="row">
                            <div class="col-6 mb-3">
                                <label class="form-label">Jam Mulai <span class="text-danger">*</span></label>
                                <input type="time" name="jam_mulai" class="form-control" required>
                            </div>
                            <div class="col-6 mb-3">
                                <label class="form-label">Jam Selesai <span class="text-danger">*</span></label>
                                <input type="time" name="jam_selesai" class="form-control" required>
                            </div>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Tipe Waktu <span class="text-danger">*</span></label>
                            <select name="tipe_waktu" class="form-select" required>
                                <option value="Universal">Universal</option>
                                <option value="Pagi">Pagi</option>
                                <option value="Sore">Sore</option>
                            </select>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Keterangan</label>
                            <textarea name="keterangan" class="form-control" rows="2" placeholder="Opsional..."></textarea>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-label-secondary" data-bs-dismiss="modal">Batal</button>
                        <button type="submit" class="btn btn-primary">Simpan</button>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <!-- Modal Edit Jadwal Ujian -->
    <div class="modal fade" id="modalEditJadwal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog">
            <form id="formEditJadwal" method="POST">
                @csrf @method('PUT')
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title">Edit Jadwal Ujian</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        <input type="hidden" name="id_semester" id="edit_id_semester">
                        <div class="mb-3">
                            <label class="form-label">Kelas Kuliah <span class="text-danger">*</span></label>
                            <select name="kelas_kuliah_id" id="edit_kelas_kuliah_id" class="form-select select2" required>
                                <option value="">-- Pilih Kelas --</option>
                                @foreach($kelasKuliahs as $kk)
                                    <option value="{{ $kk->id }}">
                                        {{ $kk->mataKuliah->kode_mk ?? '' }} - {{ $kk->mataKuliah->nama_mk ?? '' }}
                                        ({{ $kk->nama_kelas_kuliah }})
                                    </option>
                                @endforeach
                            </select>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Ruangan <span class="text-danger">*</span></label>
                            <select name="ruang_id" id="edit_ruang_id" class="form-select select2" required>
                                <option value="">-- Pilih Ruangan --</option>
                                @foreach($ruangs as $ruang)
                                    <option value="{{ $ruang->id }}">
                                        {{ $ruang->nama_ruang }} (Kapasitas: {{ $ruang->kapasitas }})
                                    </option>
                                @endforeach
                            </select>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Tipe Ujian <span class="text-danger">*</span></label>
                            <select name="tipe_ujian" id="edit_tipe_ujian" class="form-select" required>
                                <option value="UTS">UTS</option>
                                <option value="UAS">UAS</option>
                            </select>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Tanggal Ujian <span class="text-danger">*</span></label>
                            <input type="date" name="tanggal_ujian" id="edit_tanggal_ujian" class="form-control" required>
                        </div>
                        <div class="row">
                            <div class="col-6 mb-3">
                                <label class="form-label">Jam Mulai <span class="text-danger">*</span></label>
                                <input type="time" name="jam_mulai" id="edit_jam_mulai" class="form-control" required>
                            </div>
                            <div class="col-6 mb-3">
                                <label class="form-label">Jam Selesai <span class="text-danger">*</span></label>
                                <input type="time" name="jam_selesai" id="edit_jam_selesai" class="form-control" required>
                            </div>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Tipe Waktu <span class="text-danger">*</span></label>
                            <select name="tipe_waktu" id="edit_tipe_waktu" class="form-select" required>
                                <option value="Universal">Universal</option>
                                <option value="Pagi">Pagi</option>
                                <option value="Sore">Sore</option>
                            </select>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Keterangan</label>
                            <textarea name="keterangan" id="edit_keterangan" class="form-control" rows="2"></textarea>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-label-secondary" data-bs-dismiss="modal">Batal</button>
                        <button type="submit" class="btn btn-primary">Perbarui</button>
                    </div>
                </div>
            </form>
        </div>
    </div>
@endsection

@push('styles')
    <link rel="stylesheet" href="{{ asset('assets/vendor/libs/datatables-bs5/datatables.bootstrap5.css') }}" />
    <link rel="stylesheet" href="{{ asset('assets/vendor/libs/select2/select2.css') }}" />
@endpush

@push('scripts')
    <script src="{{ asset('assets/vendor/libs/datatables-bs5/datatables-bootstrap5.js') }}"></script>
    <script src="{{ asset('assets/vendor/libs/select2/select2.js') }}"></script>
    <script>
        $(document).ready(function () {
            $('#jadwalUjianTable').DataTable({
                responsive: false,
                scrollX: true,
                language: {
                    search: "Cari:",
                    searchPlaceholder: "Cari jadwal ujian...",
                    lengthMenu: "Tampilkan _MENU_ data",
                    zeroRecords: "Belum ada jadwal ujian untuk semester ini.",
                    info: "Menampilkan _START_ - _END_ dari _TOTAL_ data",
                    infoEmpty: "Tidak ada data",
                    infoFiltered: "(difilter dari _MAX_ total data)",
                    paginate: { first: "Awal", last: "Akhir", next: "\u203A", previous: "\u2039" }
                },
                order: [[5, 'asc']],
                columnDefs: [
                    { orderable: false, targets: [9] }
                ]
            });

            // Inisialisasi Select2
            $('#kelas_kuliah_id, #ruang_id').select2({
                dropdownParent: $('#modalTambahJadwal')
            });

            $('#edit_kelas_kuliah_id, #edit_ruang_id').select2({
                dropdownParent: $('#modalEditJadwal')
            });

            // Edit Modal
            $(document).on('click', '.btn-edit-jadwal', function () {
                var data = $(this).data();
                var url = "{{ route('admin.ujian.update', ':id') }}".replace(':id', data.id);
                $('#formEditJadwal').attr('action', url);
                $('#edit_id_semester').val(data.idSemester);
                $('#edit_kelas_kuliah_id').val(data.kelasKuliahId).trigger('change');
                $('#edit_ruang_id').val(data.ruangId).trigger('change');
                $('#edit_tipe_ujian').val(data.tipeUjian);
                $('#edit_tanggal_ujian').val(data.tanggalUjian);
                $('#edit_jam_mulai').val(data.jamMulai);
                $('#edit_jam_selesai').val(data.jamSelesai);
                $('#edit_tipe_waktu').val(data.tipeWaktu);
                $('#edit_keterangan').val(data.keterangan);
                $('#modalEditJadwal').modal('show');
            });
        });
    </script>
@endpush
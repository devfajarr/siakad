<div class="d-flex justify-content-between align-items-center mb-3">
    <div class="fw-semibold text-muted">
        Daftar sesi penjadwalan waktu dan ruang untuk penyelenggaraan kelas ini.
    </div>
    <div class="d-flex gap-2">
        <button type="button" class="btn btn-primary btn-sm" data-bs-toggle="modal" data-bs-target="#modalJadwal">
            <i class="ri-add-line me-1"></i> Tambah Jadwal
        </button>
    </div>
</div>

<div class="table-responsive text-nowrap mt-2">
    <table class="table table-bordered table-striped table-hover align-middle">
        <thead class="table-light">
            <tr class="text-nowrap text-center">
                <th>No</th>
                <th>Hari</th>
                <th>Waktu Penyelenggaraan</th>
                <th>Daftar Durasi</th>
                <th>Ruangan</th>
                <th>Kapasitas Ruang</th>
                <th>Aksi</th>
            </tr>
        </thead>
        <tbody>
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
            @forelse ($kelasKuliah->jadwalKuliahs as $index => $jadwal)
                <tr class="text-center">
                    <td>{{ $index + 1 }}</td>
                    <td><span class="fw-semibold text-primary">{{ $hariMap[$jadwal->hari] ?? 'Tidak Valid' }}</span></td>
                    <td>
                        <span class="badge bg-label-info px-3 py-2">
                            <i class="ri-time-line me-1"></i>
                            {{ \Carbon\Carbon::parse($jadwal->jam_mulai)->format('H:i') }} -
                            {{ \Carbon\Carbon::parse($jadwal->jam_selesai)->format('H:i') }}
                        </span>
                    </td>
                    <td>{{ $jadwal->jenis_pertemuan ?? '-' }}</td>
                    <td class="text-start">
                        @if($jadwal->ruang)
                            <span class="fw-bold">{{ $jadwal->ruang->kode_ruang }}</span> - {{ $jadwal->ruang->nama_ruang }}
                        @else
                            <span class="text-danger">-</span>
                        @endif
                    </td>
                    <td>{{ $jadwal->ruang->kapasitas ?? 0 }} Kursi</td>
                    <td>
                        <div class="d-flex justify-content-center gap-1">
                            <form action="{{ route('admin.jadwal-kuliah.destroy', $jadwal->id) }}" method="POST"
                                class="d-inline">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="btn btn-icon btn-sm btn-outline-danger" title="Hapus Jadwal"
                                    onclick="return confirm('Yakin ingin menghapus jadwal ini?')">
                                    <i class="ri-delete-bin-line"></i>
                                </button>
                            </form>
                        </div>
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="7" class="text-center text-muted py-4">
                        <i class="ri-calendar-close-line d-block mb-2" style="font-size: 2rem;"></i>
                        Belum ada jadwal yang dialokasikan untuk kelas ini.
                    </td>
                </tr>
            @endforelse
        </tbody>
    </table>
</div>

{{-- MODAL TAMBAH JADWAL --}}
<div class="modal fade" id="modalJadwal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title"><i class="ri-calendar-event-line me-2"></i>Tambah Jadwal Kelas</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form action="{{ route('admin.jadwal-kuliah.store') }}" method="POST">
                @csrf
                <input type="hidden" name="kelas_kuliah_id" value="{{ $kelasKuliah->id }}">

                <div class="modal-body">

                    <div class="mb-3">
                        <label class="form-label text-primary">Informasi Penting:</label>
                        <ul class="text-muted small mb-0 ps-3">
                            <li>Sistem otomatis menolak simpan jika terjadi <b>Bentrok Ruangan</b> atau <b>Bentrok Dosen
                                    Pengajar</b> pada jam yang sama lintas seluruh jadwal kuliah.</li>
                        </ul>
                    </div>

                    <div class="row g-3">
                        <div class="col-md-12">
                            <label class="form-label" for="hari">Hari <span class="text-danger">*</span></label>
                            <select id="hari" name="hari" class="form-select" required>
                                <option value="">-- Pilih Hari --</option>
                                <option value="1">Senin</option>
                                <option value="2">Selasa</option>
                                <option value="3">Rabu</option>
                                <option value="4">Kamis</option>
                                <option value="5">Jumat</option>
                                <option value="6">Sabtu</option>
                                <option value="7">Minggu</option>
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
                            <label class="form-label" for="ruang_id">Ruangan <span class="text-danger">*</span></label>
                            <select id="ruang_id" name="ruang_id" class="form-select select2-ruangan" required>
                                <option value="">-- Cari Ruangan --</option>
                                @foreach($daftarRuang ?? [] as $ruang)
                                    <option value="{{ $ruang->id }}">
                                        {{ $ruang->kode_ruang }} - {{ $ruang->nama_ruang }} (Kapasitas:
                                        {{ $ruang->kapasitas }})
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        <div class="col-md-6">
                            <label class="form-label" for="jenis_pertemuan">Jenis Pertemuan <span
                                    class="text-muted">(opsional)</span></label>
                            <input type="text" id="jenis_pertemuan" name="jenis_pertemuan" class="form-control"
                                placeholder="Contoh: Teori, Praktikum">
                        </div>

                        <div class="col-md-6">
                            <label class="form-label" for="tipe_waktu">Tipe Waktu/Shift <span
                                    class="text-danger">*</span></label>
                            <select id="tipe_waktu" name="tipe_waktu" class="form-select" required>
                                <option value="Universal">Universal (Pagi & Sore)</option>
                                <option value="Pagi">Kelas Pagi</option>
                                <option value="Sore">Kelas Sore / Karyawan</option>
                            </select>
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
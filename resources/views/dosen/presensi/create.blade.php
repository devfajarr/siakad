@extends('layouts.app')

@section('title', 'Tambah Presensi & Jurnal')

@section('content')
    <div class="container-xxl flex-grow-1 container-p-y">
        <form action="{{ route('dosen.presensi.store', $kelasKuliah->id) }}" method="POST">
            @csrf
            <div class="row mb-4">
                <div class="col-12">
                    <nav aria-label="breadcrumb">
                        <ol class="breadcrumb">
                            <li class="breadcrumb-item"><a href="{{ route('dosen.kelas.index') }}">Daftar Kelas</a></li>
                            <li class="breadcrumb-item"><a
                                    href="{{ route('dosen.presensi.index', $kelasKuliah->id) }}">Presensi</a></li>
                            <li class="breadcrumb-item active">Tambah Pertemuan</li>
                        </ol>
                    </nav>
                    <div class="d-flex justify-content-between align-items-center">
                        <h4 class="fw-bold mb-0">Input Presensi Pertemuan Ke-{{ $pertemuanKe }}</h4>
                        <div class="d-flex gap-2">
                            <a href="{{ route('dosen.presensi.index', $kelasKuliah->id) }}"
                                class="btn btn-label-secondary">Batal</a>
                            <button type="submit" class="btn btn-primary">Simpan Presensi</button>
                        </div>
                    </div>
                </div>
            </div>

            <div class="row">
                <!-- Jurnal Perkuliahan -->
                <div class="col-md-4">
                    <div class="card mb-4 border-0 shadow-sm">
                        <div class="card-header">
                            <h5 class="card-title mb-0">Informasi Jurnal</h5>
                        </div>
                        <div class="card-body">
                            <input type="hidden" name="pertemuan_ke" value="{{ $pertemuanKe }}">

                            <div class="mb-3">
                                <label class="form-label">Tanggal</label>
                                <input type="date" name="tanggal" class="form-control" value="{{ date('Y-m-d') }}" required>
                            </div>

                            <div class="row">
                                <div class="col-6">
                                    <div class="mb-3">
                                        <label class="form-label">Jam Mulai</label>
                                        <input type="time" name="jam_mulai" class="form-control"
                                            value="{{ $defaultJamMulai }}" required>
                                    </div>
                                </div>
                                <div class="col-6">
                                    <div class="mb-3">
                                        <label class="form-label">Jam Selesai</label>
                                        <input type="time" name="jam_selesai" class="form-control"
                                            value="{{ $defaultJamSelesai }}" required>
                                    </div>
                                </div>
                            </div>

                            <div class="mb-3">
                                <label class="form-label">Metode Pembelajaran</label>
                                <select name="metode_pembelajaran" class="form-select" required>
                                    <option value="Luring">Luring (Tatap Muka)</option>
                                    <option value="Daring">Daring (Online)</option>
                                </select>
                            </div>

                            <div class="mb-0">
                                <label class="form-label">Materi / Bahasan Kuliah</label>
                                <textarea name="materi" class="form-control" rows="5"
                                    placeholder="Tuliskan pokok bahasan pertemuan ini..."
                                    required>{{ old('materi') }}</textarea>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Daftar Mahasiswa -->
                <div class="col-md-8">
                    <div class="card border-0 shadow-sm">
                        <div class="card-header d-flex justify-content-between align-items-center">
                            <h5 class="card-title mb-0">Daftar Kehadiran Mahasiswa</h5>
                            <small class="text-muted">{{ $kelasKuliah->peserta_kelas_kuliah_count }} Mahasiswa
                                Terdaftar</small>
                        </div>
                        <div class="table-responsive">
                            <table class="table table-hover align-middle mb-0">
                                <thead class="table-light">
                                    <tr>
                                        <th style="width: 50px;">No</th>
                                        <th>Mahasiswa</th>
                                        <th class="text-center">Status Kehadiran</th>
                                        <th>Keterangan</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse($kelasKuliah->pesertaKelasKuliah as $index => $peserta)
                                        @php
                                            $riwayat = $peserta->riwayatPendidikan;
                                            $mahasiswa = $riwayat->mahasiswa;
                                        @endphp
                                        <tr>
                                            <td>{{ $index + 1 }}</td>
                                            <td>
                                                <div class="fw-semibold">{{ $mahasiswa->nama_mahasiswa }}</div>
                                                <small class="text-muted">{{ $riwayat->nim }}</small>
                                            </td>
                                            <td>
                                                <div class="d-flex justify-content-center gap-3">
                                                    <div class="form-check form-check-inline me-0">
                                                        <input class="form-check-input" type="radio"
                                                            name="presensi[{{ $riwayat->id }}]" id="h_{{ $riwayat->id }}"
                                                            value="H" checked>
                                                        <label class="form-check-label text-success fw-bold"
                                                            for="h_{{ $riwayat->id }}">H</label>
                                                    </div>
                                                    <div class="form-check form-check-inline me-0">
                                                        <input class="form-check-input" type="radio"
                                                            name="presensi[{{ $riwayat->id }}]" id="s_{{ $riwayat->id }}"
                                                            value="S">
                                                        <label class="form-check-label text-warning fw-bold"
                                                            for="s_{{ $riwayat->id }}">S</label>
                                                    </div>
                                                    <div class="form-check form-check-inline me-0">
                                                        <input class="form-check-input" type="radio"
                                                            name="presensi[{{ $riwayat->id }}]" id="i_{{ $riwayat->id }}"
                                                            value="I">
                                                        <label class="form-check-label text-info fw-bold"
                                                            for="i_{{ $riwayat->id }}">I</label>
                                                    </div>
                                                    <div class="form-check form-check-inline me-0">
                                                        <input class="form-check-input" type="radio"
                                                            name="presensi[{{ $riwayat->id }}]" id="a_{{ $riwayat->id }}"
                                                            value="A">
                                                        <label class="form-check-label text-danger fw-bold"
                                                            for="a_{{ $riwayat->id }}">A</label>
                                                    </div>
                                                </div>
                                            </td>
                                            <td>
                                                <input type="text" name="keterangan[{{ $riwayat->id }}]"
                                                    class="form-control form-control-sm" placeholder="Opsional...">
                                            </td>
                                        </tr>
                                    @empty
                                        <tr>
                                            <td colspan="4" class="text-center py-4 text-muted">Tidak ada mahasiswa terdaftar di
                                                kelas ini.</td>
                                        </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </form>
    </div>
@endsection
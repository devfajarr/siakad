@extends('layouts.app')

@section('title', 'Edit Presensi & Jurnal')

@section('content')
    <div class="container-xxl flex-grow-1 container-p-y">
        <form action="{{ route('dosen.presensi.update', $pertemuan->id) }}" method="POST">
            @csrf
            @method('PUT')
            <div class="row mb-4">
                <div class="col-12">
                    <nav aria-label="breadcrumb">
                        <ol class="breadcrumb">
                            <li class="breadcrumb-item"><a href="{{ route('dosen.kelas.index') }}">Daftar Kelas</a></li>
                            <li class="breadcrumb-item"><a
                                    href="{{ route('dosen.presensi.index', $pertemuan->kelasKuliah->id) }}">Presensi</a></li>
                            <li class="breadcrumb-item active">Edit Pertemuan</li>
                        </ol>
                    </nav>
                    <div class="d-flex justify-content-between align-items-center">
                        <h4 class="fw-bold mb-0">Edit Presensi Pertemuan Ke-{{ $pertemuan->pertemuan_ke }}</h4>
                        <div class="d-flex gap-2">
                            <a href="{{ route('dosen.presensi.index', $pertemuan->kelasKuliah->id) }}"
                                class="btn btn-label-secondary">Batal</a>
                            <button type="submit" class="btn btn-warning">Perbarui Presensi</button>
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
                            <div class="mb-3">
                                <label class="form-label">Tanggal</label>
                                <input type="date" name="tanggal" class="form-control" 
                                    value="{{ $pertemuan->tanggal->format('Y-m-d') }}" required>
                            </div>

                            <div class="row">
                                <div class="col-6">
                                    <div class="mb-3">
                                        <label class="form-label">Jam Mulai</label>
                                        <input type="time" name="jam_mulai" class="form-control"
                                            value="{{ substr($pertemuan->jam_mulai, 0, 5) }}" required>
                                    </div>
                                </div>
                                <div class="col-6">
                                    <div class="mb-3">
                                        <label class="form-label">Jam Selesai</label>
                                        <input type="time" name="jam_selesai" class="form-control"
                                            value="{{ substr($pertemuan->jam_selesai, 0, 5) }}" required>
                                    </div>
                                </div>
                            </div>

                            <div class="mb-3">
                                <label class="form-label">Metode Pembelajaran</label>
                                <select name="metode_pembelajaran" class="form-select" required>
                                    <option value="Luring" {{ $pertemuan->metode_pembelajaran == 'Luring' ? 'selected' : '' }}>Luring (Tatap Muka)</option>
                                    <option value="Daring" {{ $pertemuan->metode_pembelajaran == 'Daring' ? 'selected' : '' }}>Daring (Online)</option>
                                </select>
                            </div>

                            <div class="mb-0">
                                <label class="form-label">Materi / Bahasan Kuliah</label>
                                <textarea name="materi" class="form-control" rows="5"
                                    placeholder="Tuliskan pokok bahasan pertemuan ini..."
                                    required>{{ old('materi', $pertemuan->materi) }}</textarea>
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
                                    @foreach($kelasKuliah->pesertaKelasKuliah as $index => $peserta)
                                        @php
                                            $riwayat = $peserta->riwayatPendidikan;
                                            $mahasiswa = $riwayat->mahasiswa;
                                            $presensi = $pertemuan->presensiMahasiswas->firstWhere('riwayat_pendidikan_id', $riwayat->id);
                                            $status = $presensi->status_kehadiran ?? 'H';
                                        @endphp
                                        <tr>
                                            <td>{{ $index + 1 }}</td>
                                            <td>
                                                <div class="fw-semibold">{{ $mahasiswa->nama_mahasiswa }}</div>
                                                <small class="text-muted">{{ $riwayat->nim }}</small>
                                            </td>
                                            <td>
                                                <div class="d-flex justify-content-center gap-3">
                                                    @foreach(['H' => 'success', 'S' => 'warning', 'I' => 'info', 'A' => 'danger'] as $val => $color)
                                                        <div class="form-check form-check-inline me-0">
                                                            <input class="form-check-input" type="radio"
                                                                name="presensi[{{ $riwayat->id }}]" id="{{ strtolower($val) }}_{{ $riwayat->id }}"
                                                                value="{{ $val }}" {{ $status == $val ? 'checked' : '' }}>
                                                            <label class="form-check-label text-{{ $color }} fw-bold"
                                                                for="{{ strtolower($val) }}_{{ $riwayat->id }}">{{ $val }}</label>
                                                        </div>
                                                    @endforeach
                                                </div>
                                            </td>
                                            <td>
                                                <input type="text" name="keterangan[{{ $riwayat->id }}]"
                                                    class="form-control form-control-sm" 
                                                    value="{{ $presensi->keterangan ?? '' }}"
                                                    placeholder="Opsional...">
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </form>
    </div>
@endsection

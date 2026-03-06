@extends('layouts.app')
@section('title', 'Monitoring Perkuliahan & Rekap Honorer')

@push('styles')
    <link rel="stylesheet" href="{{ asset('assets/vendor/libs/datatables-bs5/datatables.bootstrap5.css') }}">
    <link rel="stylesheet" href="{{ asset('assets/vendor/libs/flatpickr/flatpickr.css') }}" />
@endpush

@section('content')
    <div class="row">
        <div class="col-12">

            <div class="card mb-4">
                <div class="card-header d-flex align-items-center justify-content-between">
                    <h5 class="mb-0">Monitoring Perkuliahan & Rekap Honorer</h5>

                    <form action="{{ route('admin.keuangan-modul.monitoring-perkuliahan.index') }}" method="GET"
                        class="d-flex align-items-center mb-0">
                        <div class="input-group">
                            <span class="input-group-text"><i class="ri-calendar-2-line"></i></span>
                            <input type="month" class="form-control" name="bulan_tahun" value="{{ $bulanTahunFilter }}"
                                onchange="this.form.submit()">
                            <a href="{{ route('admin.keuangan-modul.monitoring-perkuliahan.export', ['bulan_tahun' => $bulanTahunFilter]) }}"
                                class="btn btn-primary" title="Export Excel / Unduh Rekap">
                                <i class="ri-file-excel-2-line me-1"></i> Rekap Honorer
                            </a>
                        </div>
                    </form>
                </div>

                <div class="card-body">
                    <p class="text-muted"><i class="ri-information-line"></i> Tampilan monitoring yang dirangkum berdasarkan
                        aktivitas dosen bulanan. Sistem akan mengecualikan pertemuan mengajar yang belum di-verifikasi
                        (Disetujui) oleh Kaprodi dari perhitungan estimasi honor.</p>
                </div>

                <div class="table-responsive text-nowrap">
                    <table class="table table-hover table-bordered" id="tableMonitoring">
                        <thead class="table-light">
                            <tr>
                                <th width="50px">No</th>
                                <th>Nama Dosen</th>
                                <th>Total SKS</th>
                                <th class="text-center">Total Pertemuan</th>
                                <th class="text-center">Sah (Verified)</th>
                                <th class="text-center">Status Draft/Pending</th>
                                <th class="text-end text-primary">Estimasi Honor (Rp)</th>
                                <th>Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($dosenRekap as $i => $item)
                                <tr>
                                    <td>{{ $i + 1 }}</td>
                                    <td>
                                        <div class="d-flex align-items-center">
                                            <div class="avatar avatar-sm me-3">
                                                <span class="avatar-initial rounded-circle bg-label-primary"><i
                                                        class="ri-user-line"></i></span>
                                            </div>
                                            <div>
                                                <span class="fw-medium text-heading">{{ $item['nama_dosen'] }}</span>
                                            </div>
                                        </div>
                                    </td>
                                    <td>{{ $item['total_sks'] }} SKS</td>
                                    <td class="text-center"><span
                                            class="badge rounded-pill bg-label-info">{{ $item['total_pertemuan'] }} x
                                            Ngajar</span></td>
                                    <td class="text-center">
                                        <span class="badge rounded-pill bg-success"><i class="ri-check-line me-1"></i>
                                            {{ $item['total_terverifikasi'] }}</span>
                                    </td>
                                    <td class="text-center">
                                        @if($item['total_pending'] > 0)
                                            <span class="badge rounded-pill bg-danger"><i class="ri-error-warning-line me-1"></i>
                                                {{ $item['total_pending'] }} Ditahan</span>
                                        @else
                                            <span class="text-muted">-</span>
                                        @endif
                                    </td>
                                    <td class="text-end fw-bold text-primary">Rp
                                        {{ number_format($item['estimasi_honor'], 0, ',', '.') }}</td>
                                    <td>
                                        <button type="button" class="btn btn-sm btn-outline-info" data-bs-toggle="modal"
                                            data-bs-target="#detailModal{{ $item['id_dosen'] }}">
                                            <i class="ri-eye-line me-1"></i> Kelas
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

    <!-- Modals Detail Kelas -->
    @foreach ($dosenRekap as $item)
        <div class="modal fade" id="detailModal{{ $item['id_dosen'] }}" tabindex="-1" aria-hidden="true">
            <div class="modal-dialog modal-xl modal-dialog-centered" role="document">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title" id="detailModalTitle"><i class="ri-book-read-line me-2"></i> Detail Pengajaran:
                            {{ $item['nama_dosen'] }}</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body p-0">
                        <div class="table-responsive">
                            <table class="table table-sm table-striped">
                                <thead class="table-dark">
                                    <tr>
                                        <th>Mata Kuliah</th>
                                        <th>SKS</th>
                                        <th>Nama Kelas</th>
                                        <th>Total Pertemuan Kelas (Global)</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse($item['pengajaran_detail'] as $pk)
                                        @if($pk->kelasKuliah)
                                            <tr>
                                                <td>{{ $pk->kelasKuliah->mataKuliah->nama_mk ?? '-' }}</td>
                                                <td>{{ $pk->kelasKuliah->mataKuliah->sks_mata_kuliah ?? '-' }}</td>
                                                <td>{{ $pk->kelasKuliah->nama_kelas_kuliah ?? '-' }}</td>
                                                <td>
                                                    @php
                                                        // Jumlahkan presensi yang eksis di bulan berjalan
                                                        $pertemuanDosenIni = 0;
                                                        foreach ($pk->kelasKuliah->presensiPertemuans as $p) {
                                                            if ($p->id_dosen === $item['id_dosen']) {
                                                                $pertemuanDosenIni++;
                                                            }
                                                        }
                                                    @endphp
                                                    {{ $pertemuanDosenIni }} Pertemuan
                                                </td>
                                            </tr>
                                        @endif
                                    @empty
                                        <tr>
                                            <td colspan="4" class="text-center text-muted">Belum ada detail pengajaran real yang
                                                tercatat di riwayat server.</td>
                                        </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Tutup</button>
                    </div>
                </div>
            </div>
        </div>
    @endforeach

@endsection

@push('scripts')
    <script src="{{ asset('assets/vendor/libs/datatables-bs5/datatables-bootstrap5.js') }}"></script>
    <script>
        $(document).ready(function () {
            $('#tableMonitoring').DataTable({
                "pageLength": 25,
                "order": []
            });
        });
    </script>
@endpush
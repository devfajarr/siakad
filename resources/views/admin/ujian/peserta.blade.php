@extends('layouts.app')

@section('title', 'Peserta Ujian - ' . ($jadwal->tipe_ujian ?? ''))

@section('content')
    <div class="container-xxl flex-grow-1 container-p-y">
        <div class="row mb-4 align-items-center">
            <div class="col-sm-6">
                <h4 class="mb-0 fw-bold">Peserta Ujian</h4>
                <p class="text-muted mb-0">
                    {{ $jadwal->kelasKuliah->mataKuliah->nama_mk ?? '-' }}
                    &mdash; {{ $jadwal->tipe_ujian }}
                </p>
            </div>
            <div class="col-sm-6 text-sm-end mt-3 mt-sm-0">
                <a href="{{ route('admin.ujian.index', ['id_semester' => $jadwal->id_semester]) }}"
                    class="btn btn-label-secondary">
                    <i class="ri-arrow-left-line me-1"></i> Kembali
                </a>
            </div>
        </div>

        <!-- Alert -->
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

        <!-- Info Card -->
        <div class="card bg-label-primary shadow-sm mb-4 border-0">
            <div class="card-body d-flex align-items-center justify-content-between flex-wrap gap-3">
                <div class="d-flex align-items-center gap-3">
                    <div class="avatar avatar-lg">
                        <span class="avatar-initial rounded bg-primary">
                            <i class="ri-file-list-3-line fs-2"></i>
                        </span>
                    </div>
                    <div>
                        <h6 class="mb-0 fw-bold">{{ $jadwal->kelasKuliah->mataKuliah->nama_mk ?? '-' }}</h6>
                        <small class="text-muted">
                            {{ $jadwal->kelasKuliah->nama_kelas_kuliah ?? '' }} |
                            {{ $jadwal->tanggal_ujian->format('d M Y') }} |
                            {{ \Carbon\Carbon::parse($jadwal->jam_mulai)->format('H:i') }} -
                            {{ \Carbon\Carbon::parse($jadwal->jam_selesai)->format('H:i') }}
                        </small>
                    </div>
                </div>
                <div class="d-flex gap-3">
                    <div class="text-center">
                        <p class="mb-0 text-muted small">Total</p>
                        <span class="badge bg-secondary fs-6">{{ $pesertaUjians->count() }}</span>
                    </div>
                    <div class="text-center">
                        <p class="mb-0 text-muted small">Layak</p>
                        <span class="badge bg-success fs-6">{{ $pesertaUjians->where('is_eligible', true)->count() }}</span>
                    </div>
                    <div class="text-center">
                        <p class="mb-0 text-muted small">Tidak Layak</p>
                        <span class="badge bg-danger fs-6">{{ $pesertaUjians->where('is_eligible', false)->count() }}</span>
                    </div>
                </div>
            </div>
        </div>

        <!-- Tabel Peserta -->
        <div class="card shadow-sm border-0">
            <div class="card-body">
                <div class="table-responsive text-nowrap">
                    <table class="table table-hover table-striped" id="pesertaUjianTable">
                        <thead class="table-light">
                            <tr>
                                <th width="5%" class="text-center">No</th>
                                <th>NIM</th>
                                <th>Nama Mahasiswa</th>
                                <th class="text-center">Hadir</th>
                                <th class="text-center">Persentase</th>
                                <th class="text-center">Kelayakan</th>
                                <th class="text-center">Status Cetak</th>
                                <th class="text-center" width="12%">Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($pesertaUjians as $index => $peserta)
                                @php
                                    $riwayat = $peserta->pesertaKelasKuliah->riwayatPendidikan ?? null;
                                    $mhs = $riwayat->mahasiswa ?? null;
                                @endphp
                                <tr>
                                    <td class="text-center">{{ $index + 1 }}</td>
                                    <td><span class="fw-bold text-primary">{{ $riwayat->nim ?? '-' }}</span></td>
                                    <td>{{ $mhs->nama_mahasiswa ?? '-' }}</td>
                                    <td class="text-center">
                                        {{ $peserta->jumlah_hadir }}/{{ config('academic.target_pertemuan') }}
                                    </td>
                                    <td class="text-center">
                                        <span
                                            class="badge bg-label-{{ $peserta->persentase_kehadiran >= 85 ? 'success' : 'danger' }}">
                                            {{ $peserta->persentase_kehadiran }}%
                                        </span>
                                    </td>
                                    <td class="text-center">
                                        @if($peserta->is_eligible)
                                            <span class="badge bg-success"><i class="ri-checkbox-circle-line me-1"></i>Layak</span>
                                        @elseif($peserta->is_dispensasi)
                                            <span class="badge bg-warning"><i class="ri-check-line me-1"></i>Dispensasi</span>
                                        @else
                                            <span class="badge bg-danger" data-bs-toggle="tooltip"
                                                title="{{ $peserta->keterangan_tidak_layak }}">
                                                <i class="ri-close-circle-line me-1"></i>Tidak Layak
                                            </span>
                                        @endif
                                    </td>
                                    <td class="text-center">
                                        @if($peserta->status_cetak === 'dicetak')
                                            <span class="badge bg-success"><i class="ri-printer-line me-1"></i>Dicetak</span>
                                        @elseif($peserta->status_cetak === 'diminta')
                                            <span class="badge bg-warning"><i class="ri-time-line me-1"></i>Diminta</span>
                                        @else
                                            <span class="badge bg-label-secondary">Belum</span>
                                        @endif
                                    </td>
                                    <td class="text-center">
                                        <div class="d-flex justify-content-center align-items-center gap-1">
                                            @if($peserta->can_print)
                                                <div class="btn-group btn-group-sm">
                                                    <a href="{{ route('admin.ujian.print-kartu', $peserta->id) }}" target="_blank"
                                                        class="btn btn-sm btn-outline-primary" data-bs-toggle="tooltip"
                                                        title="Cetak Kartu">
                                                        <i class="ri-printer-line"></i>
                                                    </a>
                                                    @if($peserta->status_cetak !== 'dicetak')
                                                        <form
                                                            action="{{ route('admin.ujian.mark-printed', [$jadwal->id, $peserta->id]) }}"
                                                            method="POST" class="d-inline">
                                                            @csrf
                                                            <button type="submit" class="btn btn-sm btn-outline-success"
                                                                data-bs-toggle="tooltip" title="Tandai Dicetak">
                                                                <i class="ri-checkbox-circle-line"></i>
                                                            </button>
                                                        </form>
                                                    @endif
                                                </div>
                                            @endif

                                            @if(!$peserta->is_eligible)
                                                <form
                                                    action="{{ route('admin.ujian.toggle-dispensasi', [$jadwal->id, $peserta->id]) }}"
                                                    method="POST" class="d-inline">
                                                    @csrf
                                                    <button type="submit"
                                                        class="btn btn-sm {{ $peserta->is_dispensasi ? 'btn-warning' : 'btn-outline-warning' }}"
                                                        data-bs-toggle="tooltip"
                                                        title="{{ $peserta->is_dispensasi ? 'Cabut Dispensasi' : 'Berikan Dispensasi' }}">
                                                        <i class="ri-shield-keyhole-line"></i>
                                                    </button>
                                                </form>
                                            @endif

                                            @if(!$peserta->can_print && $peserta->is_eligible)
                                                <span class="text-muted">-</span>
                                            @endif
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
@endsection

@push('styles')
    <link rel="stylesheet" href="{{ asset('assets/vendor/libs/datatables-bs5/datatables.bootstrap5.css') }}" />
@endpush

@push('scripts')
    <script src="{{ asset('assets/vendor/libs/datatables-bs5/datatables-bootstrap5.js') }}"></script>
    <script>
        $(document).ready(function () {
            $('#pesertaUjianTable').DataTable({
                responsive: false,
                scrollX: true,
                language: {
                    search: "Cari:",
                    searchPlaceholder: "Cari peserta...",
                    lengthMenu: "Tampilkan _MENU_ data",
                    zeroRecords: "Belum ada peserta. Klik 'Generate Peserta' di halaman jadwal.",
                    info: "Menampilkan _START_ - _END_ dari _TOTAL_ data",
                    infoEmpty: "Tidak ada data",
                    infoFiltered: "(difilter dari _MAX_ total data)",
                    paginate: { first: "Awal", last: "Akhir", next: "\u203A", previous: "\u2039" }
                },
                order: [[5, 'desc']],
                columnDefs: [
                    { orderable: false, targets: [7] }
                ]
            });
        });
    </script>
@endpush
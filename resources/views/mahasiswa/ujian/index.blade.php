@extends('layouts.app')

@section('title', 'Kartu Ujian')

@section('content')
    <div class="d-flex align-items-center justify-content-between mb-4">
        <h4 class="fw-bold py-3 mb-0"><span class="text-muted fw-light">Mahasiswa /</span> Kartu Ujian</h4>
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
    @if(session('info'))
        <div class="alert alert-info alert-dismissible fade show" role="alert">
            <i class="ri-information-line me-2"></i>{{ session('info') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif

    <div class="row">
        <!-- Student Info Card -->
        <div class="col-xl-4 col-lg-5 col-md-5 order-1 order-md-0">
            <div class="card mb-4 border-top border-primary border-3">
                <div class="card-body">
                    <div class="user-avatar-section">
                        <div class="d-flex align-items-center flex-column">
                            <div class="avatar avatar-xl mb-3">
                                <span class="avatar-initial rounded-circle bg-label-primary fs-2">
                                    {{ substr($mahasiswa->nama_mahasiswa, 0, 1) }}
                                </span>
                            </div>
                            <div class="user-info text-center">
                                <h5 class="mb-2">{{ $mahasiswa->nama_mahasiswa }}</h5>
                                <span class="badge bg-label-secondary">{{ $mahasiswa->nim }}</span>
                            </div>
                        </div>
                    </div>

                    <div class="d-flex justify-content-around flex-wrap my-4 py-3 border-top border-bottom">
                        <div class="d-flex align-items-start mt-1 gap-2">
                            <div class="avatar">
                                <span class="avatar-initial rounded bg-label-primary">
                                    <i class="ri-file-list-3-line ri-24px"></i>
                                </span>
                            </div>
                            <div>
                                <h5 class="mb-0">{{ $pesertaUjians->count() }}</h5>
                                <small>Total MK Ujian</small>
                            </div>
                        </div>
                        <div class="d-flex align-items-start mt-1 gap-2">
                            <div class="avatar">
                                <span class="avatar-initial rounded bg-label-success">
                                    <i class="ri-checkbox-circle-line ri-24px"></i>
                                </span>
                            </div>
                            <div>
                                <h5 class="mb-0">{{ $pesertaUjians->where('is_eligible', true)->count() }}</h5>
                                <small>Layak Ujian</small>
                            </div>
                        </div>
                    </div>

                    <h5 class="pb-2 border-bottom mb-4">Informasi</h5>
                    <ul class="list-unstyled mb-4">
                        <li class="mb-3">
                            <span class="fw-bold me-2 text-muted">Tipe Kelas:</span>
                            <span class="badge bg-label-{{ $tipeKelas === 'Pagi' ? 'warning' : 'info' }}">
                                {{ $tipeKelas ?? 'Belum ditentukan' }}
                            </span>
                        </li>
                        <li class="mb-3">
                            <span class="fw-bold me-2 text-muted">Min. Kehadiran:</span>
                            <span>{{ config('academic.min_kehadiran_ujian') }}/{{ config('academic.target_pertemuan') }}
                                pertemuan</span>
                        </li>
                    </ul>

                    <div class="card bg-label-info">
                        <div class="card-body py-3">
                            <h6 class="text-info fw-bold mb-2">Panduan Cetak & Dispensasi:</h6>
                            <ul class="mb-0 small ps-3">
                                <li>Pastikan <strong>Periode Cetak Kartu</strong> (UTS/UAS) sedang dibuka oleh Bagian
                                    Akademik.</li>
                                <li>Klik <strong>"Ajukan Cetak"</strong> untuk meminta persetujuan cetak kartu ujian.</li>
                                <li class="text-danger mt-1">
                                    Jika Anda berstatus <strong>Tidak Layak</strong>, silakan hubungi <b>Bagian Akademik</b>
                                    secara langsung untuk meminta evaluasi Pengecualian / Dispensasi kehadiran.
                                </li>
                                <li>Admin akan mencetak kartu dan memberitahu Anda saat kartu siap diambil.</li>
                            </ul>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Daftar Ujian -->
        <div class="col-xl-8 col-lg-7 col-md-7 order-0 order-md-1">
            <div class="card mb-4">
                <div class="card-header border-bottom">
                    <h5 class="card-title mb-0">Daftar Ujian Semester Aktif</h5>
                </div>

                @if($pesertaUjians->isNotEmpty())
                    <div class="card-body py-3 border-bottom border-light">
                        @php
                            $tipeUjians = $pesertaUjians->pluck('jadwalUjian.tipe_ujian')->unique();
                        @endphp

                        @foreach($tipeUjians as $tipe)
                            @php
                                $pengaturan = $pesertaUjians->first()->jadwalUjian->semester->pengaturanUjians
                                    ->where('tipe_ujian', $tipe)->first();
                                $now = now();
                            @endphp

                            @if(!$pengaturan || !$pengaturan->tgl_mulai_cetak || !$pengaturan->tgl_akhir_cetak)
                                <div class="alert alert-warning mb-2 py-2 d-flex align-items-center" role="alert">
                                    <i class="ri-alert-line me-2 fs-5"></i>
                                    <span><b>Belum ada ujian saat ini</b> untuk pelaksanaan {{ $tipe }}.</span>
                                </div>
                            @elseif($pengaturan->tgl_akhir_cetak && $now->gt($pengaturan->tgl_akhir_cetak))
                                @if($now->lte($pengaturan->tgl_akhir_cetak->copy()->addDays(3)))
                                    <div class="alert alert-danger mb-2 py-2 d-flex align-items-center" role="alert">
                                        <i class="ri-error-warning-line me-2 fs-5"></i>
                                        <span><b>Waktu pengambilan telah melewati batas</b> untuk ujian {{ $tipe }}. (Batas akhir:
                                            {{ $pengaturan->tgl_akhir_cetak->format('d M Y') }})</span>
                                    </div>
                                @endif
                            @endif
                        @endforeach
                    </div>
                @endif

                <div class="table-responsive">
                    <table class="table table-hover mb-0">
                        <thead class="table-light">
                            <tr>
                                <th>No</th>
                                <th>Mata Kuliah</th>
                                <th class="text-center">Tipe</th>
                                <th>Tanggal</th>
                                <th class="text-center">Jam</th>
                                <th class="text-center">Kehadiran</th>
                                <th class="text-center">Status</th>
                                <th class="text-center">Cetak</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($pesertaUjians as $index => $pu)
                                <tr>
                                    <td>{{ $index + 1 }}</td>
                                    <td>
                                        <div class="d-flex flex-column">
                                            <span
                                                class="fw-bold">{{ $pu->jadwalUjian->kelasKuliah->mataKuliah->nama_mk ?? '-' }}</span>
                                            <small
                                                class="text-muted">{{ $pu->jadwalUjian->kelasKuliah->mataKuliah->kode_mk ?? '' }}</small>
                                        </div>
                                    </td>
                                    <td class="text-center">
                                        <span
                                            class="badge bg-{{ $pu->jadwalUjian->tipe_ujian === 'UTS' ? 'info' : 'primary' }}">
                                            {{ $pu->jadwalUjian->tipe_ujian }}
                                        </span>
                                    </td>
                                    <td>{{ $pu->jadwalUjian->tanggal_ujian->format('d M Y') }}</td>
                                    <td class="text-center">
                                        {{ \Carbon\Carbon::parse($pu->jadwalUjian->jam_mulai)->format('H:i') }} -
                                        {{ \Carbon\Carbon::parse($pu->jadwalUjian->jam_selesai)->format('H:i') }}
                                    </td>
                                    <td class="text-center">
                                        <span
                                            class="badge bg-label-{{ $pu->persentase_kehadiran >= 85 ? 'success' : 'danger' }}">
                                            {{ $pu->jumlah_hadir }}/{{ config('academic.target_pertemuan') }}
                                        </span>
                                    </td>
                                    <td class="text-center">
                                        @if($pu->is_eligible)
                                            <span class="badge bg-success">Layak</span>
                                        @elseif($pu->is_dispensasi)
                                            <span class="badge bg-warning">Dispensasi</span>
                                        @else
                                            <span class="badge bg-danger" data-bs-toggle="tooltip"
                                                title="{{ $pu->keterangan_tidak_layak }}">
                                                Tidak Layak
                                            </span>
                                        @endif
                                    </td>
                                    <td class="text-center">
                                        @php
                                            $pengaturan = $pu->jadwalUjian->semester->pengaturanUjians
                                                ->where('tipe_ujian', $pu->jadwalUjian->tipe_ujian)
                                                ->first();
                                            $isTimeOpen = false;
                                            $timeMessage = '';
                                            $isMissingSetting = false;
                                            $now = now();

                                            if ($pengaturan && $pengaturan->tgl_mulai_cetak && $pengaturan->tgl_akhir_cetak) {
                                                if ($now->lt($pengaturan->tgl_mulai_cetak)) {
                                                    $timeMessage = 'Belum dibuka (Buka: ' . $pengaturan->tgl_mulai_cetak->format('d M H:i') . ')';
                                                } elseif ($now->gt($pengaturan->tgl_akhir_cetak)) {
                                                    if ($now->lte($pengaturan->tgl_akhir_cetak->copy()->addDays(3))) {
                                                        $timeMessage = 'Melewati batas (' . $pengaturan->tgl_akhir_cetak->format('d M') . ')';
                                                    } else {
                                                        $timeMessage = 'Sudah ditutup';
                                                    }
                                                } else {
                                                    $isTimeOpen = true;
                                                }
                                            } else {
                                                // Jika pengaturan belum di set, anggap belum dibuka
                                                $isMissingSetting = true;
                                                $timeMessage = 'Belum ada ujian saat ini';
                                            }
                                        @endphp

                                        @if($isMissingSetting)
                                            <span class="badge bg-label-warning" data-bs-toggle="tooltip"
                                                title="{{ $timeMessage }}">
                                                <i class="ri-calendar-close-line me-1"></i>Belum Diatur
                                            </span>
                                        @elseif(!$pu->can_print)
                                            <button class="btn btn-sm btn-label-secondary" disabled data-bs-toggle="tooltip"
                                                title="Hubungi Akademik untuk meminta Dispensasi Cetak">
                                                <i class="ri-lock-line"></i> Tidak Layak
                                            </button>
                                        @elseif(!$isTimeOpen)
                                            <button class="btn btn-sm btn-label-danger" disabled data-bs-toggle="tooltip"
                                                title="{{ $timeMessage }}">
                                                <i class="ri-time-line"></i> Tutup
                                            </button>
                                        @elseif($pu->status_cetak === 'dicetak')
                                            <span class="badge bg-success" data-bs-toggle="tooltip"
                                                title="Kartu sudah dicetak Bagian Akademik">
                                                <i class="ri-checkbox-circle-line me-1"></i>Selesai
                                            </span>
                                        @elseif($pu->status_cetak === 'diminta')
                                            <span class="badge bg-warning" data-bs-toggle="tooltip"
                                                title="Menunggu persetujuan Bagian Akademik">
                                                <i class="ri-loader-4-line me-1"></i>Menunggu
                                            </span>
                                        @else
                                            <form action="{{ route('mahasiswa.ujian.ajukan-cetak', $pu->id) }}" method="POST"
                                                class="d-inline">
                                                @csrf
                                                <button type="submit" class="btn btn-sm btn-primary btn-ajukan-cetak">
                                                    <i class="ri-printer-line me-1"></i> Ajukan Cetak
                                                </button>
                                            </form>
                                        @endif
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="8" class="text-center py-5">
                                        <i class="ri-file-search-line ri-4x text-muted mb-3 d-block"></i>
                                        <p class="mb-0 text-muted">Belum ada jadwal ujian yang di-generate oleh admin untuk
                                            semester ini.</p>
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
@endsection

@push('scripts')
    <script>
        $(function () {
            $('.btn-ajukan-cetak').on('click', function (e) {
                e.preventDefault();
                var form = $(this).closest('form');
                Swal.fire({
                    title: 'Ajukan Cetak Kartu Ujian?',
                    text: "Permintaan cetak akan dikirim ke admin. Anda akan diberitahu saat kartu siap diambil.",
                    icon: 'question',
                    showCancelButton: true,
                    confirmButtonText: 'Ya, Ajukan!',
                    cancelButtonText: 'Batal',
                    customClass: {
                        confirmButton: 'btn btn-primary me-3',
                        cancelButton: 'btn btn-label-secondary'
                    },
                    buttonsStyling: false
                }).then(function (result) {
                    if (result.isConfirmed) {
                        form.submit();
                    }
                });
            });
        });
    </script>
@endpush
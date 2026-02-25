@extends('layouts.app')

@section('title', 'KRS Online')

@section('content')
    <div class="d-flex align-items-center justify-content-between mb-4">
        <h4 class="fw-bold py-3 mb-0"><span class="text-muted fw-light">Mahasiswa /</span> KRS Online</h4>
        <div class="d-flex align-items-center gap-2">
            <label for="id_semester" class="form-label mb-0 text-nowrap d-none d-md-block">Pilih Semester:</label>
            <form action="{{ route('mahasiswa.krs.index') }}" method="GET" id="formSemesterSwitcher">
                <select name="id_semester" id="id_semester" class="form-select" onchange="this.form.submit()">
                    @foreach($riwayatSemester as $sem)
                        <option value="{{ $sem->id_semester }}" {{ $semesterAktif->id_semester == $sem->id_semester ? 'selected' : '' }}>
                            {{ $sem->nama_semester }}
                        </option>
                    @endforeach
                </select>
            </form>
        </div>
    </div>

    @if(!$mahasiswa->dosenPembimbing)
        <div class="alert alert-danger d-flex align-items-center" role="alert">
            <i class="ri-error-warning-line me-2"></i>
            <div>
                <strong>Perhatian!</strong> Anda belum memiliki Dosen Pembimbing Akademik yang terdaftar untuk semester ini.
                Silakan hubungi bagian Akademik untuk pemetaan PA agar Anda dapat mengajukan KRS.
            </div>
        </div>
    @endif

    <div class="row">
        <!-- Student & PA Summary -->
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
                                    <i class="ri-book-read-line ri-24px"></i>
                                </span>
                            </div>
                            <div>
                                <h5 class="mb-0">{{ $krsItems->sum(fn($i) => $i->kelasKuliah->sks_mk) }}</h5>
                                <small>Total SKS</small>
                            </div>
                        </div>
                        <div class="d-flex align-items-start mt-1 gap-2">
                            <div class="avatar">
                                <span class="avatar-initial rounded bg-label-success">
                                    <i class="ri-checkbox-circle-line ri-24px"></i>
                                </span>
                            </div>
                            <div>
                                <h5 class="mb-0">{{ $krsItems->where('status_krs', 'acc')->count() }}</h5>
                                <small>MK Disetujui</small>
                            </div>
                        </div>
                    </div>

                    <h5 class="pb-2 border-bottom mb-4">Detail Akademik</h5>
                    <div class="info-container">
                        <ul class="list-unstyled mb-4">
                            <li class="mb-3">
                                <span class="fw-bold me-2 text-muted">ID Mahasiswa:</span>
                                <span>{{ $mahasiswa->id }}</span>
                            </li>
                            <li class="mb-3">
                                <span class="fw-bold me-2 text-muted">Semester:</span>
                                <span>{{ $semesterAktif->nama_semester }}</span>
                            </li>
                            <li class="mb-3">
                                <span class="fw-bold me-2 text-muted">Dosen PA:</span>
                                <span
                                    class="text-primary fw-bold">{{ $mahasiswa->dosenPembimbing?->nama ?? 'BELUM ADA' }}</span>
                            </li>
                            <li class="mb-3">
                                <span class="fw-bold me-2 text-muted">Status KRS:</span>
                                @php
                                    $allAcc = $krsItems->isNotEmpty() && $krsItems->every(fn($i) => $i->status_krs === 'acc');
                                    $hasPending = $krsItems->contains('status_krs', 'pending');
                                @endphp
                                @if($allAcc)
                                    <span class="badge bg-label-success">Valid / ACC</span>
                                @elseif($hasPending)
                                    <span class="badge bg-label-warning">Menunggu ACC</span>
                                @else
                                    <span class="badge bg-label-info">Draft / Paket</span>
                                @endif
                            </li>
                        </ul>

                        @if($isSemesterAktif)
                            @if($canSubmit && $krsItems->contains('status_krs', 'paket'))
                                <div class="d-grid gap-2">
                                    <form action="{{ route('mahasiswa.krs.submit') }}" method="POST" id="formSubmitKrs">
                                        @csrf
                                        <input type="hidden" name="id_semester" value="{{ $semesterAktif->id_semester }}">
                                        <button type="button" class="btn btn-primary w-100 btn-submit-krs">
                                            <i class="ri-send-plane-line me-1"></i> AJUKAN KRS SEKARANG
                                        </button>
                                    </form>
                                </div>
                            @elseif(!$canSubmit && $krsItems->contains('status_krs', 'paket'))
                                <div class="alert alert-warning small mb-0">
                                    <i class="ri-lock-line me-1"></i> Masa pengisian KRS telah ditutup. Silakan hubungi prodi jika
                                    ada kendala.
                                </div>
                            @endif
                        @else
                            <div class="alert alert-info small mb-0">
                                <i class="ri-information-line me-1"></i> Anda sedang melihat riwayat KRS semester lampau.
                            </div>
                        @endif
                    </div>
                </div>
            </div>
        </div>

        <!-- KRS Items Table -->
        <div class="col-xl-8 col-lg-7 col-md-7 order-0 order-md-1">
            <div class="card mb-4">
                <div class="card-header border-bottom d-flex justify-content-between align-items-center">
                    <h5 class="card-title mb-0">Rencana Studi (KRS)</h5>
                    @if($allAcc)
                        <a href="{{ route('mahasiswa.krs.print', ['id_semester' => $semesterAktif->id_semester, 'autoprint' => 1]) }}" target="_blank"
                            class="btn btn-sm btn-success">
                            <i class="ri-printer-line me-1"></i> Cetak KRS
                        </a>
                    @else
                        <button type="button" class="btn btn-sm btn-label-secondary" disabled data-bs-toggle="tooltip" title="KRS belum disetujui Dosen PA">
                            <i class="ri-printer-line me-1"></i> Cetak KRS
                        </button>
                    @endif
                </div>
                <div class="table-responsive">
                    <table class="table table-hover mb-0">
                        <thead class="table-light">
                            <tr>
                                <th>No</th>
                                <th>Kode</th>
                                <th>Mata Kuliah</th>
                                <th class="text-center">SKS</th>
                                <th>Status</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($krsItems as $index => $item)
                                <tr>
                                    <td>{{ $index + 1 }}</td>
                                    <td><span class="badge bg-label-dark">{{ $item->kelasKuliah->mataKuliah->kode_mk }}</span>
                                    </td>
                                    <td>
                                        <div class="d-flex flex-column">
                                            <span class="fw-bold">{{ $item->kelasKuliah->mataKuliah->nama_mk }}</span>
                                            <small class="text-muted">{{ $item->kelasKuliah->nama_kelas_kuliah }}</small>
                                        </div>
                                    </td>
                                    <td class="text-center">{{ $item->kelasKuliah->sks_mk }}</td>
                                    <td>
                                        @if($item->status_krs === 'acc')
                                            <span class="badge bg-success">Disetujui</span>
                                        @elseif($item->status_krs === 'pending')
                                            <span class="badge bg-warning">Menunggu</span>
                                        @else
                                            <span class="badge bg-info">Paket</span>
                                        @endif
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="5" class="text-center py-5">
                                        <i class="ri-file-search-line ri-4x text-muted mb-3 d-block"></i>
                                        <p class="mb-0 text-muted">Anda belum memiliki data rencana studi untuk semester ini.
                                        </p>
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                        @if($krsItems->isNotEmpty())
                            <tfoot class="table-light fw-bold">
                                <tr>
                                    <td colspan="3" class="text-end">Total Kredit Terdaftar :</td>
                                    <td class="text-center">{{ $krsItems->sum(fn($i) => $i->kelasKuliah->sks_mk) }}</td>
                                    <td class="text-start">SKS</td>
                                </tr>
                            </tfoot>
                        @endif
                    </table>
                </div>
            </div>

            <div class="card bg-label-info">
                <div class="card-body">
                    <h6 class="text-info fw-bold mb-2">Panduan Pengisian:</h6>
                    <ul class="mb-0 small ps-3">
                        <li>Pastikan Mata Kuliah yang muncul sudah benar sesuai paket semester Anda.</li>
                        <li>Status <strong>Draft (Paket)</strong> berarti data masih tersimpan sebagai draf dan belum
                            dilihat Dosen PA.</li>
                        <li>Klik <strong>Ajukan KRS</strong> untuk mengirim rencana studi ke Dosen PA Anda.</li>
                        <li>Setelah diajukan, Anda tidak dapat mengubah KRS hingga disetujui atau dikembalikan untuk revisi.
                        </li>
                    </ul>
                </div>
            </div>
        </div>
    </div>
@endsection

@push('scripts')
    <script>
        $(function () {
            $('.btn-submit-krs').on('click', function () {
                Swal.fire({
                    title: 'Ajukan KRS Sangat Penting?',
                    text: "Pastikan semua mata kuliah sudah sesuai. Setelah diajukan, status akan menjadi PENDING menunggu persetujuan Dosen PA.",
                    icon: 'question',
                    showCancelButton: true,
                    confirmButtonText: 'Ya, Ajukan!',
                    cancelButtonText: 'Cek Kembali',
                    customClass: {
                        confirmButton: 'btn btn-primary me-3',
                        cancelButton: 'btn btn-label-secondary'
                    },
                    buttonsStyling: false
                }).then(function (result) {
                    if (result.isConfirmed) {
                        $('#formSubmitKrs').submit();
                    }
                });
            });
        });
    </script>
@endpush
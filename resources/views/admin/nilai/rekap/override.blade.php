@extends('layouts.app')

@section('title', 'Override Nilai - ' . ($kelas->mataKuliah->nama_mk ?? 'Matakuliah'))

@push('styles')
    <style>
        .input-nilai-detail {
            width: 65px !important;
            text-align: center;
            padding: 0.35rem 0.2rem !important;
            font-weight: 500;
            border-color: #d9dee3;
            transition: all 0.2s ease-in-out;
        }

        .comp-tugas {
            width: 50px !important;
        }

        .input-nilai-detail:focus {
            border-color: #696cff !important;
            box-shadow: 0 0 0.25rem 0.05rem rgba(105, 108, 255, 0.25) !important;
            background-color: #f8f9ff !important;
            transform: scale(1.05);
            z-index: 10;
        }

        .table-danger-custom {
            background-color: #ffeef0 !important;
        }

        /* Group column for T1-5 */
        .col-tugas-group {
            min-width: 280px !important;
            background-color: #fbfbff;
        }
    </style>
@endpush

@section('content')
    <div class="container-xxl flex-grow-1 container-p-y">
        <div class="d-flex justify-content-between align-items-center py-3 mb-4">
            <h4 class="fw-bold mb-0">
                <span class="text-muted fw-light">Rekapitulasi Nilai /</span> Override Nilai
            </h4>
            <a href="{{ route('admin.rekap-nilai.show', $kelas->id_prodi) }}" class="btn btn-outline-secondary">
                <i class="ri-arrow-left-line me-1"></i> Kembali
            </a>
        </div>

        <!-- Info Kelas & Status Lock -->
        <div class="card mb-4 border-start border-primary border-3">
            <div class="card-body">
                <div class="row">
                    <div class="col-md-6">
                        <table class="table table-borderless table-sm">
                            <tr>
                                <th width="150">Mata Kuliah</th>
                                <td>: {{ $kelas->mataKuliah->kode_mk ?? '-' }} - {{ $kelas->mataKuliah->nama_mk ?? 'Tidak Ditemukan' }}</td>
                            </tr>
                            <tr>
                                <th>Kelas</th>
                                <td>: {{ $kelas->nama_kelas_kuliah }}</td>
                            </tr>
                            <tr>
                                <th>Status Otoritas</th>
                                <td>: <span class="badge bg-label-primary">ADMIN OVERRIDE</span></td>
                            </tr>
                        </table>
                    </div>
                    <div class="col-md-6">
                        <table class="table table-borderless table-sm">
                            <tr>
                                <th width="150">Semester</th>
                                <td>: {{ $kelas->semester->nama_semester }}</td>
                            </tr>
                            <tr>
                                <th>Status Lock</th>
                                <td>: 
                                    @if($kelas->is_locked)
                                        <span class="badge bg-danger"><i class="ri-lock-2-line me-1"></i> LOCKED</span>
                                        <small class="text-muted d-block ms-2">Dikunci pada: {{ $row->locked_at ?? '-' }}</small>
                                    @else
                                        <span class="badge bg-success"><i class="ri-lock-unlock-line me-1"></i> OPEN</span>
                                    @endif
                                </td>
                            </tr>
                        </table>
                    </div>
                </div>
            </div>
        </div>

        <!-- Form Override Nilai -->
        <form action="{{ route('admin.rekap-nilai.override.store', $kelas->id_kelas_kuliah) }}" method="POST">
            @csrf
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h5 class="mb-0">Daftar Mahasiswa (Override Mode)</h5>
                    <button type="submit" class="btn btn-danger">
                        <i class="ri-shield-user-line me-1"></i> Simpan Perubahan (Override)
                    </button>
                </div>
                <div class="card-body">
                    <div class="alert alert-warning d-flex align-items-center" role="alert">
                        <i class="ri-error-warning-line me-2 fs-4"></i>
                        <div>
                            <strong>Perhatian Admin:</strong> Setiap perubahan nilai yang dilakukan di halaman ini akan dicatat dalam Log Audit Trail sistem.
                        </div>
                    </div>

                    <div class="table-responsive text-nowrap">
                        <table class="table table-bordered" id="tableMahasiswa">
                            <thead class="table-light">
                                <tr>
                                    <th width="50" class="text-center">No</th>
                                    <th>Nama Mahasiswa</th>
                                    <th class="text-center col-tugas-group" title="Tugas 1-5 (25%)">Tugas 1-5 (25%)</th>
                                    <th class="text-center" title="Aktif (5%)">Akt</th>
                                    <th class="text-center" title="Etika (5%)">Etk</th>
                                    <th class="text-center" title="Presensi (15%)">Psn</th>
                                    <th class="text-center" title="UTS (25%)">UTS</th>
                                    <th class="text-center" title="UAS (25%)">UAS</th>
                                    <th width="120" class="text-center">Nilai Akhir</th>
                                    <th width="80" class="text-center">Grade</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($peserta as $index => $row)
                                    @php
                                        $isFailed = $row->nilai_indeks !== null && $row->nilai_indeks < 2.0;
                                        $targetPertemuan = config('academic.target_pertemuan', 14);
                                    @endphp
                                    <tr class="{{ $isFailed ? 'table-danger-custom' : '' }}" data-peserta-id="{{ $row->id }}">
                                        <td class="text-center">{{ $index + 1 }}</td>
                                        <td>
                                            <div class="fw-bold">{{ $row->riwayatPendidikan->mahasiswa->nama_mahasiswa ?? '-' }}</div>
                                            <small class="text-muted">{{ $row->riwayatPendidikan->nim ?? '-' }}</small>
                                        </td>
                                        <!-- Tugas 1-5 -->
                                        <td class="text-center d-flex gap-1 justify-content-center p-1 col-tugas-group">
                                            @for($i=1; $i<=5; $i++)
                                                <input type="number" step="0.01" min="0" max="100"
                                                    name="tugas{{$i}}[{{ $row->id }}]" value="{{ $row->{'tugas'.$i} ?: 0 }}"
                                                    class="form-control form-control-sm input-nilai-detail comp-tugas"
                                                    data-comp="t{{$i}}">
                                            @endfor
                                        </td>
                                        <!-- Aktif & Etika -->
                                        <td class="text-center">
                                            <input type="number" step="0.01" min="0" max="100"
                                                name="aktif[{{ $row->id }}]" value="{{ $row->aktif ?: 0 }}"
                                                class="form-control form-control-sm input-nilai-detail comp-aktif">
                                        </td>
                                        <td class="text-center">
                                            <input type="number" step="0.01" min="0" max="100"
                                                name="etika[{{ $row->id }}]" value="{{ $row->etika ?: 0 }}"
                                                class="form-control form-control-sm input-nilai-detail comp-etika">
                                        </td>
                                        <!-- Presensi -->
                                        <td class="text-center">
                                            <div class="presensi-score fw-bold" 
                                                 data-hadir="{{ $row->total_hadir }}" 
                                                 data-target="{{ $targetPertemuan }}">
                                                {{ round(($row->total_hadir / $targetPertemuan) * 15, 2) }}
                                            </div>
                                            <small class="text-muted">{{ $row->total_hadir }}/{{ $targetPertemuan }}</small>
                                        </td>
                                        <!-- UTS & UAS -->
                                        <td class="text-center">
                                            <input type="number" step="0.01" min="0" max="100"
                                                name="uts[{{ $row->id }}]" value="{{ $row->uts ?: 0 }}"
                                                class="form-control form-control-sm input-nilai-detail comp-uts">
                                        </td>
                                        <td class="text-center">
                                            <input type="number" step="0.01" min="0" max="100"
                                                name="uas[{{ $row->id }}]" value="{{ $row->uas ?: 0 }}"
                                                class="form-control form-control-sm input-nilai-detail comp-uas">
                                        </td>
                                        <!-- Result -->
                                        <td class="text-center">
                                            <span id="akhir-{{ $row->id }}" class="fw-bold fs-5 text-primary">
                                                {{ number_format($row->nilai_angka, 2) }}
                                            </span>
                                        </td>
                                        <td class="text-center">
                                            <span id="huruf-{{ $row->id }}" class="badge bg-label-dark p-2">
                                                {{ $row->nilai_huruf ?: '-' }}
                                            </span>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </form>
    </div>
@endsection

@push('scripts')
    <script>
        $(function () {
            const ajaxUrl = "{{ route('admin.rekap-nilai.ajax-convert') }}";
            let timeout = null;

            function calculateRow(tr) {
                const pesertaId = tr.data('peserta-id');
                
                // 1. Tugas (25%) - Rata-rata 5 tugas
                let sumTugas = 0;
                tr.find('.comp-tugas').each(function() {
                    sumTugas += parseFloat($(this).val()) || 0;
                });
                const avgTugas = sumTugas / 5;
                const scoreTugas = avgTugas * 0.25;

                // 2. Aktif (5%) & Etika (5%)
                const scoreAktif = (parseFloat(tr.find('.comp-aktif').val()) || 0) * 0.05;
                const scoreEtika = (parseFloat(tr.find('.comp-etika').val()) || 0) * 0.05;

                // 3. Presensi (15%)
                const presensiDiv = tr.find('.presensi-score');
                const hadir = parseFloat(presensiDiv.data('hadir')) || 0;
                const target = parseFloat(presensiDiv.data('target')) || 14;
                const scorePresensi = Math.min((hadir / target) * 15, 15);

                // 4. UTS (25%) & UAS (25%)
                const scoreUTS = (parseFloat(tr.find('.comp-uts').val()) || 0) * 0.25;
                const scoreUAS = (parseFloat(tr.find('.comp-uas').val()) || 0) * 0.25;

                // Total
                const total = scoreTugas + scoreAktif + scoreEtika + scorePresensi + scoreUTS + scoreUAS;
                const finalScore = Math.round(total * 100) / 100;

                tr.find(`#akhir-${pesertaId}`).text(finalScore.toFixed(2));

                // AJAX untuk Grade Huruf
                clearTimeout(timeout);
                timeout = setTimeout(function () {
                    $.ajax({
                        url: ajaxUrl,
                        method: 'POST',
                        data: {
                            _token: "{{ csrf_token() }}",
                            nilai_angka: finalScore,
                            id_prodi: "{{ $kelas->id_prodi }}"
                        },
                        success: function (res) {
                            $(`#huruf-${pesertaId}`).text(res.nilai_huruf);
                            
                            if (parseFloat(res.nilai_indeks) < 2.0) {
                                tr.addClass('table-danger-custom');
                            } else {
                                tr.removeClass('table-danger-custom');
                            }
                        }
                    });
                }, 500);
            }

            $('.input-nilai-detail').on('input', function () {
                calculateRow($(this).closest('tr'));
            });
        });
    </script>
@endpush

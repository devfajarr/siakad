@extends('layouts.app')

@section('title', 'Input Nilai - ' . $kelas->mataKuliah->nama_mata_kuliah)

@push('styles')
    <style>
        .input-nilai {
            width: 80px;
            text-align: center;
        }

        .table-danger-custom {
            background-color: #ffeef0 !important;
        }
    </style>
@endpush

@section('content')
    <div class="container-xxl flex-grow-1 container-p-y">
        <div class="d-flex justify-content-between align-items-center py-3 mb-4">
            <h4 class="fw-bold mb-0">
                <span class="text-muted fw-light">Input Nilai /</span> {{ $kelas->mataKuliah->nama_mk }}
            </h4>
            <a href="{{ route('dosen.nilai.index') }}" class="btn btn-outline-secondary">
                <i class="ri-arrow-left-line me-1"></i> Kembali
            </a>
        </div>

        <!-- Info Kelas -->
        <div class="card mb-4">
            <div class="card-body">
                <div class="row">
                    <div class="col-md-6">
                        <table class="table table-borderless table-sm">
                            <tr>
                                <th width="150">Mata Kuliah</th>
                                <td>: {{ $kelas->mataKuliah->kode_mk }} -
                                    {{ $kelas->mataKuliah->nama_mk }}
                                </td>
                            </tr>
                            <tr>
                                <th>Kelas</th>
                                <td>: {{ $kelas->nama_kelas_kuliah }}</td>
                            </tr>
                            <tr>
                                <th>Program Studi</th>
                                <td>: {{ $kelas->programStudi->nama_program_studi }}</td>
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
                                <th>Dosen Pengampu</th>
                                <td>: {{ auth()->user()->dosen->nama_tampilan }}</td>
                            </tr>
                            <tr>
                                <th>Jumlah Mhs</th>
                                <td>: {{ $peserta->count() }} Mahasiswa</td>
                            </tr>
                        </table>
                    </div>
                </div>
            </div>
        </div>

        <!-- Form Input Nilai -->
        <form action="{{ route('dosen.nilai.store', $kelas->id_kelas_kuliah) }}" method="POST">
            @csrf
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h5 class="mb-0">Daftar Mahasiswa</h5>
                    <button type="submit" class="btn btn-primary">
                        <i class="ri-save-line me-1"></i> Simpan Nilai
                    </button>
                </div>
                <div class="card-body">
                    <div class="table-responsive text-nowrap">
                        <table class="table table-bordered" id="tableMahasiswa">
                            <thead class="table-light">
                                <tr>
                                    <th width="50" class="text-center">No</th>
                                    <th width="150">NIM</th>
                                    <th>Nama Mahasiswa</th>
                                    <th width="120" class="text-center">Nilai Angka</th>
                                    <th width="100" class="text-center">Nilai Huruf</th>
                                    <th width="100" class="text-center">Indeks</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($peserta as $index => $row)
                                    @php
                                        $isFailed = $row->nilai_indeks !== null && $row->nilai_indeks < 2.0; // Misal < 2.0 (C) dianggap merah
                                    @endphp
                                    <tr class="{{ $isFailed ? 'table-danger-custom' : '' }}">
                                        <td class="text-center">{{ $index + 1 }}</td>
                                        <td>{{ $row->riwayatPendidikan->nim ?? '-' }}</td>
                                        <td>{{ $row->riwayatPendidikan->mahasiswa->nama_mahasiswa ?? '-' }}</td>
                                        <td class="text-center">
                                            <input type="number" step="0.01" min="0" max="100" name="nilai[{{ $row->id }}]"
                                                value="{{ $row->nilai_angka }}"
                                                class="form-control form-control-sm input-nilai numeric-input"
                                                data-peserta-id="{{ $row->id }}"
                                                data-id-prodi="{{ $row->riwayatPendidikan->id_prodi ?? $kelas->id_prodi }}">
                                        </td>
                                        <td class="text-center">
                                            <span id="huruf-{{ $row->id }}" class="fw-bold fs-5">
                                                {{ $row->nilai_huruf ?: '-' }}
                                            </span>
                                        </td>
                                        <td class="text-center">
                                            <span id="indeks-{{ $row->id }}">
                                                {{ $row->nilai_indeks !== null ? number_format($row->nilai_indeks, 2) : '0.00' }}
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
            const ajaxUrl = "{{ route('dosen.nilai.ajax-convert') }}";
            let timeout = null;

            $('.input-nilai').on('input', function () {
                const input = $(this);
                const val = input.val();
                const pesertaId = input.data('peserta-id');
                const prodiId = input.data('id-prodi');
                const tr = input.closest('tr');

                // Clear previous timeout
                clearTimeout(timeout);

                // Basic validation
                if (val === '' || isNaN(val)) {
                    $(`#huruf-${pesertaId}`).text('-');
                    $(`#indeks-${pesertaId}`).text('0.00');
                    tr.removeClass('table-danger-custom');
                    return;
                }

                if (val < 0 || val > 100) {
                    input.addClass('is-invalid');
                    return;
                } else {
                    input.removeClass('is-invalid');
                }

                // Debounce AJAX call
                timeout = setTimeout(function () {
                    $.ajax({
                        url: ajaxUrl,
                        method: 'POST',
                        data: {
                            _token: "{{ csrf_token() }}",
                            nilai_angka: val,
                            id_prodi: prodiId
                        },
                        success: function (res) {
                            $(`#huruf-${pesertaId}`).text(res.nilai_huruf);
                            $(`#indeks-${pesertaId}`).text(parseFloat(res.nilai_indeks).toFixed(2));

                            // Visual Feedback for failed
                            if (parseFloat(res.nilai_indeks) < 2.0) {
                                tr.addClass('table-danger-custom');
                            } else {
                                tr.removeClass('table-danger-custom');
                            }
                        },
                        error: function () {
                            console.error('Gagal mengambil data konversi nilai');
                        }
                    });
                }, 400); // 400ms debounce
            });
        });
    </script>
@endpush
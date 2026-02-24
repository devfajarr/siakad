@extends('layouts.app')

@section('title', 'Daftar Kelas Saya')

@push('styles')
    <link rel="stylesheet" href="{{ asset('assets/vendor/libs/datatables-bs5/datatables.bootstrap5.css') }}" />
    <link rel="stylesheet" href="{{ asset('assets/vendor/libs/datatables-responsive-bs5/responsive.bootstrap5.css') }}" />
@endpush

@section('content')
    <div class="card">
        <div class="card-header d-flex justify-content-between align-items-center flex-wrap gap-3">
            <h5 class="card-title mb-0">Daftar Kelas
                {{ $semesters->firstWhere('id_semester', $semesterId)->nama_semester ?? '' }}
            </h5>

            <form action="{{ route('dosen.kelas.index') }}" method="GET" class="d-flex align-items-center gap-2">
                <label for="semester_id" class="form-label mb-0 text-nowrap">Filter Semester:</label>
                <select name="semester_id" id="semester_id" class="form-select form-select-sm"
                    onchange="this.form.submit()">
                    @foreach($semesters as $sem)
                        <option value="{{ $sem->id_semester }}" {{ $semesterId == $sem->id_semester ? 'selected' : '' }}>
                            {{ $sem->nama_semester }}
                        </option>
                    @endforeach
                </select>
            </form>
        </div>
        <div class="table-responsive pt-2 pb-5">
            <table class="datatables-basic table table-bordered table-hover text-nowrap">
                <thead class="table-light">
                    <tr>
                        <th width="50px">No</th>
                        <th>Kode & Nama Mata Kuliah</th>
                        <th>Nama Kelas</th>
                        <th>SKS</th>
                        <th>Mahasiswa Tedaftar</th>
                        <th width="100px">Action</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($kelasKuliahs as $index => $item)
                        @php
                            $dosenPengajar = $item->dosenPengajars->firstWhere('id_dosen', auth()->user()->dosen->id);
                            $jenisEvaluasi = $dosenPengajar ? (\App\Models\DosenPengajarKelasKuliah::JENIS_EVALUASI[$dosenPengajar->jenis_evaluasi] ?? '-') : '-';
                        @endphp
                        <tr>
                            <td>{{ $index + 1 }}</td>
                            <td>
                                <span class="fw-bold text-primary">{{ $item->mataKuliah->kode_mk ?? '-' }}</span><br>
                                <small>{{ $item->mataKuliah->nama_mk ?? '-' }}</small>
                            </td>
                            <td>{{ $item->nama_kelas_kuliah }}</td>
                            <td>{{ rtrim(rtrim(number_format($item->mataKuliah->sks ?? 0, 2), '0'), '.') }}</td>
                            <td class="text-center">
                                <span class="badge bg-label-info">{{ $item->peserta_kelas_kuliah_count }} Mahasiswa</span>
                            </td>
                            <td>
                                <div class="d-flex gap-2">
                                    <a href="{{ route('dosen.kelas.show', $item->id) }}"
                                        class="btn btn-sm btn-info rounded-pill" title="Detail Kelas">
                                        <i class="ri-eye-line me-1"></i> Detail
                                    </a>
                                    <a href="{{ route('dosen.presensi.index', $item->id) }}"
                                        class="btn btn-sm btn-primary rounded-pill" title="Presensi & Jurnal">
                                        <i class="ri-contacts-book-line me-1"></i> Presensi
                                    </a>
                                </div>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
@endsection

@push('scripts')
    <script src="{{ asset('assets/vendor/libs/datatables-bs5/datatables-bootstrap5.js') }}"></script>
    <script>
        $(function () {
            var dt_basic_table = $('.datatables-basic');
            if (dt_basic_table.length) {
                var dt_basic = dt_basic_table.DataTable({
                    displayLength: 10,
                    lengthMenu: [7, 10, 25, 50],
                    responsive: false,
                    scrollX: true,
                    dom: '<"row mt-3"<"col-sm-12 col-md-6 px-4"l><"col-sm-12 col-md-6 d-flex justify-content-center justify-content-md-end px-4"f>>t<"row mt-3"<"col-sm-12 col-md-6 px-4"i><"col-sm-12 col-md-6 px-4"p>>',
                });
            }
        });
    </script>
@endpush
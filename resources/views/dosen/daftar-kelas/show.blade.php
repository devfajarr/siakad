@extends('layouts.app')

@section('title', 'Detail Kelas: ' . $kelasKuliah->nama_kelas_kuliah)

@push('styles')
    <link rel="stylesheet" href="{{ asset('assets/vendor/libs/datatables-bs5/datatables.bootstrap5.css') }}" />
    <link rel="stylesheet" href="{{ asset('assets/vendor/libs/datatables-responsive-bs5/responsive.bootstrap5.css') }}" />
@endpush

@section('content')
    <div class="row">
        <!-- Informasi Kelas -->
        <div class="col-12 col-lg-4 mb-4">
            <div class="card h-100">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h5 class="card-title mb-0">Informasi Kelas</h5>
                    <a href="{{ route('dosen.kelas.index') }}" class="btn btn-sm btn-outline-secondary">
                        <i class="ri-arrow-left-line me-1"></i> Kembali
                    </a>
                </div>
                <div class="card-body">
                    <dl class="row mb-0">
                        <dt class="col-sm-4 fw-medium text-nowrap">Mata Kuliah</dt>
                        <dd class="col-sm-8">{{ $kelasKuliah->mataKuliah->nama_mk ?? '-' }}
                            ({{ $kelasKuliah->mataKuliah->kode_mk ?? '-' }})</dd>

                        <dt class="col-sm-4 fw-medium text-nowrap">Nama Kelas</dt>
                        <dd class="col-sm-8">{{ $kelasKuliah->nama_kelas_kuliah }}</dd>

                        <dt class="col-sm-4 fw-medium text-nowrap">SKS / Kapasitas</dt>
                        <dd class="col-sm-8">
                            {{ rtrim(rtrim(number_format($kelasKuliah->mataKuliah->sks ?? 0, 2), '0'), '.') }} /
                            {{ $kelasKuliah->kapasitas ?? 0 }}
                        </dd>

                        <dt class="col-sm-4 fw-medium text-nowrap">Team Teaching</dt>
                        <dd class="col-sm-8">
                            <ul class="list-unstyled mb-0">
                                @foreach($kelasKuliah->dosenPengajars as $pengajar)
                                    @php
                                        $isMe = $pengajar->id_dosen == auth()->user()->dosen->id;
                                        $jenisEvaluasi = \App\Models\DosenPengajarKelasKuliah::JENIS_EVALUASI[$pengajar->jenis_evaluasi] ?? '-';
                                    @endphp
                                    <li class="{{ $isMe ? 'fw-bold text-primary' : '' }}">
                                        <i class="ri-user-{{ $isMe ? 'star' : 'line' }} me-1"></i>
                                        {{ $pengajar->nama_tampilan }}
                                        <br><small class="text-muted ms-4">Peran: {{ $jenisEvaluasi }}</small>
                                    </li>
                                @endforeach
                            </ul>
                        </dd>

                        <dt class="col-sm-4 fw-medium text-nowrap mt-3">Jadwal Kuliah</dt>
                        <dd class="col-sm-8 mt-3">
                            @forelse($kelasKuliah->jadwalKuliahs as $jadwal)
                                <div class="mb-1">
                                    <span class="badge bg-label-primary"><i class="ri-calendar-line"></i>
                                        {{ ucfirst($jadwal->hari) }}</span>
                                    <span class="badge bg-label-info"><i class="ri-time-line"></i>
                                        {{ \Carbon\Carbon::parse($jadwal->jam_mulai)->format('H:i') }} -
                                        {{ \Carbon\Carbon::parse($jadwal->jam_selesai)->format('H:i') }}</span>
                                    <span class="badge bg-label-secondary"><i class="ri-map-pin-line"></i>
                                        {{ $jadwal->ruang->nama_ruang ?? 'TBA' }}</span>
                                </div>
                            @empty
                                <span class="text-muted fst-italic">Jadwal belum ditentukan</span>
                            @endforelse
                        </dd>
                    </dl>
                </div>
            </div>
        </div>

        <!-- Daftar Mahasiswa -->
        <div class="col-12 col-lg-8 mb-4">
            <div class="card h-100">
                <div class="card-header border-bottom">
                    <h5 class="card-title mb-0">Peserta Kelas ({{ $kelasKuliah->peserta_kelas_kuliah_count }} Mahasiswa)
                    </h5>
                </div>
                <div class="table-responsive pt-2 pb-5">
                    <table class="datatables-basic table table-bordered table-hover text-nowrap">
                        <thead class="table-light">
                            <tr>
                                <th width="50px">No</th>
                                <th>NIM</th>
                                <th>Nama Mahasiswa</th>
                                <th>Program Studi</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($kelasKuliah->pesertaKelasKuliah as $index => $peserta)
                                @php
                                    $riwayat = $peserta->riwayatPendidikan;
                                    $mahasiswa = $riwayat->mahasiswa ?? null;
                                @endphp
                                <tr>
                                    <td>{{ $index + 1 }}</td>
                                    <td>
                                        <span class="fw-bold">{{ $riwayat->nim ?? '-' }}</span>
                                    </td>
                                    <td>{{ $mahasiswa->nama_mahasiswa ?? '-' }}</td>
                                    <td>{{ $riwayat->prodi->nama_program_studi ?? '-' }}</td>
                                </tr>
                            @endforeach

                            @if($kelasKuliah->pesertaKelasKuliah->isEmpty())
                                <tr>
                                    <td colspan="4" class="text-center py-4 text-muted fst-italic">Belum ada mahasiswa yang
                                        masuk ke kelas ini.</td>
                                </tr>
                            @endif
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
@endsection

@push('scripts')
    <script src="{{ asset('assets/vendor/libs/datatables-bs5/datatables-bootstrap5.js') }}"></script>
    <script>
        $(function () {
            var dt_basic_table = $('.datatables-basic');
            if (dt_basic_table.length && dt_basic_table.find('tbody tr td').length > 1) {
                var dt_basic = dt_basic_table.DataTable({
                    displayLength: 25,
                    lengthMenu: [10, 25, 50, 75, 100],
                    responsive: false,
                    scrollX: true,
                    dom: '<"row mt-3"<"col-sm-12 col-md-6 px-4"l><"col-sm-12 col-md-6 d-flex justify-content-center justify-content-md-end px-4"f>>t<"row mt-3"<"col-sm-12 col-md-6 px-4"i><"col-sm-12 col-md-6 px-4"p>>',
                });
            }
        });
    </script>
@endpush
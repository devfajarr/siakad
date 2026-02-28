@extends('layouts.app')

@section('title', 'Daftar Kelas Saya')

@section('content')
    <div class="card border-0 shadow-sm">
        <div class="card-header d-flex align-items-center justify-content-between p-6">
            <h5 class="card-title m-0">
                <i class="ri-artboard-line me-2 text-primary"></i> Daftar Kelas Perkuliahan
            </h5>
            <div class="col-md-3">
                <form action="{{ route('mahasiswa.kelas.index') }}" method="GET" id="filterForm">
                    <select name="semester_id" class="form-select select2" onchange="this.form.submit()">
                        @foreach($semesters as $smt)
                            <option value="{{ $smt->id_semester }}" {{ $semesterId == $smt->id_semester ? 'selected' : '' }}>
                                {{ $smt->nama_semester }}
                            </option>
                        @endforeach
                    </select>
                </form>
            </div>
        </div>
        <div class="card-body">
            @if($kelasKuliahs->isEmpty())
                <div class="alert alert-warning d-flex align-items-center mb-0" role="alert">
                    <i class="ri-error-warning-line ri-22px me-2"></i>
                    Anda belum terdaftar pada kelas perkuliahan manapun di <b>Semester
                        {{ $semesters->firstWhere('id_semester', $semesterId)->nama_semester ?? 'Terpilih' }}</b>.
                </div>
            @else
                <div class="table-responsive text-nowrap">
                    <table class="table table-hover datatables" id="tableKelas">
                        <thead>
                            <tr>
                                <th>Kode MK</th>
                                <th>Mata Kuliah</th>
                                <th>Dosen Pengajar</th>
                                <th>SKS</th>
                                <th>Jadwal & Ruangan</th>
                                <th class="text-center">Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($kelasKuliahs as $kelas)
                                <tr>
                                    <td><span class="fw-bold">{{ $kelas->mataKuliah->kode_mk ?? '-' }}</span></td>
                                    <td>
                                        <div class="d-flex flex-column">
                                            <span class="fw-medium">{{ $kelas->mataKuliah->nama_mk ?? '-' }}</span>
                                            <small class="text-muted">Kelas: {{ $kelas->nama_kelas_kuliah }}</small>
                                        </div>
                                    </td>
                                    <td>
                                        @if($kelas->dosenPengajars->isNotEmpty())
                                            <div class="small">
                                                <i class="ri-user-follow-line text-primary me-1"></i>
                                                {{ $kelas->dosenPengajars->first()->nama_tampilan }}
                                            </div>
                                        @else
                                            <span class="text-muted italic small">Belum ada dosen</span>
                                        @endif
                                    </td>
                                    <td>{{ rtrim(rtrim(number_format($kelas->sks_mk ?? ($kelas->mataKuliah->sks ?? 0), 2), '0'), '.') }}
                                    </td>
                                    <td>
                                        @forelse($kelas->jadwalKuliahs as $jadwal)
                                            <div class="small">
                                                <i class="ri-time-line text-primary me-1"></i>
                                                {{ \App\Services\JadwalKuliahService::HARI[$jadwal->hari] ?? $jadwal->hari }},
                                                {{ substr($jadwal->jam_mulai, 0, 5) }}-{{ substr($jadwal->jam_selesai, 0, 5) }}
                                                ({{ $jadwal->ruang->nama_ruang ?? '?' }})
                                            </div>
                                        @empty
                                            <span class="text-muted italic small">Jadwal belum diset</span>
                                        @endforelse
                                    </td>
                                    <td class="text-center">
                                        <div class="d-flex justify-content-center gap-2">
                                            <a href="{{ route('mahasiswa.kelas.show', $kelas->id) }}"
                                                class="btn btn-sm btn-label-secondary">
                                                Detail
                                            </a>
                                            <a href="{{ route('mahasiswa.presensi.show', $kelas->id) }}"
                                                class="btn btn-sm btn-label-primary">
                                                Presensi
                                            </a>
                                        </div>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @endif
        </div>
    </div>
@endsection

@push('page-script')
    <script>
        $(document).ready(function () {
            $('#tableKelas').DataTable({
                responsive: true,
                language: {
                    searchPlaceholder: "Cari mata kuliah...",
                    search: ""
                }
            });
            $('.select2').select2({
                width: '100%'
            });
        });
    </script>
@endpush
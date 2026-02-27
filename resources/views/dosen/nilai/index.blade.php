@extends('layouts.app')

@section('title', 'Input Nilai Mahasiswa')

@section('content')
    <div class="container-xxl flex-grow-1 container-p-y">
        <h4 class="fw-bold py-3 mb-4">
            <span class="text-muted fw-light">Dosen /</span> Input Nilai Mahasiswa
        </h4>

        <div class="card">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h5 class="mb-0">Daftar Kelas Semester {{ $activeSemester->nama_semester }}</h5>
            </div>
            <div class="card-body">
                <div class="table-responsive text-nowrap">
                    <table class="table table-hover" id="tableKelas">
                        <thead>
                            <tr>
                                <th>Mata Kuliah</th>
                                <th>Kelas</th>
                                <th>Program Studi</th>
                                <th class="text-center">Peran</th>
                                <th class="text-center">Mahasiswa (Terisi/Total)</th>
                                <th class="text-center">Progres</th>
                                <th class="text-center">Aksi</th>
                            </tr>
                        </thead>
                        <tbody class="table-border-bottom-0">
                            @forelse ($kelas as $row)
                                @php
                                    $progres = $row->total_mahasiswa > 0 ? ($row->terisi_count / $row->total_mahasiswa) * 100 : 0;
                                    $badgeColor = $progres == 100 ? 'success' : ($progres > 0 ? 'warning' : 'secondary');

                                    // Tentukan peran dosen yang sedang login
                                    $dosenId = auth()->user()->dosen->id;
                                    $isUtama = $row->dosenPengajar->contains('id_dosen', $dosenId);
                                @endphp
                                <tr>
                                    <td>
                                        <strong>{{ $row->mataKuliah->nama_mk }}</strong><br>
                                        <small class="text-muted">{{ $row->mataKuliah->kode_mk }}</small>
                                    </td>
                                    <td>{{ $row->nama_kelas_kuliah }}</td>
                                    <td>{{ $row->programStudi->nama_program_studi }}</td>
                                    <td class="text-center">
                                        @if($isUtama)
                                            <span class="badge bg-label-success">Dosen Utama</span>
                                        @else
                                            <span class="badge bg-label-info">Dosen Alias</span>
                                        @endif
                                    </td>
                                    <td class="text-center">
                                        <span class="badge rounded-pill bg-label-primary">
                                            {{ $row->terisi_count }} / {{ $row->total_mahasiswa }}
                                        </span>
                                    </td>
                                    <td class="text-center">
                                        <div class="progress" style="height: 8px;">
                                            <div class="progress-bar bg-{{ $badgeColor }}" role="progressbar"
                                                style="width: {{ $progres }}%;" aria-valuenow="{{ $progres }}" aria-valuemin="0"
                                                aria-valuemax="100"></div>
                                        </div>
                                        <small class="text-muted">{{ number_format($progres, 0) }}%</small>
                                    </td>
                                    <td class="text-center">
                                        <a href="{{ route('dosen.nilai.show', $row->id_kelas_kuliah) }}"
                                            class="btn btn-sm btn-primary">
                                            <i class="ri-edit-line me-1"></i> Input Nilai
                                        </a>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="6" class="text-center py-4">
                                        <img src="{{ asset('assets/img/illustrations/page-misc-error-light.png') }}"
                                            alt="No Data" width="150" class="mb-3"><br>
                                        <span class="text-muted">Tidak ada kelas yang diampu pada semester aktif ini.</span>
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
            if ($.fn.DataTable) {
                $('#tableKelas').DataTable({
                    language: {
                        searchPlaceholder: 'Cari Kelas/MK...',
                        sLengthMenu: '_MENU_',
                    }
                });
            }
        });
    </script>
@endpush
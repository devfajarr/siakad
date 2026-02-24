@extends('layouts.app')

@section('title', 'Monitoring Perkuliahan')

@section('content')
    <div class="container-xxl flex-grow-1 container-p-y">
        <div class="row g-4 mb-4">
            <div class="col-sm-6 col-xl-4">
                <div class="card h-100 border-0 shadow-sm">
                    <div class="card-body">
                        <div class="d-flex align-items-center mb-2">
                            <div class="avatar me-3">
                                <span class="avatar-initial rounded bg-label-primary"><i
                                        class="ri-book-open-line"></i></span>
                            </div>
                            <h4 class="mb-0">{{ $stats['total_kelas'] }}</h4>
                        </div>
                        <p class="mb-0 text-muted fst-italic">Total Kelas Aktif</p>
                    </div>
                </div>
            </div>
            <div class="col-sm-6 col-xl-4">
                <div class="card h-100 border-0 shadow-sm">
                    <div class="card-body">
                        <div class="d-flex align-items-center mb-2">
                            <div class="avatar me-3">
                                <span class="avatar-initial rounded bg-label-success"><i
                                        class="ri-checkbox-circle-line"></i></span>
                            </div>
                            <h4 class="mb-0">{{ $stats['selesai'] }}</h4>
                        </div>
                        <p class="mb-0 text-muted fst-italic">Selesai (>= 13 Pertemuan)</p>
                    </div>
                </div>
            </div>
            <div class="col-sm-6 col-xl-4">
                <div class="card h-100 border-0 shadow-sm">
                    <div class="card-body">
                        <div class="d-flex align-items-center mb-2">
                            <div class="avatar me-3">
                                <span class="avatar-initial rounded bg-label-danger"><i
                                        class="ri-error-warning-line"></i></span>
                            </div>
                            <h4 class="mb-0">{{ $stats['tertinggal'] }}</h4>
                        </div>
                        <p class="mb-0 text-muted fst-italic">Butuh Perhatian (< 7 Pertemuan)</p>
                    </div>
                </div>
            </div>
        </div>

        <div class="card border-0 shadow-sm">
            <div class="card-header d-flex justify-content-between align-items-center flex-wrap gap-3">
                <h5 class="card-title mb-0">Monitoring Progres Pertemuan</h5>
                <div class="d-flex align-items-center gap-3">
                    <form action="{{ route('admin.monitoring.perkuliahan') }}" method="GET"
                        class="d-flex align-items-center gap-2">
                        @if($semesterId)
                            <input type="hidden" name="semester_id" value="{{ $semesterId }}">
                        @endif
                        <div class="input-group input-group-sm">
                            <input type="text" name="search" class="form-control"
                                placeholder="Cari MK, Dosen, atau Prodi..." value="{{ $search }}">
                            <button type="submit" class="btn btn-primary">Cari</button>
                        </div>
                    </form>

                    <form action="{{ route('admin.monitoring.perkuliahan') }}" method="GET"
                        class="d-flex align-items-center gap-2">
                        @if($search)
                            <input type="hidden" name="search" value="{{ $search }}">
                        @endif
                        <label for="semester_id" class="form-label mb-0 text-nowrap">Semester:</label>
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
            </div>
            <div class="table-responsive pt-2 pb-2 text-nowrap">
                <table class="table table-hover align-middle mb-0 datatables-monitoring">
                    <thead class="table-light">
                        <tr>
                            <th width="50px">No</th>
                            <th>Program Studi</th>
                            <th>Mata Kuliah</th>
                            <th>Kelas</th>
                            <th>Dosen Pengampu</th>
                            <th class="text-center">Progres</th>
                            <th class="text-center">Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($kelasKuliahs as $index => $kelas)
                            <tr>
                                <td>{{ $kelasKuliahs->firstItem() + $index }}</td>
                                <td><small>{{ $kelas->programStudi->nama_program_studi ?? '-' }}</small></td>
                                <td>
                                    <div class="fw-semibold text-primary">
                                        {{ $kelas->mataKuliah->nama_mk ?? 'Matkul Tidak Ditemukan' }}
                                    </div>
                                    <small class="text-muted">{{ $kelas->mataKuliah->kode_mk ?? '-' }}</small>
                                </td>
                                <td>{{ $kelas->nama_kelas_kuliah }}</td>
                                <td>
                                    @php
                                        $primaryDosen = $kelas->dosenPengajars->first()->nama_admin_display ?? '-';
                                    @endphp
                                    {{ $primaryDosen }}
                                    @if($kelas->dosenPengajars->count() > 1)
                                        <small class="text-muted text-nowrap">+{{ $kelas->dosenPengajars->count() - 1 }} Tim</small>
                                    @endif
                                </td>
                                <td class="text-center">
                                    <div class="d-flex align-items-center justify-content-center">
                                        <div class="progress w-100 me-2" style="height: 8px;">
                                            <div class="progress-bar bg-{{ $kelas->status_warna }}" role="progressbar"
                                                style="width: {{ ($kelas->presensi_pertemuans_count / config('academic.target_pertemuan')) * 100 }}%"
                                                aria-valuenow="{{ $kelas->presensi_pertemuans_count }}" aria-valuemin="0"
                                                aria-valuemax="{{ config('academic.target_pertemuan') }}"></div>
                                        </div>
                                        <span
                                            class="fw-bold">{{ $kelas->presensi_pertemuans_count }}/{{ config('academic.target_pertemuan') }}</span>
                                    </div>
                                </td>
                                <td class="text-center">
                                    <span class="badge bg-label-{{ $kelas->status_warna }}">{{ $kelas->status_label }}</span>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" class="text-center py-5">
                                    <p class="text-muted">Data tidak ditemukan.</p>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            <div class="card-footer pb-4">
                {{ $kelasKuliahs->links() }}
            </div>
        </div>
    </div>
@endsection

@push('scripts')
    <script>
        $(function () {
            var dt_monitoring = $('.datatables-monitoring');
            if (dt_monitoring.length) {
                dt_monitoring.DataTable({
                    responsive: false,
                    scrollX: true,
                    dom: 't', // Only show table, hide internal search/pagination
                    paging: false,
                    searching: false,
                    info: false
                });
            }
        });
    </script>
@endpush
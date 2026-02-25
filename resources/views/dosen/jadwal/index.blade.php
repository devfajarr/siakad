@extends('layouts.app')

@section('content')
    <div class="row">
        <div class="col-12">
            <div class="card mb-4">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <div>
                        <h5 class="mb-0">Jadwal Mengajar Saya</h5>
                        <small class="text-muted">Semester: {{ $activeSemester->nama_semester ?? $id_semester }}</small>
                    </div>
                    <form action="{{ route('dosen.jadwal.index') }}" method="GET" class="d-flex align-items-center">
                        <label for="id_semester" class="me-2 mb-0 fw-medium">Filter:</label>
                        <select name="id_semester" id="id_semester" class="form-select form-select-sm w-auto" onchange="this.form.submit()">
                            @foreach ($semesters as $sem)
                                <option value="{{ $sem->id_semester }}" {{ $id_semester == $sem->id_semester ? 'selected' : '' }}>
                                    {{ $sem->nama_semester }}
                                </option>
                            @endforeach
                        </select>
                    </form>
                </div>
                <div class="card-body">
                    @php
                        $days = [
                            1 => 'Senin',
                            2 => 'Selasa',
                            3 => 'Rabu',
                            4 => 'Kamis',
                            5 => 'Jumat',
                            6 => 'Sabtu',
                            7 => 'Minggu',
                        ];
                        $todayNum = date('N'); // 1 (for Monday) through 7 (for Sunday)
                    @endphp

                    <div class="nav-align-top mb-4">
                        <ul class="nav nav-tabs nav-fill" role="tablist">
                            @foreach ($days as $num => $name)
                                <li class="nav-item">
                                    <button type="button" class="nav-link {{ $num == $todayNum ? 'active' : '' }}" role="tab"
                                        data-bs-toggle="tab" data-bs-target="#navs-{{ $num }}"
                                        aria-controls="navs-{{ $num }}" aria-selected="{{ $num == $todayNum ? 'true' : 'false' }}">
                                        @if ($num == $todayNum)
                                            <span class="badge badge-dot bg-primary me-1"></span>
                                        @endif
                                        {{ $name }}
                                        <span class="badge rounded-pill bg-label-secondary ms-1">{{ count($jadwalGrouped[$num] ?? []) }}</span>
                                    </button>
                                </li>
                            @endforeach
                        </ul>
                        <div class="tab-content border-0 px-0 pb-0">
                            @foreach ($days as $num => $name)
                                <div class="tab-pane fade {{ $num == $todayNum ? 'show active' : '' }}" id="navs-{{ $num }}"
                                    role="tabpanel">
                                    @if (empty($jadwalGrouped[$num]))
                                        <div class="text-center py-5">
                                            <i class="ri-calendar-event-line ri-3x text-light mb-3"></i>
                                            <p class="text-muted">Tidak ada jadwal mengajar pada hari {{ $name }}.</p>
                                        </div>
                                    @else
                                        <div class="row g-4">
                                            @foreach ($jadwalGrouped[$num] as $item)
                                                <div class="col-12 col-md-6 col-lg-4">
                                                    <div class="card h-100 border shadow-none {{ $num == $todayNum ? 'border-primary' : '' }}">
                                                        <div class="card-body p-3">
                                                            <div class="d-flex justify-content-between align-items-start mb-2">
                                                                <span class="badge bg-label-primary">{{ $item['jam_mulai'] }} - {{ $item['jam_selesai'] }}</span>
                                                                <small class="text-muted"><i class="ri-map-pin-2-line me-1"></i>{{ $item['ruang'] }}</small>
                                                            </div>
                                                            <h6 class="mb-1 fw-bold">{{ $item['nama_mk'] }}</h6>
                                                            <div class="d-flex align-items-center mb-0">
                                                                <small class="text-muted fw-medium">{{ $item['kode_mk'] }}</small>
                                                                <span class="mx-2 text-muted">•</span>
                                                                <small class="text-muted">Kelas {{ $item['kelas'] }}</small>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                            @endforeach
                                        </div>
                                    @endif
                                </div>
                            @endforeach
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection

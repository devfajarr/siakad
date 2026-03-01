@extends('layouts.mahasiswa')

@section('content')
<div class="container-xxl flex-grow-1 container-p-y">
    <h4 class="fw-bold mb-4">
        <span class="text-muted fw-light">Dashboard /</span> Kuisioner Akademik
    </h4>

    @include('components.alert')

    <div class="row">
        <!-- Instrumen Pelayanan Akademik -->
        <div class="col-md-6 mb-4">
            <h5 class="fw-semibold mb-3"><i class="ri-building-4-line text-primary me-2"></i> Evaluasi Pelayanan Akademik</h5>
            @forelse($kuesionerPelayanan as $kp)
                @php $isDone = $progressPelayanan[$kp->id] ?? false; @endphp
                <div class="card mb-3 @if($isDone) border-success @else border-warning @endif">
                    <div class="card-body">
                        <div class="d-flex justify-content-between align-items-start mb-2">
                            <h6 class="card-title fw-bold text-primary mb-0">{{ $kp->judul }}</h6>
                            @if($isDone)
                                <span class="badge bg-label-success"><i class="ri-checkbox-circle-fill me-1"></i> Selesai</span>
                            @else
                                <span class="badge bg-label-warning"><i class="ri-error-warning-fill me-1"></i> Wajib Diisi</span>
                            @endif
                        </div>
                        <p class="card-text text-muted small mb-3">{{ Str::limit($kp->deskripsi, 80) }}</p>
                        
                        <div class="d-flex justify-content-between align-items-center">
                            <span class="fs-tiny text-muted">Syarat Cetak: {{ $kp->target_ujian }}</span>
                            @if(!$isDone)
                                <a href="{{ route('mahasiswa.kuisioner.show', $kp->id) }}" class="btn btn-sm btn-primary">Mulai Isi Formulir <i class="ri-arrow-right-line ms-1"></i></a>
                            @else
                                <button class="btn btn-sm btn-outline-success" disabled>Terima Kasih</button>
                            @endif
                        </div>
                    </div>
                </div>
            @empty
                <div class="alert alert-secondary text-center">Belum ada Instrumen Kuesioner Pelayanan saat ini.</div>
            @endforelse
        </div>

        <!-- Instrumen Kinerja Dosen -->
        <div class="col-md-6 mb-4">
            <h5 class="fw-semibold mb-3"><i class="ri-group-line text-info me-2"></i> Evaluasi Kinerja Dosen</h5>
            @forelse($kuesionerDosen as $kd)
                @php 
                    $prog = $progressDosen[$kd->id] ?? ['done' => 0, 'total' => 0, 'completed' => true]; 
                    $percent = $prog['total'] > 0 ? round(($prog['done'] / $prog['total']) * 100) : 100;
                @endphp
                <div class="card mb-3 @if($prog['completed']) border-success @else border-danger @endif">
                    <div class="card-body">
                        <div class="d-flex justify-content-between align-items-start mb-2">
                            <h6 class="card-title fw-bold text-info mb-0">{{ $kd->judul }}</h6>
                            @if($prog['completed'])
                                <span class="badge bg-label-success"><i class="ri-checkbox-circle-fill me-1"></i> Lengkap</span>
                            @else
                                <span class="badge bg-label-danger">{{ $prog['done'] }} / {{ $prog['total'] }} Kelas</span>
                            @endif
                        </div>
                        <p class="card-text text-muted small mb-3">{{ Str::limit($kd->deskripsi, 80) }}</p>
                        
                        <div class="mb-3">
                            <div class="d-flex justify-content-between text-muted fs-tiny mb-1">
                                <span>Progress Penyelesaian</span>
                                <span>{{ $percent }}%</span>
                            </div>
                            <div class="progress" style="height: 6px;">
                                <div class="progress-bar @if($prog['completed']) bg-success @else bg-danger @endif" role="progressbar" style="width: {{ $percent }}%" aria-valuenow="{{ $percent }}" aria-valuemin="0" aria-valuemax="100"></div>
                            </div>
                        </div>

                        <div class="d-flex justify-content-between align-items-center">
                            <span class="fs-tiny text-muted">Syarat Cetak: {{ $kd->target_ujian }}</span>
                            @if(!$prog['completed'])
                                <button type="button" class="btn btn-sm btn-danger" data-bs-toggle="collapse" data-bs-target="#collapseKelas{{ $kd->id }}">
                                    Pilih Kelas <i class="ri-arrow-down-s-line ms-1"></i>
                                </button>
                            @else
                                <button class="btn btn-sm btn-outline-success" disabled>Terima Kasih</button>
                            @endif
                        </div>

                        <!-- Collapse Pilihan Kelas Pembelajaran Tersedia -->
                        <div class="collapse mt-3" id="collapseKelas{{ $kd->id }}">
                            <div class="list-group list-group-flush rounded-3 border">
                                @foreach($pesertaKelasArray as $pk)
                                    @foreach($pk->kelasKuliah->dosenPengajar as $pengajar)
                                        @php
                                            $dosenId = $pengajar->id_dosen_alias_lokal ?? $pengajar->id_dosen;
                                            
                                            if (!$dosenId) continue;

                                            $sudahIni = \App\Models\KuisionerSubmission::where('id_kuisioner', $kd->id)
                                                        ->where('id_mahasiswa', auth()->user()->mahasiswa->id)
                                                        ->where('id_kelas_kuliah', $pk->id_kelas_kuliah)
                                                        ->where('id_dosen', $dosenId)
                                                        ->exists();
                                        @endphp
                                        @if(!$sudahIni)
                                            <a href="{{ route('mahasiswa.kuisioner.show', ['kuisioner' => $kd->id, 'kelas' => $pk->kelasKuliah->id, 'dosen' => $dosenId]) }}" class="list-group-item list-group-item-action d-flex justify-content-between align-items-center p-2">
                                                <div>
                                                    <div class="fw-semibold" style="font-size: 0.85rem;">{{ $pk->kelasKuliah->mataKuliah->nama_mata_kuliah }}</div>
                                                    <div class="text-muted" style="font-size: 0.75rem;">
                                                        <i class="ri-user-line me-1"></i> {{ $pengajar->nama_tampilan }}
                                                    </div>
                                                </div>
                                                <i class="ri-file-edit-line text-primary"></i>
                                            </a>
                                        @else
                                            <div class="list-group-item bg-lighter text-muted d-flex justify-content-between align-items-center p-2" style="opacity: 0.6">
                                                <div>
                                                    <div class="fw-semibold text-decoration-line-through" style="font-size: 0.85rem;">{{ $pk->kelasKuliah->mataKuliah->nama_mata_kuliah }}</div>
                                                    <div class="text-muted" style="font-size: 0.75rem;">
                                                        <i class="ri-user-line me-1"></i> {{ $pengajar->nama_tampilan }} (Selesai Dievaluasi)
                                                    </div>
                                                </div>
                                                <i class="ri-checkbox-circle-fill text-success"></i>
                                            </div>
                                        @endif
                                    @endforeach
                                @endforeach
                            </ul>
                        </div>
                    </div>
                </div>
            @empty
                <div class="alert alert-secondary text-center">Belum ada Instrumen Evaluasi Dosen saat ini.</div>
            @endforelse
        </div>
    </div>
</div>
@endsection

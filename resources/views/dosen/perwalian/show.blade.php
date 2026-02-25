@extends('layouts.app')

@section('title', 'Detail KRS Mahasiswa')

@section('content')
    <div class="d-flex align-items-center justify-content-between mb-4">
        <h4 class="fw-bold py-3 mb-0"><span class="text-muted fw-light">Dosen / Perwalian /</span> Detail KRS</h4>
        <a href="{{ route('dosen.perwalian.index') }}" class="btn btn-outline-secondary">
            <i class="ri-arrow-left-line me-1"></i> Kembali
        </a>
    </div>

    <div class="row">
        <!-- Student Info -->
        <div class="col-md-4">
            <div class="card mb-4">
                <div class="card-body text-center">
                    <div class="avatar avatar-xl mx-auto mb-3">
                        <span class="avatar-initial rounded-circle bg-label-primary fs-2">
                            {{ substr($mahasiswa->nama_mahasiswa, 0, 1) }}
                        </span>
                    </div>
                    <h5 class="mb-1">{{ $mahasiswa->nama_mahasiswa }}</h5>
                    <span class="badge bg-label-secondary mb-3">{{ $mahasiswa->nim }}</span>
                    <hr>
                    <div class="text-start">
                        <p class="mb-1 small text-muted">Program Studi</p>
                        <p class="fw-bold">{{ $mahasiswa->riwayatAktif?->programStudi?->nama_program_studi }}</p>
                        <p class="mb-1 small text-muted">Semester Berjalan</p>
                        <p class="fw-bold">{{ $semesterAktif->nama_semester }}</p>
                    </div>
                </div>
            </div>
            
            @php
                $isPending = $krsItems->contains('status_krs', 'pending');
                $isDraft = $krsItems->contains('status_krs', 'paket');
                $isAcc = $krsItems->every(fn($i) => $i->status_krs === 'acc');
            @endphp

            @if($isPending || $isDraft)
                <div class="card bg-label-warning border-warning mb-4">
                    <div class="card-body text-center">
                        <p class="mb-3">Mahasiswa telah mengajukan KRS untuk semester ini.</p>
                        <form action="{{ route('dosen.perwalian.approve', $mahasiswa->id) }}" method="POST" class="mb-2">
                            @csrf
                            <input type="hidden" name="id_semester" value="{{ $semesterAktif->id_semester }}">
                            <button type="submit" class="btn btn-warning w-100">
                                <i class="ri-check-double-line me-1"></i> ACC KRS SEKARANG
                            </button>
                        </form>
                        <a href="{{ route('dosen.perwalian.print', [$mahasiswa->id, 'autoprint' => 1]) }}" target="_blank" class="btn btn-outline-warning w-100">
                            <i class="ri-printer-line me-1"></i> Cetak Draft KRS
                        </a>
                    </div>
                </div>
            @elseif($isAcc)
                <div class="card bg-label-success border-success text-center mb-4">
                    <div class="card-body">
                        <i class="ri-checkbox-circle-line ri-3x text-success mb-2"></i>
                        <h5 class="text-success">KRS TELAH DI-ACC</h5>
                        <p class="mb-3 small">Disetujui pada: {{ $krsItems->first()->last_acc_at ? $krsItems->first()->last_acc_at->format('d/m/Y H:i') : '-' }}</p>
                        <a href="{{ route('dosen.perwalian.print', [$mahasiswa->id, 'autoprint' => 1]) }}" target="_blank" class="btn btn-success w-100">
                            <i class="ri-printer-line me-1"></i> CETAK KRS RESMI
                        </a>
                    </div>
                </div>
            @endif
        </div>

        <!-- KRS List -->
        <div class="col-md-8">
            <div class="card">
                <div class="card-header border-bottom">
                    <h5 class="card-title mb-0">Rencana Studi Mahasiswa</h5>
                </div>
                <div class="table-responsive">
                    <table class="table table-hover mb-0">
                        <thead class="table-light">
                            <tr>
                                <th>No</th>
                                <th>Kode MK</th>
                                <th>Mata Kuliah</th>
                                <th class="text-center">SKS</th>
                                <th>Status</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($krsItems as $index => $item)
                                <tr>
                                    <td>{{ $index + 1 }}</td>
                                    <td><span class="badge bg-label-dark">{{ $item->kelasKuliah->mataKuliah->kode_mk }}</span></td>
                                    <td>
                                        <div class="d-flex flex-column">
                                            <span class="fw-bold">{{ $item->kelasKuliah->mataKuliah->nama_mk }}</span>
                                            <small class="text-muted">{{ $item->kelasKuliah->nama_kelas_kuliah }}</small>
                                        </div>
                                    </td>
                                    <td class="text-center">{{ $item->kelasKuliah->sks_mk }}</td>
                                    <td>
                                        @if($item->status_krs === 'acc')
                                            <span class="badge bg-success">ACC</span>
                                        @elseif($item->status_krs === 'pending')
                                            <span class="badge bg-warning">Pending</span>
                                        @else
                                            <span class="badge bg-info">Paket</span>
                                        @endif
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="5" class="text-center py-4">Mahasiswa belum mengisi KRS.</td>
                                </tr>
                            @endforelse
                        </tbody>
                        <tfoot class="table-light fw-bold">
                            <tr>
                                <td colspan="3" class="text-end">Total SKS :</td>
                                <td class="text-center">{{ $krsItems->sum(fn($i) => $i->kelasKuliah->sks_mk) }}</td>
                                <td></td>
                            </tr>
                        </tfoot>
                    </table>
                </div>
            </div>
        </div>
    </div>
@endsection

@extends('layouts.app')

@section('content')
    <div class="container-xxl flex-grow-1 container-p-y">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <div>
                <h4 class="fw-bold mb-1">
                    <a href="{{ route('dosen.kuisioner.index') }}" class="text-muted fw-light">Kuesioner /</a> Dashboard
                    Laporan
                </h4>
                <div class="d-flex align-items-center gap-2 mt-2">
                    <span class="badge bg-label-primary fs-6">{{ $kuisioner->judul }}</span>
                    <span class="badge bg-label-info">{{ $kuisioner->target_ujian }}</span>
                    <span class="text-muted fs-tiny"><i
                            class="ri-calendar-line me-1"></i>{{ $kuisioner->semester->nama_semester ?? '' }}</span>
                </div>
            </div>
            <div>
                <a href="{{ route('dosen.kuisioner.index') }}" class="btn btn-outline-secondary">
                    <i class="ri-arrow-left-line me-1"></i> Kembali
                </a>
            </div>
        </div>

        <div class="row g-4 mb-4">
            <!-- Partisipasi Card -->
            <div class="col-sm-6 col-lg-4">
                <div class="card h-100">
                    <div class="card-body">
                        <div class="d-flex align-items-center justify-content-between">
                            <div class="content-left">
                                <span class="fw-semibold">Tingkat Partisipasi</span>
                                <div class="d-flex align-items-end mt-2">
                                    <h3 class="mb-0 me-2">{{ $coverage }}%</h3>
                                    <small class="text-success">(Coverage)</small>
                                </div>
                                <small class="mb-0 text-muted">{{ $totalResponden }} dari {{ $targetPartisipan }}
                                    Target</small>
                            </div>
                            <div class="avatar">
                                <span class="avatar-initial rounded bg-label-primary">
                                    <i class="ri-group-line ri-24px"></i>
                                </span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Grand Average Score -->
            <div class="col-sm-6 col-lg-4">
                <div class="card h-100">
                    <div class="card-body">
                        <div class="d-flex align-items-center justify-content-between">
                            <div class="content-left">
                                <span class="fw-semibold">Rata-Rata Indeks Skala</span>
                                <div class="d-flex align-items-center mt-2">
                                    <h3 class="mb-0 me-2">{{ number_format($grandAverage, 2) }}</h3>
                                    <i class="ri-star-fill text-warning mb-1"></i>
                                </div>
                                <small class="mb-0 text-muted">Dari Skala 1.00 - 5.00</small>
                            </div>
                            <div class="avatar">
                                <span class="avatar-initial rounded bg-label-warning">
                                    <i class="ri-bar-chart-grouped-line ri-24px"></i>
                                </span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Label Kepuasan / Kesimpulan -->
            <div class="col-sm-6 col-lg-4">
                <div class="card border-{{ $grandKesimpulan['color'] }} h-100">
                    <div class="card-body text-center d-flex flex-column justify-content-center">
                        <h5 class="card-title text-muted mb-1">Kesimpulan Penilaian Otomatis</h5>
                        <h2 class="text-{{ $grandKesimpulan['color'] }} fw-bold mb-0 mt-2">{{ $grandKesimpulan['teks'] }}
                        </h2>
                    </div>
                </div>
            </div>
        </div>

        <!-- Data Table Rekap Per Pertanyaan -->
        <div class="card mb-4">
            <h5 class="card-header border-bottom">Perincian Skor Rata-Rata per Pertanyaan (Skala Likert)</h5>
            <div class="table-responsive text-nowrap">
                <table class="table table-hover">
                    <thead class="table-light">
                        <tr>
                            <th style="width: 5%">No</th>
                            <th style="width: 50%">Aspek Pertanyaan</th>
                            <th style="width: 20%" class="text-center">Nilai Rata-Rata (AVG)</th>
                            <th style="width: 25%" class="text-center">Kategori Indeks</th>
                        </tr>
                    </thead>
                    <tbody class="table-border-bottom-0">
                        @forelse($rekapPertanyaan as $index => $rp)
                            <tr>
                                <td>{{ $index + 1 }}</td>
                                <td class="text-wrap"><strong>{{ $rp['teks'] }}</strong></td>
                                <td class="text-center">
                                    <div class="d-flex align-items-center justify-content-center">
                                        <span class="fw-bold fs-5 me-1">{{ number_format($rp['avg'], 2) }}</span>
                                        <i class="ri-star-fill text-warning fs-6"></i>
                                    </div>
                                </td>
                                <td class="text-center">
                                    <span
                                        class="badge bg-label-{{ $rp['label']['color'] }} w-100 py-2">{{ $rp['label']['teks'] }}</span>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="4" class="text-center text-muted py-4">
                                    <i class="ri-survey-line ri-2x mb-2 d-block"></i>
                                    Belum ada instrumen perhitungan Likert (Skala 1-5) pada formulir ini.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Sample Esai Terbuka -->
        @if($esaiTerbaru->isNotEmpty())
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center border-bottom">
                    <h5 class="mb-0"><i class="ri-chat-quote-line text-primary me-2"></i> Sorotan Masukan Bebas (Esai) Mahasiswa
                    </h5>
                </div>
                <div class="card-body pt-3">
                    <div class="list-group list-group-flush">
                        @foreach($esaiTerbaru as $esai)
                            <div class="list-group-item list-group-item-action d-flex align-items-start py-3">
                                <div class="avatar me-3 mt-1">
                                    <span class="avatar-initial rounded-circle bg-label-secondary"><i
                                            class="ri-user-smile-line"></i></span>
                                </div>
                                <div class="w-100">
                                    <div class="d-flex justify-content-between mb-1">
                                        <h6 class="mb-0 text-primary">{{ Str::limit($esai->pertanyaan->teks_pertanyaan, 60) }}</h6>
                                        <small class="text-muted">{{ $esai->created_at->diffForHumans() }}</small>
                                    </div>
                                    <p class="mb-0 text-dark" style="font-size: 0.9rem;">"{{ $esai->jawaban_teks }}"</p>
                                </div>
                            </div>
                        @endforeach
                    </div>
                    <div class="text-center mt-3">
                        <span class="text-muted fs-tiny">Menampilkan 5 masukan terbuka terbaru... Fitur ekspor penuh CSV akan
                            hadir pada pembaruan SIAKAD berikutnya.</span>
                    </div>
                </div>
            </div>
        @endif
    </div>
@endsection
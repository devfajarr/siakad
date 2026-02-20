<div class="card shadow-sm">
    <div class="card-header">
        <h5 class="mb-0">KRS Mahasiswa</h5>
    </div>
    <div class="card-body">
        <div class="alert alert-secondary d-flex align-items-center" role="alert">
            <span class="alert-icon text-secondary me-2">
                <i class="ri-information-line ri-lg"></i>
            </span>
            Menampilkan Data berdasarkan semester : <strong>2025/2026 Genap</strong>
        </div>

        <div class="d-flex justify-content-end mb-3 gap-2">
            <button class="btn btn-info btn-sm">
                <i class="ri-refresh-line me-1"></i> UPDATE KRS
            </button>
            <button class="btn btn-success btn-sm">
                <i class="ri-printer-line me-1"></i> CETAK KRS
            </button>
        </div>

        <div class="table-responsive text-nowrap mb-4">
            <table class="table table-bordered table-striped">
                <thead>
                    <tr>
                        <th width="5%">Action</th>
                        <th width="5%">No</th>
                        <th>Kode MK</th>
                        <th>Nama MK</th>
                        <th>Kelas</th>
                        <th class="text-center">Bobot MK (sks)</th>
                    </tr>
                </thead>
                <tbody>
                    @php
                        $dummyKRS = [
                            ['code' => 'TIK207', 'name' => 'ALJABAR LINIER DAN MATRIK', 'class' => 'TI2', 'sks' => 3.00],
                            ['code' => 'TIK209', 'name' => 'KONSEP BASIS DATA', 'class' => 'TI2', 'sks' => 2.00],
                            ['code' => 'TIK210', 'name' => 'STRUKTUR DATA DAN LOGARITMA', 'class' => 'TI2', 'sks' => 3.00],
                            ['code' => 'TIK212', 'name' => 'DESAIN WEB', 'class' => 'TI2', 'sks' => 3.00],
                            ['code' => 'TIK213', 'name' => 'PROYEK 1 (Hardware dan Jaringan)', 'class' => 'TI2', 'sks' => 3.00],
                            ['code' => 'TIK425', 'name' => 'GRAFIKA KOMPUTER', 'class' => 'TI2', 'sks' => 3.00],
                            ['code' => 'TIK532', 'name' => 'APLIKASI SERVER', 'class' => 'TI2', 'sks' => 3.00],
                        ];
                        $totalSks = collect($dummyKRS)->sum('sks');
                    @endphp

                    @foreach($dummyKRS as $index => $mk)
                        <tr>
                            <td class="text-center">
                                <button class="btn btn-sm btn-icon btn-text-danger rounded-pill"><i
                                        class="ri-delete-bin-line"></i></button>
                            </td>
                            <td>{{ $index + 1 }}</td>
                            <td>{{ $mk['code'] }}</td>
                            <td>{{ $mk['name'] }}</td>
                            <td class="text-center">{{ $mk['class'] }}</td>
                            <td class="text-center">{{ number_format($mk['sks'], 2) }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

        <div class="alert alert-secondary p-3 mb-0">
            <strong>TOTAL SKS MAHASISWA ADALAH : {{ $totalSks }} SKS</strong>
        </div>
    </div>
</div>
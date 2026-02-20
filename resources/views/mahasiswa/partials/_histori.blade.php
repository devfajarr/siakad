<div class="card shadow-sm">
    <div class="card-header d-flex justify-content-between align-items-center">
        <h5 class="mb-0">Histori dan Restore Pendidikan Mahasiswa</h5>
    </div>
    <div class="card-body">
        {{-- Flash Messages --}}
        @if(session('success'))
            <div class="alert alert-success alert-dismissible fade show" role="alert">
                <i class="ri-check-line me-2"></i>{{ session('success') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        @endif
        @if(session('error'))
            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                <i class="ri-error-warning-line me-2"></i>{{ session('error') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        @endif
        @if($errors->any())
            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                <i class="ri-error-warning-line me-2"></i>
                <strong>Validasi gagal:</strong>
                <ul class="mb-0 mt-1">
                    @foreach($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        @endif

        <div class="nav-align-top mb-4">
            <ul class="nav nav-tabs" role="tablist">
                <li class="nav-item">
                    <button type="button" class="nav-link active" role="tab" data-bs-toggle="tab"
                        data-bs-target="#navs-top-histori" aria-controls="navs-top-histori"
                        aria-selected="true">HISTORI</button>
                </li>
                <li class="nav-item">
                    <button type="button" class="nav-link" role="tab" data-bs-toggle="tab"
                        data-bs-target="#navs-top-restore" aria-controls="navs-top-restore"
                        aria-selected="false">RESTORE</button>
                </li>
            </ul>
            <div class="tab-content">
                <div class="tab-pane fade show active" id="navs-top-histori" role="tabpanel">
                    <div class="d-flex justify-content-end mb-3">
                        <button class="btn btn-primary btn-sm" data-bs-toggle="modal"
                            data-bs-target="#modalRiwayatPendidikan" id="btnTambahRiwayat">
                            <i class="ri-add-line me-1"></i> TAMBAH
                        </button>
                    </div>
                    <div class="table-responsive text-nowrap">
                        <table class="table table-bordered table-hover">
                            <thead>
                                <tr>
                                    <th>Action</th>
                                    <th>Status</th>
                                    <th>NIM</th>
                                    <th>Jenis Pendaftaran</th>
                                    <th>Periode</th>
                                    <th>Tanggal Masuk</th>
                                    <th>Perguruan Tinggi</th>
                                    <th>Program Studi</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($mahasiswa->riwayatPendidikans as $riwayat)
                                    <tr>
                                        <td>
                                            <div class="dropdown">
                                                <button type="button" class="btn p-0 dropdown-toggle hide-arrow"
                                                    data-bs-toggle="dropdown"><i class="ri-more-2-line"></i></button>
                                                <div class="dropdown-menu">
                                                    <a class="dropdown-item btn-edit-riwayat" href="javascript:void(0);"
                                                        data-id="{{ $riwayat->id }}"
                                                        data-url="{{ route('admin.riwayat-pendidikan.edit', $riwayat->id) }}">
                                                        <i class="ri-pencil-line me-1"></i> Edit
                                                    </a>
                                                    <form
                                                        action="{{ route('admin.riwayat-pendidikan.destroy', $riwayat->id) }}"
                                                        method="POST" class="d-inline"
                                                        onsubmit="return confirm('Yakin ingin menghapus riwayat pendidikan ini?')">
                                                        @csrf
                                                        @method('DELETE')
                                                        <button type="submit" class="dropdown-item text-danger">
                                                            <i class="ri-delete-bin-6-line me-1"></i> Delete
                                                        </button>
                                                    </form>
                                                </div>
                                            </div>
                                        </td>
                                        <td>
                                            @if($riwayat->is_synced)
                                                <span class="badge bg-success">sudah sync</span>
                                            @else
                                                <span class="badge bg-warning">belum sync</span>
                                            @endif
                                        </td>
                                        <td>{{ $riwayat->nim }}</td>
                                        <td>{{ $riwayat->jenisDaftar->nama_jenis_daftar }}</td>
                                        <td>{{ $riwayat->semester->nama_semester ?? '' }}</td>
                                        <td>{{ $riwayat->tanggal_daftar?->format('d F Y') ?? '-' }}</td>
                                        <td>{{ $riwayat->perguruanTinggi->nama_perguruan_tinggi ?? $riwayat->id_perguruan_tinggi ?? '-' }}
                                        </td>
                                        <td>{{ $riwayat->prodi->nama_program_studi ?? $riwayat->id_prodi ?? '-' }}</td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="8" class="text-center text-muted py-4">
                                            <i class="ri-information-line me-1"></i> Belum ada data riwayat pendidikan.
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
                <div class="tab-pane fade" id="navs-top-restore" role="tabpanel">
                    <p class="mb-0">Data Restore tidak tersedia.</p>
                </div>
            </div>
        </div>
    </div>
</div>

{{-- Modal Riwayat Pendidikan --}}
@include('mahasiswa.partials._modal_histori')
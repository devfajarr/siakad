@extends('layouts.app')

@section('title', 'Manajemen Data Dosen')

@push('styles')
    <link rel="stylesheet" href="{{ asset('assets/vendor/libs/datatables-bs5/datatables.bootstrap5.css') }}" />
    <link rel="stylesheet" href="{{ asset('assets/vendor/libs/datatables-responsive-bs5/responsive.bootstrap5.css') }}" />
    <link rel="stylesheet" href="{{ asset('assets/vendor/libs/datatables-buttons-bs5/buttons.bootstrap5.css') }}" />
@endpush

@section('content')
    {{-- Page Header --}}
    <h4 class="fw-bold py-3 mb-2"><span class="text-muted fw-light">Master Data /</span> Dosen</h4>

    <div class="card">
        <div class="card-header border-bottom d-flex flex-wrap justify-content-between align-items-center gap-3">
            <h5 class="card-title mb-0">Daftar Dosen</h5>
            <div class="d-flex gap-2 align-items-center flex-wrap">
                <form id="syncForm" action="{{ route('admin.dosen.sync') }}" method="POST" class="d-inline">
                    @csrf
                    <button type="button" id="btnSync" class="btn btn-outline-info waves-effect">
                        <i class="ri-refresh-line me-1"></i> Sync Dosen
                    </button>
                </form>
                <button type="button" class="btn btn-primary waves-effect waves-light" data-bs-toggle="modal"
                    data-bs-target="#addDosenModal">
                    <i class="ri-add-line me-1"></i> Add Dosen
                </button>
            </div>
        </div>

        <div class="card-datatable table-responsive">
            <table id="dosenTable" class="table table-bordered table-hover text-nowrap">
                <thead class="table-light">
                    <tr>
                        <th width="80px">Action</th>
                        <th width="50px">No.</th>
                        <th>Nama</th>
                        <th>NIDN</th>
                        <th>NIP</th>
                        <th>Jenis Kelamin</th>
                        <th>Agama</th>
                        <th>Status</th>
                        <th>Tanggal Lahir</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($dosen as $index => $item)
                        <tr>
                            <td>
                                <div class="d-flex gap-1">
                                    <a href="#" class="btn btn-icon btn-sm btn-info rounded-pill" title="Detail">
                                        <i class="ri-eye-line"></i>
                                    </a>
                                    <a href="#" class="btn btn-icon btn-sm btn-warning rounded-pill" title="Edit">
                                        <i class="ri-pencil-line"></i>
                                    </a>
                                </div>
                            </td>
                            <td>{{ $index + 1 }}</td>
                            <td>
                                <span class="fw-semibold text-primary">{{ $item->nama }}</span>
                            </td>
                            <td>{{ $item->nidn ?? '-' }}</td>
                            <td>{{ $item->nip ?? '-' }}</td>
                            <td>
                                @if ($item->jenis_kelamin === 'L')
                                    Laki - Laki
                                @elseif($item->jenis_kelamin === 'P')
                                    Perempuan
                                @else
                                    -
                                @endif
                            </td>
                            <td>
                                @php
                                    $agamaMap = [
                                        1 => 'Islam',
                                        2 => 'Kristen',
                                        3 => 'Katolik',
                                        4 => 'Hindu',
                                        5 => 'Budha',
                                        6 => 'Konghucu',
                                        98 => 'Tidak diisi',
                                        99 => 'Lain-lain',
                                    ];
                                @endphp
                                {{ $agamaMap[$item->id_agama] ?? '-' }}
                            </td>
                            <td>
                                @if ($item->is_active)
                                    <span class="badge bg-label-success rounded-pill">Aktif</span>
                                @else
                                    <span class="badge bg-label-danger rounded-pill">Tidak Aktif</span>
                                @endif
                            </td>
                            <td>{{ $item->tanggal_lahir ? $item->tanggal_lahir->format('d/m/Y') : '-' }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>

    {{-- Add Dosen Modal --}}
    <div class="modal fade" id="addDosenModal" tabindex="-1" aria-labelledby="addDosenModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="addDosenModalLabel">
                        <i class="ri-user-add-line me-2"></i>Tambah Dosen Baru
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <form id="formAddDosen">
                        <div class="row g-3">
                            <div class="col-md-6">
                                <label class="form-label" for="dosenNama">Nama <span class="text-danger">*</span></label>
                                <input type="text" id="dosenNama" class="form-control" placeholder="Nama lengkap" />
                            </div>
                            <div class="col-md-6">
                                <label class="form-label" for="dosenNidn">NIDN</label>
                                <input type="text" id="dosenNidn" class="form-control"
                                    placeholder="Nomor Induk Dosen Nasional" />
                            </div>
                            <div class="col-md-6">
                                <label class="form-label" for="dosenNip">NIP</label>
                                <input type="text" id="dosenNip" class="form-control" placeholder="Nomor Induk Pegawai" />
                            </div>
                            <div class="col-md-6">
                                <label class="form-label" for="dosenEmail">Email</label>
                                <input type="email" id="dosenEmail" class="form-control" placeholder="email@domain.com" />
                            </div>
                            <div class="col-md-6">
                                <label class="form-label" for="dosenJk">Jenis Kelamin</label>
                                <select id="dosenJk" class="form-select">
                                    <option value="">-- Pilih --</option>
                                    <option value="L">Laki - Laki</option>
                                    <option value="P">Perempuan</option>
                                </select>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label" for="dosenAgama">Agama</label>
                                <select id="dosenAgama" class="form-select">
                                    <option value="">-- Pilih --</option>
                                    @foreach ($agamaList as $agama)
                                        <option value="{{ $agama->id_agama }}">{{ $agama->nama_agama }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label" for="dosenTglLahir">Tanggal Lahir</label>
                                <input type="date" id="dosenTglLahir" class="form-control" />
                            </div>
                            <div class="col-md-6">
                                <label class="form-label" for="dosenStatus">Status</label>
                                <select id="dosenStatus" class="form-select">
                                    <option value="1">Aktif</option>
                                    <option value="0">Tidak Aktif</option>
                                </select>
                            </div>
                        </div>
                    </form>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">
                        <i class="ri-close-line me-1"></i> Batal
                    </button>
                    <button type="button" class="btn btn-primary">
                        <i class="ri-save-line me-1"></i> Simpan
                    </button>
                </div>
            </div>
        </div>
    </div>
@endsection

@push('scripts')
    <script src="{{ asset('assets/vendor/libs/datatables-bs5/datatables-bootstrap5.js') }}"></script>

    <script>
        $(function () {
            // DataTables init
            $('#dosenTable').DataTable({
                responsive: false,
                scrollX: false,
                columnDefs: [{
                    targets: 0,
                    orderable: false,
                    searchable: false,
                }],
                order: [
                    [2, 'asc']
                ],
                language: {
                    search: '',
                    searchPlaceholder: 'Search...',
                    lengthMenu: '_MENU_',
                    info: 'Menampilkan _START_ - _END_ dari _TOTAL_ data',
                    infoEmpty: 'Tidak ada data',
                    emptyTable: 'Tidak ada data dosen.',
                    paginate: {
                        first: '«',
                        last: '»',
                        next: '›',
                        previous: '‹'
                    }
                },
                dom: '<"row"<"col-sm-12 col-md-6"l><"col-sm-12 col-md-6 d-flex justify-content-center justify-content-md-end"f>>t<"row"<"col-sm-12 col-md-6"i><"col-sm-12 col-md-6"p>>',
            });

            // Sync button with SweetAlert confirmation
            $('#btnSync').on('click', function () {
                Swal.fire({
                    title: 'Sinkronisasi Dosen',
                    text: 'Apakah Anda yakin ingin menarik data dosen dari API Pusat? Proses ini mungkin memerlukan waktu.',
                    icon: 'question',
                    showCancelButton: true,
                    confirmButtonText: '<i class="ri-refresh-line me-1"></i> Ya, Sync Sekarang',
                    cancelButtonText: 'Batal',
                    customClass: {
                        confirmButton: 'btn btn-primary me-2',
                        cancelButton: 'btn btn-outline-secondary'
                    },
                    buttonsStyling: false,
                }).then((result) => {
                    if (result.isConfirmed) {
                        document.getElementById('syncForm').submit();
                    }
                });
            });
        });
    </script>
@endpush
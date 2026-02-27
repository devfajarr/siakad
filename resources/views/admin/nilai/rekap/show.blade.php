@extends('layouts.app')

@section('title', 'Detail Rekap Nilai - ' . $prodi->nama_program_studi)

@push('styles')
    <link rel="stylesheet" href="{{ asset('assets/vendor/libs/datatables-bs5/datatables.bootstrap5.css') }}" />
    <link rel="stylesheet" href="{{ asset('assets/vendor/libs/datatables-responsive-bs5/responsive.bootstrap5.css') }}" />
@endpush

@section('content')
    <div class="container-xxl flex-grow-1 container-p-y">
        <div class="d-flex justify-content-between align-items-center py-3 mb-4">
            <h4 class="fw-bold mb-0">
                <span class="text-muted fw-light">Rekapitulasi Nilai /</span> {{ $prodi->nama_program_studi }}
            </h4>
            <a href="{{ route('admin.rekap-nilai.index', ['semester_id' => $semesterId]) }}"
                class="btn btn-outline-secondary">
                <i class="ri-arrow-left-line me-1"></i> Kembali
            </a>
        </div>

        <!-- Bulk Actions -->
        <div class="card mb-4">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-center flex-wrap gap-3">
                    <div>
                        <h5 class="mb-1">Kontrol Penguncian Massal</h5>
                        <p class="mb-0 text-muted small">Semester: {{ $selectedSemester->nama_semester ?? $semesterId }}</p>
                    </div>
                    <div class="d-flex gap-2">
                        <form action="{{ route('admin.rekap-nilai.bulk-lock') }}" method="POST"
                            onsubmit="return confirm('Kunci SELURUH kelas di prodi ini?')">
                            @csrf
                            <input type="hidden" name="id_prodi" value="{{ $prodi->id_prodi }}">
                            <input type="hidden" name="id_semester" value="{{ $semesterId }}">
                            <input type="hidden" name="action" value="lock">
                            <button type="submit" class="btn btn-danger">
                                <i class="ri-lock-2-line me-1"></i> Kunci Semua Kelas
                            </button>
                        </form>
                        <form action="{{ route('admin.rekap-nilai.bulk-lock') }}" method="POST"
                            onsubmit="return confirm('Buka kunci SELURUH kelas di prodi ini?')">
                            @csrf
                            <input type="hidden" name="id_prodi" value="{{ $prodi->id_prodi }}">
                            <input type="hidden" name="id_semester" value="{{ $semesterId }}">
                            <input type="hidden" name="action" value="unlock">
                            <button type="submit" class="btn btn-outline-primary">
                                <i class="ri-lock-unlock-line me-1"></i> Buka Semua Kunci
                            </button>
                        </form>
                    </div>
                </div>
            </div>
        </div>

        <!-- Kelas List -->
        <div class="card">
            <div class="card-datatable table-responsive text-nowrap">
                <table class="table table-hover" id="tableKelas">
                    <thead class="table-light">
                        <tr>
                            <th width="50">No</th>
                            <th>Kode & Mata Kuliah</th>
                            <th>Kelas</th>
                            <th>Dosen Pengampu</th>
                            <th class="text-center">Progres</th>
                            <th class="text-center">Status</th>
                            <th class="text-center">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($kelas as $index => $row)
                            <tr>
                                <td>{{ $index + 1 }}</td>
                                <td>
                                    @if ($row->mataKuliah)
                                        <div class="fw-bold">{{ $row->mataKuliah->kode_mk }}</div>
                                        <small class="text-muted">{{ $row->mataKuliah->nama_mk }}</small>
                                    @else
                                        <div class="fw-bold text-danger">Mata Kuliah Tidak Ditemukan</div>
                                        <small class="text-muted">ID: {{ $row->id_matkul }}</small>
                                    @endif
                                </td>
                                <td>{{ $row->nama_kelas_kuliah }}</td>
                                <td>
                                    <div class="small">{{ $row->dosen_pengampu }}</div>
                                </td>
                                <td class="text-center">
                                    <div class="badge bg-label-info">{{ $row->terisi_count }} / {{ $row->total_peserta }} Mhs
                                    </div>
                                    <div class="small text-muted">{{ $row->persentase }}%</div>
                                </td>
                                <td class="text-center">
                                    @if($row->is_locked)
                                        <span class="badge bg-label-danger" title="Dikunci pada: {{ $row->locked_at }}">
                                            <i class="ri-lock-2-line me-1"></i> LOCKED
                                        </span>
                                    @else
                                        <span class="badge bg-label-success">
                                            <i class="ri-edit-2-line me-1"></i> OPEN
                                        </span>
                                    @endif
                                </td>
                                <td class="text-center">
                                    <div class="d-flex justify-content-center gap-1">
                                        <a href="{{ route('admin.rekap-nilai.override', $row->id_kelas_kuliah) }}"
                                            class="btn btn-sm btn-icon btn-outline-info" title="Input/Edit Nilai (Override)">
                                            <i class="ri-edit-box-line"></i>
                                        </a>
                                        <form action="{{ route('admin.rekap-nilai.toggle-lock', $row->id_kelas_kuliah) }}"
                                            method="POST" class="d-inline">
                                            @csrf
                                            <input type="hidden" name="action"
                                                value="{{ $row->is_locked ? 'unlock' : 'lock' }}">
                                            <button type="submit"
                                                class="btn btn-sm btn-icon {{ $row->is_locked ? 'btn-outline-primary' : 'btn-outline-danger' }}"
                                                title="{{ $row->is_locked ? 'Buka Kunci' : 'Kunci Nilai' }}">
                                                <i class="ri-{{ $row->is_locked ? 'lock-unlock-line' : 'lock-2-line' }}"></i>
                                            </button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" class="text-center py-4">Tidak ada kelas kuliah di prodi ini.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
@endsection

@push('scripts')
    <script src="{{ asset('assets/vendor/libs/datatables-bs5/datatables-bootstrap5.js') }}"></script>
    <script>
        $(document).ready(function () {
            if ($.fn.DataTable) {
                $('#tableKelas').DataTable({
                    language: {
                        search: "Cari:",
                        lengthMenu: "Tampilkan _MENU_ data",
                        info: "Menampilkan _START_ sampai _END_ dari _TOTAL_ data",
                        infoEmpty: "Data tidak ditemukan",
                        zeroRecords: "Data tidak ditemukan",
                        paginate: {
                            first: "Pertama",
                            last: "Terakhir",
                            next: '<i class="ri-arrow-right-s-line"></i>',
                            previous: '<i class="ri-arrow-left-s-line"></i>'
                        }
                    },
                    dom: '<"card-header flex-column flex-md-row"<"head-label text-center"><"dt-action-buttons text-end pt-3 pt-md-0"f>>t<"row"<"col-sm-12 col-md-6"i><"col-sm-12 col-md-6"p>>',
                    displayLength: 25,
                    lengthMenu: [10, 25, 50, 75, 100],
                    order: [[0, 'asc']]
                });

                // Set title in the card header for search area consistency
                $('div.head-label').html('<h5 class="card-title mb-0">Daftar Kelas & Progres Nilai</h5>');
            }
        });
    </script>
@endpush
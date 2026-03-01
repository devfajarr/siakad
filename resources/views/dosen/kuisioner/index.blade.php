@extends('layouts.app')

@section('content')
    <div class="container-xxl flex-grow-1 container-p-y">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <div>
                <h4 class="fw-bold mb-1"><span class="text-muted fw-light">Kuisioner BPMI /</span> Master Form</h4>
                <p class="text-muted mb-0">Manajemen formulir evaluasi pelayanan akademik dan kinerja dosen.</p>
            </div>
            @if(auth()->user()->hasAnyRole(['BPMI', 'bpmi']))
                <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#tambahKuisionerModal">
                    <i class="ri-add-line me-1"></i> Buat Kuesioner
                </button>
            @endif
        </div>

        @include('components.alert')

        <!-- Filter Semester (Select2) -->
        <div class="card mb-4 shadow-sm border-0">
            <div class="card-body py-3">
                <form action="{{ route('dosen.kuisioner.index') }}" method="GET" class="row align-items-center">
                    <div class="col-auto">
                        <label class="form-label mb-0 fw-bold">Pilih Semester:</label>
                    </div>
                    <div class="col-md-4 col-12">
                        <select name="id_semester" class="form-select select2-filter" onchange="this.form.submit()"
                            data-allow-clear="true">
                            @foreach($semesters as $sem)
                                <option value="{{ $sem->id_semester }}" {{ $idSemester == $sem->id_semester ? 'selected' : '' }}>
                                    {{ $sem->nama_semester }} {{ $sem->a_periode_aktif == 1 ? '(Aktif)' : '' }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                </form>
            </div>
        </div>

        <div class="card">
            <div class="card-datatable table-responsive pt-0">
                <table class="datatables-kuisioner table">
                    <thead>
                        <tr>
                            <th class="text-start">No</th>
                            <th>Judul Form</th>
                            <th>Sasaran Target</th>
                            <th>Semester Berlaku</th>
                            <th>Tipe / Kategori</th>
                            <th>Status Form</th>
                            <th class="text-end">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="table-border-bottom-0">
                        @forelse($kuisioners as $index => $item)
                            <tr>
                                <td class="text-start">{{ $index + 1 }}</td>
                                <td>
                                    <strong class="text-heading">{{ $item->judul }}</strong><br>
                                    <small class="text-muted d-block text-truncate" style="max-width: 250px;">
                                        {{ $item->deskripsi ?? 'Tidak ada deskripsi' }}
                                    </small>
                                </td>
                                <td><span class="badge bg-label-info">{{ $item->target_ujian }}</span></td>
                                <td>{{ $item->semester->nama_semester ?? '-' }}</td>
                                <td>
                                    @if($item->tipe === 'dosen')
                                        <span class="badge bg-label-primary"><i class="ri-user-star-line me-1"></i>Kinerja
                                            Dosen</span>
                                    @else
                                        <span class="badge bg-label-success"><i
                                                class="ri-customer-service-2-line me-1"></i>Pelayanan</span>
                                    @endif
                                </td>
                                <td>
                                    @if($item->status === 'published')
                                        <span class="badge bg-label-success">Published</span>
                                    @elseif($item->status === 'draft')
                                        <span class="badge bg-label-secondary">Draft</span>
                                    @else
                                        <span class="badge bg-label-danger">Closed</span>
                                    @endif
                                </td>
                                <td class="text-end">
                                    <div class="dropdown">
                                        <button type="button" class="btn p-0 dropdown-toggle hide-arrow"
                                            data-bs-toggle="dropdown">
                                            <i class="ri-more-2-line"></i>
                                        </button>
                                        <div class="dropdown-menu">
                                            <!-- Tombol Lihat Laporan Hasil (Semua Role) -->
                                            <a class="dropdown-item text-success"
                                                href="{{ route('dosen.kuisioner.show', $item->id) }}"><i
                                                    class="ri-bar-chart-box-line me-1"></i> Lihat Laporan</a>

                                            @if(auth()->user()->hasAnyRole(['BPMI', 'bpmi']))
                                                <!-- Tombol Desain Form -->
                                                <a class="dropdown-item text-primary"
                                                    href="{{ route('dosen.kuisioner.edit', $item->id) }}"><i
                                                        class="ri-layout-masonry-line me-1"></i> Desain Pertanyaan</a>

                                                <!-- Edit Status Modal Trigger -->
                                                <a class="dropdown-item edit-kuisioner-btn" href="javascript:void(0);"
                                                    data-id="{{ $item->id }}" data-judul="{{ $item->judul }}"
                                                    data-deskripsi="{{ $item->deskripsi }}" data-status="{{ $item->status }}"
                                                    data-bs-toggle="modal" data-bs-target="#editKuisionerModal">
                                                    <i class="ri-pencil-line me-1"></i> Ubah Pengaturan
                                                </a>

                                                @if($item->status !== 'published')
                                                    <!-- Hapus Button -->
                                                    <form action="{{ route('dosen.kuisioner.destroy', $item->id) }}" method="POST"
                                                        class="d-inline">
                                                        @csrf
                                                        @method('DELETE')
                                                        <button type="button" class="dropdown-item text-danger delete-btn">
                                                            <i class="ri-delete-bin-6-line me-1"></i> Hapus
                                                        </button>
                                                    </form>
                                                @else
                                                    <div class="dropdown-item text-muted" style="cursor: not-allowed;"
                                                        title="Turunkan dulu dari status Published">
                                                        <i class="ri-delete-bin-6-line me-1"></i> Hapus
                                                    </div>
                                                @endif
                                            @endif
                                        </div>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" class="text-center text-muted py-4">
                                    <i class="ri-inbox-line ri-3x mb-3 d-block"></i>
                                    Belum ada form kuesioner BPMI yang ditambahkan
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    @if(auth()->user()->hasAnyRole(['BPMI', 'bpmi']))
        <!-- Modal Tambah Kuisioner -->
        <div class="modal fade" id="tambahKuisionerModal" tabindex="-1" aria-hidden="true">
            <div class="modal-dialog modal-dialog-centered">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title">Buat Kuesioner Baru</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <form action="{{ route('dosen.kuisioner.store') }}" method="POST">
                        @csrf
                        <div class="modal-body">
                            <div class="row g-3">
                                <div class="col-12">
                                    <label class="form-label required">Judul Form Kuesioner</label>
                                    <input type="text" name="judul" class="form-control"
                                        placeholder="Evaluasi Dosen Ganjil 2026..." required>
                                </div>
                                <div class="col-12">
                                    <label class="form-label required">Semester Target Pelaksanaan</label>
                                    <select name="id_semester" class="form-select select2-modal" required>
                                        <option value="">Pilih Semester</option>
                                        @foreach($semesters as $semester)
                                            <option value="{{ $semester->id_semester }}" {{ $semester->a_periode_aktif == 1 ? 'selected' : '' }}>{{ $semester->nama_semester }}
                                                {{ $semester->a_periode_aktif == 1 ? '(Aktif)' : '' }}
                                            </option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label required">Target Gatekeeping Ujian</label>
                                    <select name="target_ujian" class="form-select" required>
                                        <option value="">Pilih Tahap Ujian</option>
                                        <option value="UTS">Hanya UTS</option>
                                        <option value="UAS">Hanya UAS</option>
                                    </select>
                                    <div class="form-text mt-1 text-muted">Mahasiswa akan dihadang saat Cetak Kartu jika belum
                                        mengisi.</div>
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label required">Kategori Pertanyaan</label>
                                    <select name="tipe" class="form-select" required>
                                        <option value="">Pilih Kategori</option>
                                        <option value="pelayanan">Pelayanan Akademik</option>
                                        <option value="dosen">Kinerja Dosen</option>
                                    </select>
                                    <div class="form-text mt-1 text-muted">Tipe "Kinerja Dosen" akan diulang sebanyak variasi
                                        kelas yang diambil mahasiswa.</div>
                                </div>
                                <div class="col-12">
                                    <label class="form-label">Deskripsi Opsional (Tampil di Mahasiswa)</label>
                                    <textarea name="deskripsi" class="form-control" rows="3"
                                        placeholder="Pesan pengantar untuk mahasiswa..."></textarea>
                                </div>
                            </div>
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Batal</button>
                            <button type="submit" class="btn btn-primary">Simpan Draft</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    @endif

    @if(auth()->user()->hasAnyRole(['BPMI', 'bpmi']))
        <!-- Modal Edit Kuisioner -->
        <div class="modal fade" id="editKuisionerModal" tabindex="-1" aria-hidden="true">
            <div class="modal-dialog modal-dialog-centered">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title">Ubah Pengaturan Kuesioner</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <form id="formEditKuisioner" method="POST">
                        @csrf
                        @method('PUT')
                        <div class="modal-body">
                            <div class="row g-3">
                                <div class="col-12">
                                    <label class="form-label required">Status Publikasi Kuesioner</label>
                                    <select name="status" id="edit_status" class="form-select" required>
                                        <option value="draft">Draft (Disembunyikan, Bisa Edit Soal)</option>
                                        <option value="published">Published (Formulir Active, Tidak bisa dihapus)</option>
                                        <option value="closed">Closed (Tutup, Selesai digunakan)</option>
                                    </select>
                                    <div class="alert alert-warning mt-2 mb-0 py-2 d-none" id="alertPublishedWarning">
                                        <i class="ri-alert-line me-1"></i> Mengubah status ke <strong>Published</strong> akan
                                        mengaktifkan kuesioner ini ke Dashboard Ujian Mahasiswa. Parameter target ujian dan
                                        semester tidak dapat diubah lagi.
                                    </div>
                                </div>
                                <div class="col-12">
                                    <label class="form-label required">Judul Form Kuesioner</label>
                                    <input type="text" name="judul" id="edit_judul" class="form-control" required>
                                </div>
                                <div class="col-12">
                                    <label class="form-label">Deskripsi Opsional </label>
                                    <textarea name="deskripsi" id="edit_deskripsi" class="form-control" rows="3"></textarea>
                                </div>
                            </div>
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Batal</button>
                            <button type="submit" class="btn btn-primary">Simpan Perubahan</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    @endif

@endsection

@push('scripts')
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            // Initialize Select2 in Add Modal
            $('.select2-modal').select2({
                dropdownParent: $('#tambahKuisionerModal')
            });

            // Initialize Select2 in Filter
            $('.select2-filter').select2();

            // Initialize DataTables
            var table = $('.datatables-kuisioner').DataTable({
                language: {
                    url: '//cdn.datatables.net/plug-ins/1.13.4/i18n/id.json',
                },
                responsive: true,
                columnDefs: [
                    { width: '5%', targets: 0 },
                    { width: '30%', targets: 1 },
                    { width: '15%', targets: 6, orderable: false, searchable: false }
                ],
                dom: '<"row mx-2"<"col-md-2"<"me-3"l>><"col-md-10"<"dt-action-buttons text-xl-end text-lg-start text-md-end text-start d-flex align-items-center justify-content-end flex-md-row flex-column mb-3 mb-md-0"f>>>t<"row mx-2"<"col-sm-12 col-md-6"i><"col-sm-12 col-md-6"p>>',
            });

            // Setup Edit Modal Data
            $('.edit-kuisioner-btn').on('click', function () {
                var id = $(this).data('id');
                var judul = $(this).data('judul');
                var deskripsi = $(this).data('deskripsi');
                var status = $(this).data('status');

                $('#edit_judul').val(judul);
                $('#edit_deskripsi').val(deskripsi);
                $('#edit_status').val(status);

                // Re-trigger alert visibility check
                $('#edit_status').trigger('change');

                // Dynamically set form action URL
                var url = "{{ route('dosen.kuisioner.update', ':id') }}";
                url = url.replace(':id', id);
                $('#formEditKuisioner').attr('action', url);
            });

            // Tampilkan warning di Edit Modal jika milih published
            $('#edit_status').on('change', function () {
                if ($(this).val() === 'published') {
                    $('#alertPublishedWarning').removeClass('d-none');
                } else {
                    $('#alertPublishedWarning').addClass('d-none');
                }
            });

            // Handle Delete Confirmation (SweetAlert UI)
            $('.delete-btn').on('click', function (e) {
                e.preventDefault();
                var form = $(this).closest('form');

                Swal.fire({
                    title: 'Hapus Kuesioner ini?',
                    text: "Semua form dan jika ada jawaban mahasiswa yang tersisa juga akan ikut terhapus permanen!",
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonColor: '#d33',
                    cancelButtonColor: '#8592a3',
                    confirmButtonText: 'Ya, Hapus!',
                    cancelButtonText: 'Batal',
                    customClass: {
                        confirmButton: 'btn btn-danger me-1',
                        cancelButton: 'btn btn-outline-secondary'
                    },
                    buttonsStyling: false
                }).then((result) => {
                    if (result.isConfirmed) {
                        form.submit();
                    }
                });
            });
        });
    </script>
@endpush
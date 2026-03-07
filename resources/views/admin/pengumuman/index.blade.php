@extends('layouts.app')

@section('title', 'Manajemen Pengumuman')

@section('content')
    <div class="d-flex align-items-center justify-content-between mb-4">
        <h4 class="fw-bold py-3 mb-0"><span class="text-muted fw-light">Admin /</span> Pengumuman</h4>
        <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#modalTambah">
            <i class="ri-add-line me-1"></i> Tambah Pengumuman
        </button>
    </div>

    <div class="card">
        <div class="card-datatable table-responsive">
            <table class="table table-hover table-bordered" id="tablePengumuman">
                <thead>
                    <tr>
                        <th style="width: 40px;">No</th>
                        <th>Judul</th>
                        <th>Kategori</th>
                        <th>Periode Tayang</th>
                        <th>Status</th>
                        <th style="width: 100px;">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($pengumumans as $index => $p)
                        <tr>
                            <td>{{ $pengumumans->firstItem() + $index }}</td>
                            <td>
                                <div class="fw-bold">{{ $p->judul }}</div>
                                <small class="text-muted">{{ Str::limit($p->konten, 60) }}</small>
                            </td>
                            <td><span class="badge bg-label-primary text-capitalize">{{ $p->kategori }}</span></td>
                            <td>
                                <small>{{ $p->tgl_mulai->format('d/m/Y') }} — {{ $p->tgl_selesai->format('d/m/Y') }}</small>
                            </td>
                            <td>
                                @if ($p->is_active && $p->tgl_mulai <= now() && $p->tgl_selesai >= now())
                                    <span class="badge bg-success">Tayang</span>
                                @elseif($p->is_active && $p->tgl_mulai > now())
                                    <span class="badge bg-warning">Terjadwal</span>
                                @else
                                    <span class="badge bg-secondary">Nonaktif</span>
                                @endif
                            </td>
                            <td>
                                <button class="btn btn-sm btn-icon btn-warning" title="Edit"
                                    data-bs-toggle="modal" data-bs-target="#modalEdit{{ $p->id }}">
                                    <i class="ri-pencil-line"></i>
                                </button>
                                <form action="{{ route('admin.pengumuman.destroy', $p->id) }}" method="POST"
                                    class="d-inline" onsubmit="return confirm('Hapus pengumuman ini?')">
                                    @csrf @method('DELETE')
                                    <button class="btn btn-sm btn-icon btn-danger" title="Hapus">
                                        <i class="ri-delete-bin-line"></i>
                                    </button>
                                </form>
                            </td>
                        </tr>

                        {{-- Modal Edit --}}
                        <div class="modal fade" id="modalEdit{{ $p->id }}" tabindex="-1">
                            <div class="modal-dialog">
                                <form action="{{ route('admin.pengumuman.update', $p->id) }}" method="POST">
                                    @csrf @method('PUT')
                                    <div class="modal-content">
                                        <div class="modal-header">
                                            <h5 class="modal-title">Edit Pengumuman</h5>
                                            <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                        </div>
                                        <div class="modal-body">
                                            <div class="mb-3">
                                                <label class="form-label">Judul <span class="text-danger">*</span></label>
                                                <input type="text" name="judul" class="form-control"
                                                    value="{{ $p->judul }}" required>
                                            </div>
                                            <div class="mb-3">
                                                <label class="form-label">Konten <span class="text-danger">*</span></label>
                                                <textarea name="konten" class="form-control" rows="4" required>{{ $p->konten }}</textarea>
                                            </div>
                                            <div class="mb-3">
                                                <label class="form-label">Kategori</label>
                                                <select name="kategori" class="form-select">
                                                    @foreach (['krs', 'kuisioner', 'ujian', 'jadwal', 'umum'] as $kat)
                                                        <option value="{{ $kat }}"
                                                            {{ $p->kategori === $kat ? 'selected' : '' }}>
                                                            {{ ucfirst($kat) }}</option>
                                                    @endforeach
                                                </select>
                                            </div>
                                            <div class="row">
                                                <div class="col-6 mb-3">
                                                    <label class="form-label">Tanggal Mulai</label>
                                                    <input type="date" name="tgl_mulai" class="form-control"
                                                        value="{{ $p->tgl_mulai->format('Y-m-d') }}" required>
                                                </div>
                                                <div class="col-6 mb-3">
                                                    <label class="form-label">Tanggal Selesai</label>
                                                    <input type="date" name="tgl_selesai" class="form-control"
                                                        value="{{ $p->tgl_selesai->format('Y-m-d') }}" required>
                                                </div>
                                            </div>
                                            <div class="form-check form-switch">
                                                <input class="form-check-input" type="checkbox" name="is_active"
                                                    value="1" {{ $p->is_active ? 'checked' : '' }}>
                                                <label class="form-check-label">Aktif</label>
                                            </div>
                                        </div>
                                        <div class="modal-footer">
                                            <button type="button" class="btn btn-outline-secondary"
                                                data-bs-dismiss="modal">Batal</button>
                                            <button type="submit" class="btn btn-primary">Simpan Perubahan</button>
                                        </div>
                                    </div>
                                </form>
                            </div>
                        </div>
                    @endforeach
                </tbody>
            </table>
        </div>
        @if ($pengumumans->hasPages())
            <div class="card-footer border-top">{{ $pengumumans->links() }}</div>
        @endif
    </div>

    {{-- Modal Tambah --}}
    <div class="modal fade" id="modalTambah" tabindex="-1">
        <div class="modal-dialog">
            <form action="{{ route('admin.pengumuman.store') }}" method="POST">
                @csrf
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title">Tambah Pengumuman</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body">
                        <div class="mb-3">
                            <label class="form-label">Judul <span class="text-danger">*</span></label>
                            <input type="text" name="judul" class="form-control" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Konten <span class="text-danger">*</span></label>
                            <textarea name="konten" class="form-control" rows="4" required></textarea>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Kategori</label>
                            <select name="kategori" class="form-select">
                                <option value="umum">Umum</option>
                                <option value="krs">KRS</option>
                                <option value="kuisioner">Kuisioner</option>
                                <option value="ujian">Ujian</option>
                                <option value="jadwal">Jadwal</option>
                            </select>
                        </div>
                        <div class="row">
                            <div class="col-6 mb-3">
                                <label class="form-label">Tanggal Mulai</label>
                                <input type="date" name="tgl_mulai" class="form-control"
                                    value="{{ now()->format('Y-m-d') }}" required>
                            </div>
                            <div class="col-6 mb-3">
                                <label class="form-label">Tanggal Selesai</label>
                                <input type="date" name="tgl_selesai" class="form-control"
                                    value="{{ now()->addWeek()->format('Y-m-d') }}" required>
                            </div>
                        </div>
                        <div class="form-check form-switch">
                            <input class="form-check-input" type="checkbox" name="is_active" value="1" checked>
                            <label class="form-check-label">Aktif</label>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Batal</button>
                        <button type="submit" class="btn btn-primary">Simpan</button>
                    </div>
                </div>
            </form>
        </div>
    </div>
@endsection

@push('styles')
    <link rel="stylesheet" href="{{ asset('assets/vendor/libs/datatables-bs5/datatables.bootstrap5.css') }}" />
@endpush

@push('scripts')
    <script src="{{ asset('assets/vendor/libs/datatables-bs5/datatables-bootstrap5.js') }}"></script>
    <script>
        $(function () {
            if ($.fn.DataTable) {
                $('#tablePengumuman').DataTable({
                    paging: false,
                    info: false,
                    language: {
                        url: "//cdn.datatables.net/plug-ins/1.10.24/i18n/Indonesian.json"
                    }
                });
            }
        });
    </script>
@endpush

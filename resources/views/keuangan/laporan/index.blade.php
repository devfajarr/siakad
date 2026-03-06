@extends('layouts.app')

@section('title', 'Laporan Keuangan')

@section('vendor-style')
    <link rel="stylesheet" href="{{ asset('assets/vendor/libs/select2/select2.css') }}" />
@endsection

@section('content')
    <div class="row">
        <div class="col-12">
            <div class="card shadow-sm">
                <div class="card-header border-bottom">
                    <h5 class="card-title mb-0">Export Laporan Pembayaran Lunas</h5>
                    <small class="text-muted">Unduh rekapitulasi data pembayaran yang telah disetujui dalam format Excel
                        (.xlsx)</small>
                </div>
                <div class="card-body mt-4">
                    <form action="{{ route('admin.laporan-keuangan.export') }}" method="POST">
                        @csrf

                        <div class="row g-4">
                            <div class="col-md-6">
                                <label class="form-label" for="start_date">Dari Tanggal Bayar <span
                                        class="text-danger">*</span></label>
                                <input type="date" id="start_date" name="start_date"
                                    class="form-control @error('start_date') is-invalid @enderror"
                                    value="{{ date('Y-m-d', strtotime('-30 days')) }}" required>
                                @error('start_date')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="col-md-6">
                                <label class="form-label" for="end_date">Sampai Tanggal Bayar <span
                                        class="text-danger">*</span></label>
                                <input type="date" id="end_date" name="end_date"
                                    class="form-control @error('end_date') is-invalid @enderror" value="{{ date('Y-m-d') }}"
                                    required>
                                @error('end_date')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="col-md-6">
                                <label class="form-label" for="id_prodi">Program Studi (Boleh Kosong)</label>
                                <select id="id_prodi" name="id_prodi" class="select2 form-select" data-allow-clear="true">
                                    <option value=""></option>
                                    @foreach($prodis as $prodi)
                                        <option value="{{ $prodi->id_prodi }}">{{ $prodi->nama_program_studi }}
                                            ({{ $prodi->kode_program_studi }})</option>
                                    @endforeach
                                </select>
                            </div>

                            <div class="col-md-6">
                                <label class="form-label" for="komponen_biaya_id">Jenis Komponen Biaya (Boleh
                                    Kosong)</label>
                                <select id="komponen_biaya_id" name="komponen_biaya_id" class="select2 form-select"
                                    data-allow-clear="true">
                                    <option value=""></option>
                                    @foreach($komponens as $komp)
                                        <option value="{{ $komp->id }}">{{ $komp->nama_komponen }} ({{ $komp->kode_komponen }})
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                        </div>

                        <div class="mt-4 pt-2">
                            <button type="submit" class="btn btn-success">
                                <i class="ri-file-excel-2-line me-1"></i> Export Excel
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
@endsection

@section('vendor-script')
    <script src="{{ asset('assets/vendor/libs/select2/select2.js') }}"></script>
@endsection

@push('scripts')
    <script>
        $(document).ready(function () {
            $('.select2').select2({
                placeholder: "Pilih salah satu (Opsional)",
                allowClear: true
            });
        });
    </script>
@endpush
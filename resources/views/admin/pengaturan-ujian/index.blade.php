@extends('layouts.app')

@section('title', 'Pengaturan Waktu Cetak Kartu Ujian')

@section('content')
    <div class="row">
        <div class="col-12">
            <div class="page-title-box d-sm-flex align-items-center justify-content-between">
                <h4 class="mb-sm-0">Pengaturan Waktu Cetak Kartu Ujian</h4>
                <div class="page-title-right">
                    <ol class="breadcrumb m-0">
                        <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Dashboard</a></li>
                        <li class="breadcrumb-item active">Pengaturan Ujian</li>
                    </ol>
                </div>
            </div>
        </div>
    </div>

    <div class="row">
        <div class="col-lg-12">
            <div class="card">
                <div class="card-header border-0 pb-0">
                    <div class="row g-4 align-items-center">
                        <div class="col-sm">
                            <div>
                                <h5 class="card-title mb-0">Konfigurasi Periode Cetak Kartu Ujian</h5>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="card-body">
                    <form action="{{ route('admin.pengaturan-ujian.index') }}" method="GET" class="mb-4">
                        <div class="row">
                            <div class="col-md-4">
                                <label class="form-label">Pilih Semester Akademik</label>
                                <select name="semester_id" class="form-select select2" onchange="this.form.submit()">
                                    @foreach($semesters as $sem)
                                        <option value="{{ $sem->id_semester }}" {{ (string) $semesterId === (string) $sem->id_semester ? 'selected' : '' }}>
                                            {{ $sem->nama_semester }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                    </form>

                    <hr>

                    <div class="row mt-4">
                        <!-- Form Pengaturan UTS -->
                        <div class="col-md-6">
                            <div class="card border border-primary border-opacity-25">
                                <div class="card-header bg-primary bg-opacity-10 py-3 mb-2">
                                    <h6 class="card-title mb-0 text-white">Periode Cetak UTS</h6>
                                </div>
                                <div class="card-body">
                                    <form action="{{ route('admin.pengaturan-ujian.store') }}" method="POST">
                                        @csrf
                                        <input type="hidden" name="semester_id" value="{{ $semesterId }}">
                                        <input type="hidden" name="tipe_ujian" value="UTS">

                                        <div class="mb-3">
                                            <label class="form-label">Tanggal Mulai Cetak Kartu</label>
                                            <input type="datetime-local" class="form-control" name="tgl_mulai_cetak"
                                                value="{{ $pengaturanUTS->tgl_mulai_cetak ? $pengaturanUTS->tgl_mulai_cetak->format('Y-m-d\TH:i') : '' }}">
                                            <div class="form-text">Biarkan kosong jika belum ingin dibuka.</div>
                                        </div>

                                        <div class="mb-3">
                                            <label class="form-label">Tanggal Akhir Berakhirnya Cetak</label>
                                            <input type="datetime-local" class="form-control" name="tgl_akhir_cetak"
                                                value="{{ $pengaturanUTS->tgl_akhir_cetak ? $pengaturanUTS->tgl_akhir_cetak->format('Y-m-d\TH:i') : '' }}">
                                            <div class="form-text">Biarkan kosong jika batas waktu terbuka tanpa batasan
                                                akhir.</div>
                                        </div>

                                        <div class="text-end">
                                            <button type="submit" class="btn btn-primary">Simpan Pengaturan UTS</button>
                                        </div>
                                    </form>
                                </div>
                            </div>
                        </div>

                        <!-- Form Pengaturan UAS -->
                        <div class="col-md-6">
                            <div class="card border border-success border-opacity-25">
                                <div class="card-header bg-success bg-opacity-10 py-3 mb-2">
                                    <h6 class="card-title mb-0 text-success">Periode Cetak UAS</h6>
                                </div>
                                <div class="card-body">
                                    <form action="{{ route('admin.pengaturan-ujian.store') }}" method="POST">
                                        @csrf
                                        <input type="hidden" name="semester_id" value="{{ $semesterId }}">
                                        <input type="hidden" name="tipe_ujian" value="UAS">

                                        <div class="mb-3">
                                            <label class="form-label">Tanggal Mulai Cetak Kartu</label>
                                            <input type="datetime-local" class="form-control" name="tgl_mulai_cetak"
                                                value="{{ $pengaturanUAS->tgl_mulai_cetak ? $pengaturanUAS->tgl_mulai_cetak->format('Y-m-d\TH:i') : '' }}">
                                            <div class="form-text">Biarkan kosong jika belum ingin dibuka.</div>
                                        </div>

                                        <div class="mb-3">
                                            <label class="form-label">Tanggal Akhir Berakhirnya Cetak</label>
                                            <input type="datetime-local" class="form-control" name="tgl_akhir_cetak"
                                                value="{{ $pengaturanUAS->tgl_akhir_cetak ? $pengaturanUAS->tgl_akhir_cetak->format('Y-m-d\TH:i') : '' }}">
                                            <div class="form-text">Biarkan kosong jika batas waktu terbuka tanpa batasan
                                                akhir.</div>
                                        </div>

                                        <div class="text-end">
                                            <button type="submit" class="btn btn-success">Simpan Pengaturan UAS</button>
                                        </div>
                                    </form>
                                </div>
                            </div>
                        </div>
                    </div>

                </div>
            </div>
        </div>
    </div>
@endsection

@push('scripts')
    <script>
        $(document).ready(function () {
            $('.select2').select2({
                theme: 'bootstrap-5'
            });
        });
    </script>
@endpush
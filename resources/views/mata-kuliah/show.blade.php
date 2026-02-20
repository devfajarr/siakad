@extends('layouts.app')

@section('title', 'Detail Mata Kuliah')

@section('content')
    <h4 class="fw-bold py-3 mb-4">
        <span class="text-muted fw-light">Perkuliahan / Mata Kuliah /</span> Detail
    </h4>

    <div class="card shadow-sm">
        <div class="card-header border-bottom">
            <h5 class="card-title mb-0">Detail Mata Kuliah</h5>
        </div>

        <div class="card-body mt-4">
            <div class="row g-4">
                {{-- Kode MK & Nama MK --}}
                <div class="col-md-6">
                    <label class="form-label" for="kode_mk">Kode Mata Kuliah</label>
                    <input type="text" class="form-control" value="{{ $mataKuliah->kode_mk }}" disabled>
                </div>

                <div class="col-md-6">
                    <label class="form-label" for="nama_mk">Nama Mata Kuliah</label>
                    <input type="text" class="form-control" value="{{ $mataKuliah->nama_mk }}" disabled>
                </div>

                {{-- Prodi & Jenis MK --}}
                <div class="col-md-6">
                    <label class="form-label" for="id_prodi">Program Studi Pengampu</label>
                    <input type="text" class="form-control" value="{{ $mataKuliah->prodi->nama_program_studi ?? '-' }}"
                        disabled>
                </div>

                <div class="col-md-6">
                    <label class="form-label" for="jenis_mk">Jenis Mata Kuliah</label>
                    <input type="text" class="form-control" value="{{ $mataKuliah->jenis_mk }}" disabled>
                </div>

                {{-- Kelompok MK --}}
                <div class="col-md-12">
                    <label class="form-label" for="kelompok_mk">Kelompok Mata Kuliah</label>
                    <input type="text" class="form-control" value="{{ $mataKuliah->kelompok_mk }}" disabled>
                </div>

                <hr class="my-0 mt-5">
                <h6 class="mb-0 fw-bold">Bobot & SKS</h6>

                <div class="col-md-4">
                    <label class="form-label" for="sks">SKS Total</label>
                    <div class="input-group">
                        <input type="number" class="form-control" value="{{ $mataKuliah->sks }}" disabled>
                        <span class="input-group-text">SKS</span>
                    </div>
                </div>

                <div class="col-md-4">
                    <label class="form-label" for="sks_tatap_muka">SKS Tatap Muka</label>
                    <div class="input-group">
                        <input type="number" class="form-control" value="{{ $mataKuliah->sks_tatap_muka }}" disabled>
                        <span class="input-group-text">SKS</span>
                    </div>
                </div>

                <div class="col-md-4">
                    <label class="form-label" for="sks_praktek">SKS Praktikum</label>
                    <div class="input-group">
                        <input type="number" class="form-control" value="{{ $mataKuliah->sks_praktek }}" disabled>
                        <span class="input-group-text">SKS</span>
                    </div>
                </div>

                <div class="col-md-4">
                    <label class="form-label" for="sks_praktek_lapangan">SKS Praktek Lapangan</label>
                    <div class="input-group">
                        <input type="number" class="form-control" value="{{ $mataKuliah->sks_praktek_lapangan }}" disabled>
                        <span class="input-group-text">SKS</span>
                    </div>
                </div>

                <div class="col-md-4">
                    <label class="form-label" for="sks_simulasi">SKS Simulasi</label>
                    <div class="input-group">
                        <input type="number" class="form-control" value="{{ $mataKuliah->sks_simulasi }}" disabled>
                        <span class="input-group-text">SKS</span>
                    </div>
                </div>

                <hr class="my-0 mt-5">
                <h6 class="mb-0 fw-bold">Detail Tambahan</h6>

                {{-- Metode & Tanggal --}}
                <div class="col-md-12">
                    <label class="form-label" for="metode_kuliah">Metode Pembelajaran</label>
                    <input type="text" class="form-control" value="{{ $mataKuliah->metode_kuliah }}" disabled>
                </div>

                <div class="col-md-6">
                    <label class="form-label" for="tanggal_mulai_efektif">Tanggal Mulai Efektif</label>
                    <input type="text" class="form-control"
                        value="{{ $mataKuliah->tanggal_mulai_efektif ? $mataKuliah->tanggal_mulai_efektif->format('d-m-Y') : '-' }}"
                        disabled>
                </div>

                <div class="col-md-6">
                    <label class="form-label" for="tanggal_akhir_efektif">Tanggal Akhir Efektif</label>
                    <input type="text" class="form-control"
                        value="{{ $mataKuliah->tanggal_akhir_efektif ? $mataKuliah->tanggal_akhir_efektif->format('d-m-Y') : '-' }}"
                        disabled>
                </div>
            </div>
        </div>

        <div class="card-footer border-top d-flex justify-content-end gap-2 py-3">
            <a href="{{ route('admin.mata-kuliah.index') }}" class="btn btn-outline-secondary">
                <i class="ri-arrow-left-line me-1"></i> Kembali
            </a>
            @if($mataKuliah->sumber_data == 'lokal')
                <a href="{{ route('admin.mata-kuliah.edit', $mataKuliah->id) }}" class="btn btn-warning">
                    <i class="ri-pencil-line me-1"></i> Edit
                </a>
            @endif
        </div>
    </div>
@endsection
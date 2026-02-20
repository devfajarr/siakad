@extends('layouts.app')

@section('title', 'Edit Mahasiswa')

@section('content')
    <div class="row">
        <div class="col-xl">
            <div class="card mb-4">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h5 class="mb-0">Edit Data Mahasiswa</h5>
                </div>
                <div class="card-body">
                    <form action="{{ route('admin.mahasiswa.update', 1) }}" method="POST">
                        @csrf
                        @method('PUT')
                        <div class="mb-3">
                            <label class="form-label" for="nim">NIM</label>
                            <input type="text" class="form-control" id="nim" name="nim" value="2023001" readonly />
                        </div>
                        <div class="mb-3">
                            <label class="form-label" for="nama">Nama Lengkap</label>
                            <input type="text" class="form-control" id="nama" name="nama" value="Andi Saputra" required />
                        </div>
                        <div class="mb-3">
                            <label class="form-label" for="email">Email</label>
                            <input type="email" class="form-control" id="email" name="email" value="andi@example.com" />
                        </div>
                        <div class="mb-3">
                            <label class="form-label" for="prodi">Program Studi</label>
                            <select class="form-select" id="prodi" name="prodi" required>
                                <option value="TI" selected>Teknik Informatika</option>
                                <option value="MI">Manajemen Informatika</option>
                                <option value="AK">Akuntansi</option>
                            </select>
                        </div>
                        <div class="mb-3">
                            <label class="form-label" for="angkatan">Angkatan</label>
                            <input type="number" class="form-control" id="angkatan" name="angkatan" value="2023" required />
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Status</label>
                            <div class="form-check">
                                <input name="status" class="form-check-input" type="radio" value="1" id="statusAktif"
                                    checked />
                                <label class="form-check-label" for="statusAktif"> Aktif </label>
                            </div>
                            <div class="form-check">
                                <input name="status" class="form-check-input" type="radio" value="0" id="statusNonaktif" />
                                <label class="form-check-label" for="statusNonaktif"> Nonaktif </label>
                            </div>
                        </div>
                        <button type="submit" class="btn btn-primary">Simpan Perubahan</button>
                        <a href="{{ route('admin.mahasiswa.index') }}" class="btn btn-outline-secondary">Kembali</a>
                    </form>
                </div>
            </div>
        </div>
    </div>
@endsection
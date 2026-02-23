<div class="row">

    <!-- Kolom Kiri: Informasi Kredensial & Aksi Dasar -->
    <div class="col-md-7 mb-4">

        <!-- Card: Status Akun -->
        <div class="card shadow-none border mb-4">
            <div class="card-header border-bottom">
                <h6 class="card-title mb-0">Status Akun Sistem</h6>
            </div>
            <div class="card-body pt-4">
                <div class="d-flex align-items-center">
                    <span class="fw-medium me-3">Status Saat Ini:</span>
                    @if ($mahasiswa->user_id)
                        <span class="badge bg-label-success rounded-pill px-3 py-2">
                            <i class="ri-checkbox-circle-line me-1"></i> Akun Aktif
                        </span>
                    @else
                        <span class="badge bg-label-danger rounded-pill px-3 py-2">
                            <i class="ri-error-warning-line me-1"></i> Belum Memiliki Akun
                        </span>
                    @endif
                </div>
                <small class="text-muted d-block mt-3">Akun dibutuhkan agar mahasiswa dapat login ke dalam sistem Sistem
                    Informasi Akademik.</small>
            </div>
        </div>

        @if ($mahasiswa->user_id)
            <!-- Card: Detail Kredensial (Kondisional: Tampil jika akun aktif) -->
            <div class="card shadow-none border mb-4">
                <div class="card-header border-bottom">
                    <h6 class="card-title mb-0">Detail Kredensial</h6>
                </div>
                <div class="card-body pt-4">
                    <div class="row mb-3">
                        <label class="col-sm-3 col-form-label fw-medium" for="username">Username</label>
                        <div class="col-sm-9">
                            <input type="text" class="form-control" id="username"
                                value="{{ $mahasiswa->user->username ?? '-' }}" readonly>
                            <div class="form-text">Digunakan sebagai identitas saat login.</div>
                        </div>
                    </div>
                    <div class="row mb-3">
                        <label class="col-sm-3 col-form-label fw-medium" for="email">Email Sistem</label>
                        <div class="col-sm-9">
                            <input type="text" class="form-control" id="email" value="{{ $mahasiswa->user->email ?? '-' }}"
                                readonly>
                        </div>
                    </div>
                </div>
            </div>
        @endif

        <!-- Card: Aksi Keamanan -->
        <div class="card shadow-none border">
            <div class="card-header border-bottom">
                <h6 class="card-title mb-0">Aksi Keamanan</h6>
            </div>
            <div class="card-body pt-4">
                @if (is_null($mahasiswa->user_id))
                    <!-- Kumpulan Tombol (Kondisional: Jika belum ada akun) -->
                    <div class="alert alert-primary d-flex align-items-center" role="alert">
                        <span class="alert-icon text-primary me-2">
                            <i class="ri-information-line ri-20px"></i>
                        </span>
                        Mahasiswa ini belum memiliki entitas akun pengguna. Klik tombol di bawah untuk membuat secara
                        otomatis berserta kata sandinya.
                    </div>
                    <form action="{{ route('admin.mahasiswa.generate-user', $mahasiswa->id) }}" method="POST">
                        @csrf
                        <button type="submit" class="btn btn-primary d-block w-100">
                            <i class="ri-user-add-line me-2"></i> Generate Akun Login
                        </button>
                    </form>
                @else
                    <!-- Kumpulan Tombol (Kondisional: Jika sudah ada akun) -->
                    <div class="d-flex flex-column gap-3">
                        <div>
                            <form action="#" method="POST"
                                onsubmit="return confirm('Yakin ingin mereset kata sandi ke bawaan sistem (NIM)?');">
                                @csrf
                                <!-- @method('PUT') / Uncomment & Update route with real endpoint -->
                                <button type="submit"
                                    class="btn btn-warning w-100 d-flex justify-content-center align-items-center">
                                    <i class="ri-refresh-line me-2"></i> Reset Password ke Default
                                </button>
                            </form>
                            <small class="text-muted d-block mt-1">Mengembalikan kata sandi sesuai dengan Default bawaan
                                sistem (NIM/NISN).</small>
                        </div>

                        <hr class="my-1">

                        <div>
                            <form action="#" method="POST"
                                onsubmit="return confirm('Peringatan: Seluruh sesi login mahasiswa ini akan terputus. Lanjutkan mencabut akses?');">
                                @csrf
                                @method('DELETE')
                                <!-- Update route with real endpoint to detach user_id or delete user -->
                                <button type="submit"
                                    class="btn btn-outline-danger w-100 d-flex justify-content-center align-items-center">
                                    <i class="ri-user-forbid-line me-2"></i> Cabut Akses / Nonaktifkan Akun
                                </button>
                            </form>
                            <small class="text-muted d-block mt-1">Mahasiswa tidak akan dapat mengakses sistem apa pun
                                lagi.</small>
                        </div>
                    </div>
                @endif
            </div>
        </div>

    </div>

    <!-- Kolom Kanan: Manajemen Role Tambahan -->
    <div class="col-md-5">

        @if ($mahasiswa->user_id)
            <!-- Card: Hak Akses Ekstra (Kondisional: Tampil jika akun aktif) -->
            <div class="card shadow-none border">
                <div class="card-header border-bottom">
                    <h6 class="card-title mb-0">Hak Akses Ekstra (Roles)</h6>
                </div>
                <div class="card-body pt-4">
                    <p class="text-muted mb-4">
                        Berikan hak akses tambahan jika mahasiswa ini menjabat sebagai Asisten Dosen, Admin BEM, atau
                        keperluan spesifik lainnya.
                    </p>

                    <form action="#" method="POST">
                        @csrf
                        <!-- Update endpoint for assigning additional roles on user level -->
                        <div class="mb-4">
                            <!-- Contoh Looping Spatie Roles: $roles harus dipassing dari Controller -->
                            @isset($roles)
                                @forelse ($roles as $role)
                                    <!-- Sembunyikan Role default 'Mahasiswa' agar tidak diutak-atik manual (opsional) -->
                                    @if($role->name !== 'Mahasiswa')
                                        <div class="form-check form-switch mb-2">
                                            <input class="form-check-input" type="checkbox" name="roles[]" value="{{ $role->name }}"
                                                id="role_{{ $role->id }}" {{ $mahasiswa->user->hasRole($role->name) ? 'checked' : '' }}>
                                            <label class="form-check-label" for="role_{{ $role->id }}">
                                                {{ ucfirst($role->name) }}
                                            </label>
                                        </div>
                                    @endif
                                @empty
                                    <div class="alert alert-secondary mb-0">Tidak ada role ekstra tersedia.</div>
                                @endforelse
                            @else
                                <div class="alert alert-warning mb-0"><i class="ri-alert-line me-1"></i> Data Roles (Spatie)
                                    belum dipasang oleh Controller</div>
                            @endisset
                        </div>

                        <button type="submit" class="btn btn-primary w-100">
                            <i class="ri-save-line me-2"></i> Perbarui Hak Akses
                        </button>
                    </form>
                </div>
            </div>
        @else
            <!-- Placeholder saat tidak ada akun -->
            <div
                class="card shadow-none border border-dashed text-center h-100 p-5 d-flex flex-column justify-content-center align-items-center">
                <i class="ri-shield-keyhole-line text-muted mb-3" style="font-size: 3rem;"></i>
                <h6 class="text-muted mb-0">Manajemen Akses Ekstra belum Tersedia</h6>
                <small class="text-muted mt-2">Anda wajib men-generate akun sistem terlebih dahulu.</small>
            </div>
        @endif

    </div>

</div>
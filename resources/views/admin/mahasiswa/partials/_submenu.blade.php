<div class="card shadow-sm mb-5">
    <div class="card-header">
        <h5 class="card-title mb-0">Sub Menu Informasi Detail Mahasiswa</h5>
    </div>
    <div class="card-body">
        <div class="nav-align-top">
            <ul class="nav nav-pills mb-3" role="tablist">
                <li class="nav-item">
                    <a href="{{ route('admin.mahasiswa.detail', $mahasiswa->id) }}"
                        class="nav-link {{ request()->routeIs('admin.mahasiswa.detail') ? 'active' : '' }}" role="tab">
                        DETAIL MAHASISWA
                    </a>
                </li>
                <li class="nav-item">
                    <a href="{{ route('admin.mahasiswa.histori', $mahasiswa->id) }}"
                        class="nav-link {{ request()->routeIs('admin.mahasiswa.histori') ? 'active' : '' }}" role="tab">
                        HISTORI PENDIDIKAN
                    </a>
                </li>
                <li class="nav-item">
                    <a href="{{ route('admin.mahasiswa.krs', $mahasiswa->id) }}"
                        class="nav-link {{ request()->routeIs('admin.mahasiswa.krs') ? 'active' : '' }}" role="tab">
                        KRS MAHASISWA
                    </a>
                </li>
                <li class="nav-item">
                    <a href="{{ route('admin.mahasiswa.akun', $mahasiswa->id) }}"
                        class="nav-link {{ request()->routeIs('admin.mahasiswa.akun') ? 'active' : '' }}" role="tab">
                        AKUN & KEAMANAN
                    </a>
                </li>
            </ul>
        </div>
    </div>
</div>
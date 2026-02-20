<div class="row mb-4">
    <div class="col-12">
        <div class="card h-100">
            <div class="card-header">
                <h5 class="card-title mb-0">Sub Menu Informasi Detail Mahasiswa</h5>
            </div>
            <div class="card-body">
                <div class="nav-align-top">
                    <ul class="nav nav-pills mb-3" role="tablist">
                        <li class="nav-item">
                            <a href="{{ route('admin.mahasiswa.detail', $mahasiswa->id) }}"
                                class="nav-link {{ request()->routeIs('admin.mahasiswa.detail') ? 'active' : '' }}"
                                role="tab">
                                DETAIL MAHASISWA
                            </a>
                        </li>
                        <li class="nav-item">
                            <a href="{{ route('admin.mahasiswa.histori', $mahasiswa->id) }}"
                                class="nav-link {{ request()->routeIs('admin.mahasiswa.histori') ? 'active' : '' }}"
                                role="tab">
                                HISTORI PENDIDIKAN
                            </a>
                        </li>
                        <li class="nav-item">
                            <a href="{{ route('admin.mahasiswa.krs', $mahasiswa->id) }}"
                                class="nav-link {{ request()->routeIs('admin.mahasiswa.krs') ? 'active' : '' }}"
                                role="tab">
                                KRS MAHASISWA
                            </a>
                        </li>
                        {{-- Additional tabs as shown in image (optional/future) --}}
                        {{--
                        <li class="nav-item">
                            <button type="button" class="nav-link" role="tab" disabled>HISTORI NILAI</button>
                        </li>
                        <li class="nav-item">
                            <button type="button" class="nav-link" role="tab" disabled>AKTIVITAS PERKULIAHAN</button>
                        </li>
                        --}}
                    </ul>
                </div>
            </div>
        </div>
    </div>
</div>
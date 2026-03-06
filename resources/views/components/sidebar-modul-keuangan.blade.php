<!-- Modul Keuangan -->
<li class="menu-header mt-5 small text-uppercase">
    <span class="menu-header-text">Modul Keuangan</span>
</li>
<li class="menu-item {{ request()->routeIs('admin.keuangan-dashboard') ? 'active' : '' }}">
    <a href="{{ route('admin.keuangan-dashboard') }}" class="menu-link">
        <i class="menu-icon tf-icons ri-dashboard-3-line"></i>
        <div data-i18n="Dashboard Keuangan">Dashboard Keuangan</div>
    </a>
</li>
<li class="menu-item {{ request()->routeIs('admin.keuangan-modul.komponen-biaya.*') ? 'active' : '' }}">
    <a href="{{ route('admin.keuangan-modul.komponen-biaya.index') }}" class="menu-link">
        <i class="menu-icon tf-icons ri-file-list-2-line"></i>
        <div data-i18n="Komponen Biaya">Komponen Biaya</div>
    </a>
</li>
<li class="menu-item {{ request()->routeIs('admin.keuangan-modul.tagihan.*') ? 'active' : '' }}">
    <a href="{{ route('admin.keuangan-modul.tagihan.index') }}" class="menu-link">
        <i class="menu-icon tf-icons ri-bill-line"></i>
        <div data-i18n="Tagihan Mahasiswa">Tagihan Mahasiswa</div>
    </a>
</li>
<li class="menu-item {{ request()->routeIs('admin.keuangan-modul.verifikasi.*') ? 'active' : '' }}">
    <a href="{{ route('admin.keuangan-modul.verifikasi.index') }}" class="menu-link">
        <i class="menu-icon tf-icons ri-checkbox-circle-line"></i>
        <div data-i18n="Verifikasi Pembayaran">Verifikasi Pembayaran</div>
    </a>
</li>
<li class="menu-item {{ request()->routeIs('admin.keuangan-modul.monitoring-perkuliahan.*') ? 'active' : '' }}">
    <a href="{{ route('admin.keuangan-modul.monitoring-perkuliahan.index') }}" class="menu-link">
        <i class="menu-icon tf-icons ri-macbook-line"></i>
        <div data-i18n="Monitoring Perkuliahan">Monitoring Perkuliahan</div>
    </a>
</li>
<li class="menu-item {{ request()->routeIs('admin.laporan-keuangan.*') ? 'active' : '' }}">
    <a href="{{ route('admin.laporan-keuangan.index') }}" class="menu-link">
        <i class="menu-icon tf-icons ri-file-excel-2-line"></i>
        <div data-i18n="Laporan">Laporan</div>
    </a>
</li>
<?php

namespace App\Http\Controllers\Keuangan;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Dosen;

class MonitoringPerkuliahanController extends Controller
{
    /**
     * Tampilkan Halaman Rekapitulasi Monitoring Dosen (Keuangan View)
     */
    public function index(Request $request)
    {
        // 1. Ambil Query Filter Bulan Tahun, default ke bulan ini.
        $bulanTahunFilter = $request->get('bulan_tahun', date('Y-m'));

        // 2. Query Eager Loading yang Efisien (Sesuai Strict Constraints)
        // Kita hanya mengambil Dosen yang memiliki aktivitas mengajar pada periode terpilih
        $dosens = Dosen::with([
            'pengajaranKelas.kelasKuliah.mataKuliah',
            'pengajaranKelas.kelasKuliah.presensiPertemuans' => function ($q) use ($bulanTahunFilter) {
                // Kita ingin pastikan hanya pertemuan di bulan tersebut yang terambil atau dihitung
                if ($bulanTahunFilter) {
                    // Di postgreSQL kita bisa menggunakan whereYear dan whereMonth pada kolom date
                    $explode = explode('-', $bulanTahunFilter);
                    if (count($explode) == 2) {
                        $q->whereYear('tanggal', $explode[0])
                            ->whereMonth('tanggal', $explode[1]);
                    }
                }
            }
        ])
            ->whereHas('pengajaranKelas', function ($qHas) use ($bulanTahunFilter) {
                // Opsional: Jika kita tahu jadwal kelas itu relevan dengan bulan, namun `presensi_pertemuan`
                // adalah sumber "kapan itu terjadi". Jadi kita filter berdasarkan pertemuan yang eksis.
                $qHas->whereHas('kelasKuliah.presensiPertemuans', function ($qPresensi) use ($bulanTahunFilter) {
                    if ($bulanTahunFilter) {
                        $explode = explode('-', $bulanTahunFilter);
                        if (count($explode) == 2) {
                            $qPresensi->whereYear('tanggal', $explode[0])
                                ->whereMonth('tanggal', $explode[1]);
                        }
                    }
                });
            })
            ->get();

        // 3. Kalkulasi agregat mapping agar rapi di Blade
        // Supaya Blade tidak berat dalam looping N+1
        $dosenRekap = $dosens->map(function ($dosen) {
            $totalPertemuan = 0;
            $totalTerverifikasi = 0;
            $totalPending = 0;
            $totalSks = 0;

            // Loop untuk mendapatkan total agregasi
            foreach ($dosen->pengajaranKelas as $pk) {
                if ($pk->kelasKuliah) {
                    // Karena kita mengambil kelas kuliah, kita tambahkan SKS
                    $sksKelas = floatval($pk->kelasKuliah->mataKuliah->sks_mata_kuliah ?? 0);
                    $addedSks = false;

                    foreach ($pk->kelasKuliah->presensiPertemuans as $presensi) {
                        // Pastikan ini absensi khusus milik sang dosen pengajar (jika kelas tersebut team-teaching)
                        if ($presensi->id_dosen === $dosen->id) {
                            $totalPertemuan++;

                            if ($presensi->status_verifikasi === \App\Models\PresensiPertemuan::STATUS_TERVERIFIKASI) {
                                $totalTerverifikasi++;
                            } else {
                                $totalPending++;
                            }

                            if (!$addedSks) {
                                $totalSks += $sksKelas;
                                $addedSks = true; // Hanya tambah sekali per-kelas yang diampu (bisa disesuaikan logic aslinya jika beda)
                            }
                        }
                    }
                }
            }

            // Simulasi Asumsi Tarif SKS = Rp 150,000 per pertemuan / SKS / per regulasi internal (Contoh Standar)
            // Sesuaikan tarif jika memiliki skema berbeda. Saat ini standar rata: (Total Pertemuan Valid x SKS x Tarif SKS)
            // Namun karena instruction bilangnya: (Jumlah Pertemuan x Tarif) -> Asumsi Tarif Flat per Pertemuan = 100,000.
            // Bisa juga dikalikan berdasar SKS mata kuliah tiap pertemuan. 
            // Untuk simplifikasi sementara: Rp 100.000 x Terverifikasi.
            $estimasiHonor = $totalTerverifikasi * 100000;

            return [
                'id_dosen' => $dosen->id,
                'nama_dosen' => $dosen->nama_admin_display, // menggunakan accessor yang kita temukan sebelumnya
                'total_sks' => $totalSks,
                'total_pertemuan' => $totalPertemuan,
                'total_terverifikasi' => $totalTerverifikasi,
                'total_pending' => $totalPending,
                'estimasi_honor' => $estimasiHonor,
                'pengajaran_detail' => $dosen->pengajaranKelas
            ];
        });

        return view('admin.keuangan.monitoring-perkuliahan.index', compact('dosenRekap', 'bulanTahunFilter'));
    }

    /**
     * Endpoint Untuk Men-download Excel / Export CSV
     */
    public function export(Request $request)
    {
        // Akan didelegasikan ke Export class bawaan Maatwebsite / Excel
        $bulanTahun = $request->get('bulan_tahun');
        return \Maatwebsite\Excel\Facades\Excel::download(
            new \App\Exports\RekapHonorerMengajarExport($bulanTahun),
            'Rekapitulasi_Honor_Dosen_' . ($bulanTahun ?? 'All') . '.xlsx'
        );
    }
}

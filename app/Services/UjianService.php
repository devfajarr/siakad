<?php

namespace App\Services;

use App\Models\JadwalUjian;
use App\Models\PesertaUjian;
use App\Models\PesertaKelasKuliah;
use App\Models\PresensiMahasiswa;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class UjianService
{
    /**
     * Generate peserta ujian untuk satu jadwal ujian.
     * Mengambil semua peserta KRS yang sudah ACC, lalu menghitung eligibility berdasarkan kehadiran.
     *
     * @param JadwalUjian $jadwalUjian
     * @return array ['total' => int, 'eligible' => int, 'not_eligible' => int]
     */
    public function generatePesertaUjian(JadwalUjian $jadwalUjian): array
    {
        $kelasKuliah = $jadwalUjian->kelasKuliah;
        $minPersentase = config('academic.min_persentase_ujian', 70);
        $targetBlok = config('academic.target_pertemuan_per_blok', 7);

        // Tentukan range pertemuan berdasarkan tipe ujian
        $tipeUjian = strtoupper($jadwalUjian->tipe_ujian);
        $rangePertemuan = $tipeUjian === 'UTS' ? [1, 7] : [8, 14];

        $result = ['total' => 0, 'eligible' => 0, 'not_eligible' => 0];

        return DB::transaction(function () use ($jadwalUjian, $kelasKuliah, $minPersentase, $targetBlok, $rangePertemuan, &$result) {

            // 1. Ambil peserta KRS yang sudah ACC saja sesuai aturan strict User
            $pesertaKrs = PesertaKelasKuliah::where('id_kelas_kuliah', $kelasKuliah->id_kelas_kuliah)
                ->where('status_krs', 'acc')
                ->whereNotNull('riwayat_pendidikan_id')
                ->get();

            Log::info("UJIAN_GENERATE: Mulai generate peserta ujian", [
                'jadwal_ujian_id' => $jadwalUjian->id,
                'kelas' => $kelasKuliah->nama_kelas_kuliah,
                'tipe_ujian' => $jadwalUjian->tipe_ujian,
                'range_pertemuan' => $rangePertemuan,
                'total_krs_acc' => $pesertaKrs->count(),
            ]);

            foreach ($pesertaKrs as $pkk) {
                // 2. Hitung kehadiran pada blok pertemuan terkait
                $jumlahHadir = PresensiMahasiswa::where('riwayat_pendidikan_id', $pkk->riwayat_pendidikan_id)
                    ->whereHas('pertemuan', function ($q) use ($kelasKuliah, $rangePertemuan) {
                        $q->where('id_kelas_kuliah', $kelasKuliah->id_kelas_kuliah)
                            ->whereBetween('pertemuan_ke', $rangePertemuan);
                    })
                    ->where('status_kehadiran', 'H')
                    ->count();

                $persentase = $targetBlok > 0
                    ? round(($jumlahHadir / $targetBlok) * 100, 2)
                    : 0;

                $isEligible = $persentase >= $minPersentase;

                $keterangan = null;
                if (!$isEligible) {
                    $keterangan = "Kehadiran " . $jadwalUjian->tipe_ujian . " (Pert. {$rangePertemuan[0]}-{$rangePertemuan[1]}): {$persentase}% < {$minPersentase}%";
                }

                // 3. Simpan/Update peserta ujian (updateOrCreate untuk idempotency)
                PesertaUjian::updateOrCreate(
                    [
                        'jadwal_ujian_id' => $jadwalUjian->id,
                        'peserta_kelas_kuliah_id' => $pkk->id,
                    ],
                    [
                        'is_eligible' => $isEligible,
                        'keterangan_tidak_layak' => $keterangan,
                        'persentase_kehadiran' => $persentase,
                        'jumlah_hadir' => $jumlahHadir,
                        'status_cetak' => PesertaUjian::CETAK_BELUM,
                    ]
                );

                $result['total']++;
                $isEligible ? $result['eligible']++ : $result['not_eligible']++;
            }

            Log::info("UJIAN_GENERATE: Selesai generate peserta ujian", [
                'jadwal_ujian_id' => $jadwalUjian->id,
                'result' => $result,
            ]);

            return $result;
        });
    }
}

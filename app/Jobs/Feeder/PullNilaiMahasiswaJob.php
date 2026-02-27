<?php

namespace App\Jobs\Feeder;

use App\Models\PesertaKelasKuliah;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;

class PullNilaiMahasiswaJob extends BaseSyncJob
{
    protected function getEntityName(): string
    {
        return 'Nilai Mahasiswa';
    }

    protected function syncRow(array $row): void
    {
        // Update grade data for existing PesertaKelasKuliah
        // id_registrasi_mahasiswa and id_kelas_kuliah are the primary identifiers in Neo Feeder
        PesertaKelasKuliah::where('id_registrasi_mahasiswa', $row['id_registrasi_mahasiswa'])
            ->where('id_kelas_kuliah', $row['id_kelas_kuliah'])
            ->update([
                'nilai_angka' => $row['nilai_angka'] ?? null,
                'nilai_indeks' => $row['nilai_indeks'] ?? null,
                'nilai_huruf' => $row['nilai_huruf'] ?? null,
                'status_sinkronisasi' => 'synced',
                'last_synced_at' => Carbon::now(),
            ]);
    }
}

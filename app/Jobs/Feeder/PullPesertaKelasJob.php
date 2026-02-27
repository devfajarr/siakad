<?php

namespace App\Jobs\Feeder;

use App\Models\PesertaKelasKuliah;
use App\Models\RiwayatPendidikan;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;

class PullPesertaKelasJob extends BaseSyncJob
{
    protected function getEntityName(): string
    {
        return 'Peserta Kelas';
    }

    protected function syncRow(array $row): void
    {
        // Resolve local RiwayatPendidikan ID (bigint) from Feeder UUID (id_registrasi_mahasiswa)
        // Table riwayat_pendidikans id_feeder stores id_registrasi_mahasiswa from Feeder
        $riwayatId = RiwayatPendidikan::where('id_feeder', $row['id_registrasi_mahasiswa'])->value('id');

        if (!$riwayatId) {
            Log::warning("SYNC_WARNING: [Peserta Kelas] Riwayat Pendidikan (Registrasi) dengan ID Feeder {$row['id_registrasi_mahasiswa']} tidak ditemukan di lokal. Baris dilewati.");
            return;
        }

        PesertaKelasKuliah::updateOrCreate(
            [
                'id_registrasi_mahasiswa' => $row['id_registrasi_mahasiswa'],
                'id_kelas_kuliah' => $row['id_kelas_kuliah'],
            ],
            [
                'riwayat_pendidikan_id' => $riwayatId,
                'id_feeder' => $row['id_registrasi_mahasiswa'], // Simplified to 1 UUID to avoid Postgres UUID type error
                'status_sinkronisasi' => 'synced',
                'last_synced_at' => Carbon::now(),
                'sumber_data' => 'server'
            ]
        );
    }
}

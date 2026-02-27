<?php

namespace App\Jobs\Feeder;

use App\Models\DosenPengajarKelasKuliah;
use App\Models\Dosen;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;

class PullDosenPengajarJob extends BaseSyncJob
{
    protected function getEntityName(): string
    {
        return 'Dosen Pengajar';
    }

    protected function syncRow(array $row): void
    {
        // Resolve local Dosen ID (bigint) from Feeder UUID (external_id)
        // Usually id_dosen in GetDosenPengajar matches external_id in our dosens table
        $dosenId = Dosen::where('external_id', $row['id_dosen'])->value('id');

        if (!$dosenId) {
            Log::warning("SYNC_WARNING: [Dosen Pengajar] Dosen dengan ID Feeder {$row['id_dosen']} tidak ditemukan di lokal. Baris dilewati.");
            return;
        }

        DosenPengajarKelasKuliah::updateOrCreate(
            ['id_feeder' => $row['id_aktivitas_mengajar']],
            [
                'id_dosen' => $dosenId,
                'id_kelas_kuliah' => $row['id_kelas_kuliah'],
                'id_registrasi_dosen' => $row['id_registrasi_dosen'],
                'sks_substansi' => $row['sks_substansi_total'] ?? 0,
                'rencana_minggu_pertemuan' => $row['rencana_minggu_pertemuan'] ?? 0,
                'realisasi_minggu_pertemuan' => $row['realisasi_minggu_pertemuan'] ?? 0,
                'jenis_evaluasi' => $row['id_jenis_evaluasi'] ?? null,
                'status_sinkronisasi' => 'synced',
                'last_synced_at' => Carbon::now(),
                'sumber_data' => 'server'
            ]
        );
    }
}

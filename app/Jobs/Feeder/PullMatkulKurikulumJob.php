<?php

namespace App\Jobs\Feeder;

use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class PullMatkulKurikulumJob extends BaseSyncJob
{
    protected function getEntityName(): string
    {
        return 'Matkul Kurikulum';
    }

    protected function syncRow(array $row): void
    {
        // Many-to-Many Pivot Table sync
        DB::table('matkul_kurikulums')->updateOrInsert(
            [
                'id_kurikulum' => $row['id_kurikulum'],
                'id_matkul' => $row['id_matkul']
            ],
            [
                'semester' => $row['semester'] ?? 1,
                'sks_mata_kuliah' => $row['sks_mata_kuliah'] ?? 0,
                'sks_tatap_muka' => $row['sks_tatap_muka'] ?? 0,
                'sks_praktek' => $row['sks_praktek'] ?? 0,
                'sks_praktek_lapangan' => $row['sks_praktek_lapangan'] ?? 0,
                'sks_simulasi' => $row['sks_simulasi'] ?? 0,
                'apakah_wajib' => $row['apakah_wajib'] ?? true,
                'status_sinkronisasi' => 'synced',
                'last_synced_at' => Carbon::now(),
                'sumber_data' => 'server'
            ]
        );
    }
}

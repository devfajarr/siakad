<?php

namespace App\Jobs\Feeder;

use App\Models\Kurikulum;
use Carbon\Carbon;

class PullKurikulumJob extends BaseSyncJob
{
    protected function getEntityName(): string
    {
        return 'Kurikulum';
    }

    protected function syncRow(array $row): void
    {
        Kurikulum::updateOrCreate(
            ['id_feeder' => $row['id_kurikulum']],
            [
                'nama_kurikulum' => $row['nama_kurikulum'],
                'id_prodi' => $row['id_prodi'],
                'id_semester' => $row['id_semester'],
                'jumlah_sks_lulus' => $row['jumlah_sks_lulus'] ?? 0,
                'jumlah_sks_wajib' => $row['jumlah_sks_wajib'] ?? 0,
                'jumlah_sks_pilihan' => $row['jumlah_sks_pilihan'] ?? 0,
                'status_sinkronisasi' => 'synced',
                'last_synced_at' => Carbon::now(),
                'sumber_data' => 'server'
            ]
        );
    }
}

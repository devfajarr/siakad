<?php

namespace App\Jobs\Feeder;

use App\Models\MataKuliah;
use Carbon\Carbon;

class PullMataKuliahJob extends BaseSyncJob
{
    protected function getEntityName(): string
    {
        return 'Mata Kuliah';
    }

    protected function syncRow(array $row): void
    {
        MataKuliah::updateOrCreate(
            ['id_feeder' => $row['id_matkul']],
            [
                'id_matkul' => $row['id_matkul'],
                'id_prodi' => $row['id_prodi'],
                'kode_mk' => $row['kode_mata_kuliah'],
                'nama_mk' => $row['nama_mata_kuliah'],
                'id_jenis_mk' => $row['id_jenis_mata_kuliah'] ?? null,
                'sks_tatap_muka' => $row['sks_tatap_muka'] ?? 0,
                'sks_praktek' => $row['sks_praktek'] ?? 0,
                'sks_praktek_lapangan' => $row['sks_praktek_lapangan'] ?? 0,
                'sks_simulasi' => $row['sks_simulasi'] ?? 0,
                'metode_kuliah' => $row['metode_kuliah'] ?? null,
                'status_sinkronisasi' => 'synced',
                'last_synced_at' => Carbon::now(),
                'sumber_data' => 'server'
            ]
        );
    }
}

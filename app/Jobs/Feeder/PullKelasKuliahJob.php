<?php

namespace App\Jobs\Feeder;

use App\Models\KelasKuliah;
use Carbon\Carbon;

class PullKelasKuliahJob extends BaseSyncJob
{
    protected function getEntityName(): string
    {
        return 'Kelas Kuliah';
    }

    protected function syncRow(array $row): void
    {
        // Resolve SKS details from local Mata Kuliah master to ensure academic consistency
        $mataKuliah = \App\Models\MataKuliah::where('id_matkul', $row['id_matkul'])->first();

        KelasKuliah::updateOrCreate(
            ['id_feeder' => $row['id_kelas_kuliah']],
            [
                'id_prodi' => $row['id_prodi'],
                'id_semester' => $row['id_semester'],
                'id_matkul' => $row['id_matkul'],
                'id_kurikulum' => $row['id_kurikulum'] ?? null,
                'nama_kelas_kuliah' => $row['nama_kelas_kuliah'],
                'sks_mk' => $row['sks'] ?? ($mataKuliah->sks ?? 0),
                'sks_tm' => $mataKuliah->sks_tatap_muka ?? 0,
                'sks_prak' => $mataKuliah->sks_praktek ?? 0,
                'sks_prak_lap' => $mataKuliah->sks_praktek_lapangan ?? 0,
                'sks_sim' => $mataKuliah->sks_simulasi ?? 0,
                'kapasitas' => $row['kapasitas'] ?? 0,
                'status_sinkronisasi' => 'synced',
                'last_synced_at' => Carbon::now(),
                'sumber_data' => 'server'
            ]
        );
    }
}

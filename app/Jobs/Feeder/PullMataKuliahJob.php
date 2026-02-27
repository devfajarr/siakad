<?php

namespace App\Jobs\Feeder;

use App\Models\MataKuliah;
use Carbon\Carbon;
use Illuminate\Support\Facades\Log;

class PullMataKuliahJob extends BaseSyncJob
{
    protected function getEntityName(): string
    {
        return 'Mata Kuliah';
    }

    protected function syncRow(array $row): void
    {
        try {
            MataKuliah::updateOrCreate(
                ['id_feeder' => $row['id_matkul']],
                [
                    'id_matkul' => $row['id_matkul'],
                    'id_prodi' => $row['id_prodi'],
                    'kode_mk' => $row['kode_mata_kuliah'],
                    'nama_mk' => $row['nama_mata_kuliah'],
                    'sks' => $row['sks_mata_kuliah'] ?? 0,
                    'jenis_mk' => $row['id_jenis_mata_kuliah'] ?? null,
                    'kelompok_mk' => $row['id_kelompok_mata_kuliah'] ?? null,
                    'sks_tatap_muka' => $row['sks_tatap_muka'] ?? 0,
                    'sks_praktek' => $row['sks_praktek'] ?? 0,
                    'sks_praktek_lapangan' => $row['sks_praktek_lapangan'] ?? 0,
                    'sks_simulasi' => $row['sks_simulasi'] ?? 0,
                    'metode_kuliah' => $row['metode_kuliah'] ?? null,
                    'status_sinkronisasi' => 'synced',
                    'last_synced_at' => Carbon::now(),
                    'sumber_data' => 'server',
                    'sync_error_message' => null
                ]
            );

            Log::info("SYNC_SUCCESS: Mata Kuliah [{$row['kode_mata_kuliah']}] - {$row['nama_mata_kuliah']} berhasil diperbarui");
        } catch (\Exception $e) {
            Log::error("SYNC_ERROR: Gagal sinkron Mata Kuliah [{$row['kode_mata_kuliah']}]", [
                'message' => $e->getMessage(),
                'data' => $row
            ]);

            // Update status error jika memungkinkan
            MataKuliah::where('id_feeder', $row['id_matkul'])->update([
                'status_sinkronisasi' => 'synced', // Masap sinkron tapi ada error parsial (opsional)
                'sync_error_message' => $e->getMessage()
            ]);
        }
    }
}

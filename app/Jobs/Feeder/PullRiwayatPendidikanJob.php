<?php

namespace App\Jobs\Feeder;

use App\Models\RiwayatPendidikan;
use App\Models\Mahasiswa;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;

class PullRiwayatPendidikanJob extends BaseSyncJob
{
    protected function getEntityName(): string
    {
        return 'Riwayat Pendidikan';
    }

    protected function syncRow(array $row): void
    {
        // Neo Feeder uses id_registrasi_mahasiswa as the primary ID for Riwayat Pendidikan
        // Standardized to id_feeder in migration

        // Resolve local Mahasiswa ID (bigint) from Feeder UUID
        $mahasiswaId = Mahasiswa::where('id_feeder', $row['id_mahasiswa'])->value('id');

        if (!$mahasiswaId) {
            Log::warning("SYNC_WARNING: [Riwayat Pendidikan] Mahasiswa dengan ID Feeder {$row['id_mahasiswa']} tidak ditemukan di lokal. Baris dilewati.");
            return;
        }

        RiwayatPendidikan::updateOrCreate(
            ['id_feeder' => $row['id_registrasi_mahasiswa']],
            [
                'id_mahasiswa' => $mahasiswaId,
                'id_jenis_daftar' => $row['id_jenis_daftar'],
                'id_jalur_daftar' => $row['id_jalur_daftar'],
                'id_periode_masuk' => $row['id_periode_masuk'],
                'id_prodi' => $row['id_prodi'],
                'nim' => $row['nim'],
                'tanggal_daftar' => $row['tanggal_daftar'],
                'id_jenis_keluar' => $row['id_jenis_keluar'] ?? null,
                'tanggal_keluar' => $row['tanggal_keluar'] ?? null,
                'keterangan_keluar' => $row['keterangan_keluar'] ?? null,
                'status_sinkronisasi' => 'synced',
                'last_synced_at' => Carbon::now(),
                'sumber_data' => 'server'
            ]
        );
    }
}

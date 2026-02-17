<?php

namespace App\Services\Feeder\Reference;

use App\Models\RefPerguruanTinggi;
use App\Models\RefProdi;
use App\Models\ProfilPerguruanTinggi;
use App\Services\NeoFeederService;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class ReferenceDataSyncService extends NeoFeederService
{
    private const SYNC_BATCH_SIZE = 500;

    /**
     * Get all PTs (excluding local) for dropdown.
     */
    public function getAllPtExcludeLocal(): Collection
    {
        $this->ensureSynced();

        $localPtId = ProfilPerguruanTinggi::value('id_perguruan_tinggi');

        return RefPerguruanTinggi::query()
            ->select('id', 'kode_perguruan_tinggi', 'nama_perguruan_tinggi')
            ->when($localPtId, function ($query, $localPtId) {
                $query->where('id', '!=', $localPtId);
            })
            ->orderBy('nama_perguruan_tinggi')
            ->get();
    }

    /**
     * Get prodi for a specific PT (AJAX).
     */
    public function getProdiByPt(string $ptId): Collection
    {
        $this->ensureSynced();

        return RefProdi::query()
            ->where('id_perguruan_tinggi', $ptId)
            ->where('status', 'A')
            ->orderBy('nama_program_studi')
            ->get();
    }

    private function ensureSynced(): void
    {
        if (RefPerguruanTinggi::count() === 0) {
            $this->sync();
        }
    }

    /**
     * Sync GetAllProdi into both ref_perguruan_tinggis and ref_prodis.
     */
    public function sync(): void
    {
        try {
            $offset = 0;
            $totalSynced = 0;

            do {
                $data = $this->sendRequest('GetAllProdi', [
                    'limit' => self::SYNC_BATCH_SIZE,
                    'offset' => $offset,
                ]);

                if (empty($data)) {
                    break;
                }

                DB::transaction(function () use ($data) {
                    foreach ($data as $item) {
                        // 1. Upsert PT
                        if (!empty($item['id_perguruan_tinggi'])) {
                            RefPerguruanTinggi::upsert([
                                [
                                    'id' => $item['id_perguruan_tinggi'],
                                    'kode_perguruan_tinggi' => $item['kode_perguruan_tinggi'] ?? null,
                                    'nama_perguruan_tinggi' => $item['nama_perguruan_tinggi'] ?? 'Unknown PT',
                                    'created_at' => now(),
                                    'updated_at' => now(),
                                ]
                            ], ['id'], ['kode_perguruan_tinggi', 'nama_perguruan_tinggi', 'updated_at']);
                        }

                        // 2. Upsert Prodi
                        if (!empty($item['id_prodi'])) {
                            RefProdi::upsert([
                                [
                                    'id' => $item['id_prodi'],
                                    'kode_program_studi' => $item['kode_program_studi'] ?? null,
                                    'nama_program_studi' => $item['nama_program_studi'] ?? 'Unknown Prodi',
                                    'status' => $item['status'] ?? null,
                                    'id_jenjang_pendidikan' => $item['id_jenjang_pendidikan'] ?? null,
                                    'nama_jenjang_pendidikan' => $item['nama_jenjang_pendidikan'] ?? null,
                                    'id_perguruan_tinggi' => $item['id_perguruan_tinggi'],
                                    'created_at' => now(),
                                    'updated_at' => now(),
                                ]
                            ], ['id'], ['kode_program_studi', 'nama_program_studi', 'status', 'updated_at']);
                        }
                    }
                });

                $totalSynced += count($data);
                $offset += self::SYNC_BATCH_SIZE;

                Log::info("Sync GetAllProdi (Split Tables) batch: offset={$offset}, batch=" . count($data));

            } while (count($data) === self::SYNC_BATCH_SIZE);

            Log::info("Sync GetAllProdi (Split Tables) finished: {$totalSynced} records.");

        } catch (\Exception $e) {
            Log::error('Gagal sync ReferenceDataSyncService: ' . $e->getMessage());
        }
    }
}

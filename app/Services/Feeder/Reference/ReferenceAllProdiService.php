<?php

namespace App\Services\Feeder\Reference;

use App\Models\RefAllProdi;
use App\Models\ProfilPerguruanTinggi;
use App\Services\NeoFeederService;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class ReferenceAllProdiService extends NeoFeederService
{
    /**
     * Batch size for paginated sync from Feeder.
     */
    private const SYNC_BATCH_SIZE = 500;

    /**
     * Get all PTs (excluding local) for the PT Asal dropdown.
     * Returns distinct PTs from the ref_all_prodis table.
     */
    public function getAllPtExcludeLocal(): Collection
    {
        $this->ensureSynced();

        $localPtId = $this->getLocalPtId();

        $query = RefAllProdi::query()
            ->select('id_perguruan_tinggi', 'kode_perguruan_tinggi', 'nama_perguruan_tinggi')
            ->distinct();

        if ($localPtId) {
            $query->where('id_perguruan_tinggi', '!=', $localPtId);
        }

        return $query->orderBy('nama_perguruan_tinggi')->get();
    }

    /**
     * Get prodi for a specific PT (for AJAX dependent dropdown).
     */
    public function getProdiByPt(string $ptId): Collection
    {
        $this->ensureSynced();

        return RefAllProdi::byPerguruanTinggi($ptId)
            ->aktif()
            ->orderBy('nama_program_studi')
            ->get();
    }

    /**
     * Get the local PT ID from profil_perguruan_tinggis.
     */
    public function getLocalPtId(): ?string
    {
        return ProfilPerguruanTinggi::value('id_perguruan_tinggi');
    }

    /**
     * Ensure data is synced (auto-sync if table is empty).
     */
    private function ensureSynced(): void
    {
        if (RefAllProdi::count() === 0) {
            $this->syncFromFeeder();
        }
    }

    /**
     * Sync all national prodi data from GetAllProdi with pagination.
     */
    public function syncFromFeeder(): void
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
                        RefAllProdi::updateOrCreate(
                            ['id_prodi' => $item['id_prodi']],
                            [
                                'kode_program_studi' => $item['kode_program_studi'] ?? null,
                                'nama_program_studi' => $item['nama_program_studi'] ?? null,
                                'status' => $item['status'] ?? null,
                                'id_jenjang_pendidikan' => $item['id_jenjang_pendidikan'] ?? null,
                                'nama_jenjang_pendidikan' => $item['nama_jenjang_pendidikan'] ?? null,
                                'id_perguruan_tinggi' => $item['id_perguruan_tinggi'] ?? null,
                                'kode_perguruan_tinggi' => $item['kode_perguruan_tinggi'] ?? null,
                                'nama_perguruan_tinggi' => $item['nama_perguruan_tinggi'] ?? null,
                            ]
                        );
                    }
                });

                $totalSynced += count($data);
                $offset += self::SYNC_BATCH_SIZE;

                Log::info("Sync GetAllProdi batch: offset={$offset}, batch=" . count($data) . ", total={$totalSynced}");

            } while (count($data) === self::SYNC_BATCH_SIZE);

            Log::info("Sync GetAllProdi selesai: {$totalSynced} total records.");

        } catch (\Exception $e) {
            Log::error('Gagal sync GetAllProdi: ' . $e->getMessage());
        }
    }
}

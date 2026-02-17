<?php

namespace App\Services\Feeder\Reference;

use App\Models\JenisDaftar;
use App\Services\AkademikServices\AkademikRefService;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class ReferenceJenisPendaftaranService
{
    public function __construct(
        protected AkademikRefService $feederService
    ) {
    }

    /**
     * Get all Jenis Pendaftaran from local DB.
     * Auto-sync from Feeder if table is empty.
     */
    public function get(): Collection
    {
        if (JenisDaftar::count() === 0) {
            $this->syncFromFeeder();
        }

        return JenisDaftar::orderBy('id_jenis_daftar')->get();
    }

    /**
     * Sync Jenis Pendaftaran from Feeder API to local DB.
     */
    public function syncFromFeeder(): void
    {
        try {
            $data = $this->feederService->getJenisPendaftaran();

            DB::transaction(function () use ($data) {
                foreach ($data as $item) {
                    JenisDaftar::updateOrCreate(
                        ['id_jenis_daftar' => $item['id_jenis_daftar']],
                        ['nama_jenis_daftar' => $item['nama_jenis_daftar'] ?? null]
                    );
                }
            });

            Log::info('Sync Jenis Pendaftaran berhasil: ' . count($data) . ' records.');
        } catch (\Exception $e) {
            Log::error('Gagal sync Jenis Pendaftaran: ' . $e->getMessage());
        }
    }
}

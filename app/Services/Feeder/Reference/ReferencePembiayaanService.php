<?php

namespace App\Services\Feeder\Reference;

use App\Models\Pembiayaan;
use App\Services\NeoFeederService;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class ReferencePembiayaanService extends NeoFeederService
{
    /**
     * Get all Pembiayaan from local DB.
     * Auto-sync from Feeder if table is empty.
     */
    public function get(): Collection
    {
        if (Pembiayaan::count() === 0) {
            $this->syncFromFeeder();
        }

        return Pembiayaan::orderBy('nama_pembiayaan')->get();
    }

    /**
     * Sync Pembiayaan from Feeder API to local DB.
     */
    public function syncFromFeeder(): void
    {
        try {
            $data = $this->sendRequest('GetPembiayaan');

            DB::transaction(function () use ($data) {
                foreach ($data as $item) {
                    Pembiayaan::updateOrCreate(
                        ['id_pembiayaan' => $item['id_pembiayaan']],
                        ['nama_pembiayaan' => $item['nama_pembiayaan'] ?? null]
                    );
                }
            });

            Log::info('Sync Pembiayaan berhasil: ' . count($data) . ' records.');
        } catch (\Exception $e) {
            Log::error('Gagal sync Pembiayaan: ' . $e->getMessage());
        }
    }
}

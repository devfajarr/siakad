<?php

namespace App\Services\Feeder\Reference;

use App\Models\JalurPendaftaran;
use App\Services\AkademikServices\AkademikRefService;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class ReferenceJalurPendaftaranService
{
    public function __construct(
        protected AkademikRefService $feederService
    ) {
    }

    /**
     * Get all Jalur Pendaftaran from local DB.
     * Auto-sync from Feeder if table is empty.
     */
    public function get(): Collection
    {
        if (JalurPendaftaran::count() === 0) {
            $this->syncFromFeeder();
        }

        return JalurPendaftaran::orderBy('id_jalur_daftar')->get();
    }

    /**
     * Sync Jalur Pendaftaran from Feeder API to local DB.
     */
    public function syncFromFeeder(): void
    {
        try {
            $data = $this->feederService->getJalurMasuk();

            DB::transaction(function () use ($data) {
                foreach ($data as $item) {
                    JalurPendaftaran::updateOrCreate(
                        ['id_jalur_daftar' => $item['id_jalur_daftar']],
                        ['nama_jalur_daftar' => $item['nama_jalur_daftar'] ?? null]
                    );
                }
            });

            Log::info('Sync Jalur Pendaftaran berhasil: ' . count($data) . ' records.');
        } catch (\Exception $e) {
            Log::error('Gagal sync Jalur Pendaftaran: ' . $e->getMessage());
        }
    }
}

<?php

namespace App\Services\Feeder\Reference;

use App\Models\ProfilPerguruanTinggi;
use App\Services\NeoFeederService;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class ReferenceProfilPTService extends NeoFeederService
{
    /**
     * Get all Profil PT from local DB.
     * Auto-sync from Feeder if table is empty.
     */
    public function get(): Collection
    {
        if (ProfilPerguruanTinggi::count() === 0) {
            $this->syncFromFeeder();
        }

        return ProfilPerguruanTinggi::orderBy('nama_perguruan_tinggi')->get();
    }

    /**
     * Get the current institution's profile.
     */
    public function getOwn(): ?ProfilPerguruanTinggi
    {
        if (ProfilPerguruanTinggi::count() === 0) {
            $this->syncFromFeeder();
        }

        return ProfilPerguruanTinggi::first();
    }

    /**
     * Sync Profil PT from Feeder API (GetProfilPT) to local DB.
     * GetProfilPT returns the profile of the current institution only.
     */
    public function syncFromFeeder(): void
    {
        try {
            $data = $this->sendRequest('GetProfilPT');

            // GetProfilPT returns a single record (not an array of records)
            // but wrapped in the standard data array format
            DB::transaction(function () use ($data) {
                if (isset($data['id_perguruan_tinggi'])) {
                    // Single record response
                    ProfilPerguruanTinggi::updateOrCreate(
                        ['id_perguruan_tinggi' => $data['id_perguruan_tinggi']],
                        [
                            'kode_perguruan_tinggi' => $data['kode_perguruan_tinggi'] ?? null,
                            'nama_perguruan_tinggi' => $data['nama_perguruan_tinggi'] ?? null,
                        ]
                    );
                } else {
                    // Array response
                    foreach ($data as $item) {
                        if (!is_array($item) || !isset($item['id_perguruan_tinggi'])) {
                            continue;
                        }
                        ProfilPerguruanTinggi::updateOrCreate(
                            ['id_perguruan_tinggi' => $item['id_perguruan_tinggi']],
                            [
                                'kode_perguruan_tinggi' => $item['kode_perguruan_tinggi'] ?? null,
                                'nama_perguruan_tinggi' => $item['nama_perguruan_tinggi'] ?? null,
                            ]
                        );
                    }
                }
            });

            Log::info('Sync Profil Perguruan Tinggi berhasil.');
        } catch (\Exception $e) {
            Log::error('Gagal sync Profil Perguruan Tinggi: ' . $e->getMessage());
        }
    }
}

<?php

namespace App\Services\Feeder;

use App\Models\SkalaNilaiProdi;
use App\Services\NeoFeederService;
use Illuminate\Support\Facades\Log;
use Exception;

class SyncSkalaNilaiService
{
    protected $feeder;

    public function __construct(NeoFeederService $feeder)
    {
        $this->feeder = $feeder;
    }

    /**
     * Pull data skala nilai prodi dari Feeder.
     */
    public function pull()
    {
        Log::info("SYNC_PULL: Mulai tarik data [Skala Nilai Prodi]");

        try {
            // Get data dari Feeder
            $data = $this->feeder->execute('GetListSkalaNilaiProdi', [
                'limit' => 0, // Ambil semua
            ]);

            $count = 0;
            foreach ($data as $item) {
                SkalaNilaiProdi::updateOrCreate(
                    ['id_bobot_nilai' => $item['id_bobot_nilai']],
                    [
                        'id_prodi' => $item['id_prodi'],
                        'nilai_huruf' => $item['nilai_huruf'],
                        'nilai_indeks' => $item['nilai_indeks'],
                        'bobot_minimum' => $item['bobot_nilai_min'],
                        'bobot_maksimum' => $item['bobot_nilai_maks'],
                        'tanggal_mulai_efektif' => $item['tanggal_mulai_efektif'] ? date('Y-m-d', strtotime($item['tanggal_mulai_efektif'])) : null,
                        'tanggal_akhir_efektif' => $item['tanggal_akhir_efektif'] ? date('Y-m-d', strtotime($item['tanggal_akhir_efektif'])) : null,
                        'last_synced_at' => now(),
                    ]
                );
                $count++;
            }

            Log::info("SYNC_SUCCESS: Sinkronisasi [Skala Nilai Prodi] selesai", ['count' => $count]);
            return $count;

        } catch (Exception $e) {
            Log::error("SYSTEM_ERROR: Gagal sinkronisasi Skala Nilai Prodi", [
                'message' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            throw $e;
        }
    }
}

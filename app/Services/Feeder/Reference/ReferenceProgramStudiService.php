<?php

namespace App\Services\Feeder\Reference;

use App\Models\ProgramStudi;
use App\Services\AkademikServices\AkademikRefService;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class ReferenceProgramStudiService
{
    public function __construct(
        protected AkademikRefService $feederService
    ) {
    }

    /**
     * Get all Program Studi from local DB.
     * Auto-sync from Feeder if table is empty.
     */
    public function get(): Collection
    {
        if (ProgramStudi::count() === 0) {
            $this->syncFromFeeder();
        }

        return ProgramStudi::orderBy('nama_program_studi')->get();
    }

    /**
     * Sync Program Studi from Feeder API to local DB.
     */
    public function syncFromFeeder(): void
    {
        try {
            $data = $this->feederService->getProdi();

            DB::transaction(function () use ($data) {
                foreach ($data as $item) {
                    ProgramStudi::updateOrCreate(
                        ['id_prodi' => $item['id_prodi']],
                        [
                            'kode_program_studi' => $item['kode_program_studi'] ?? null,
                            'nama_program_studi' => $item['nama_program_studi'] ?? null,
                            'status' => $item['status'] ?? null,
                            'id_jenjang_pendidikan' => $item['id_jenjang_pendidikan'] ?? null,
                            'nama_jenjang_pendidikan' => $item['nama_jenjang_pendidikan'] ?? null,
                        ]
                    );
                }
            });

            Log::info('Sync Program Studi berhasil: ' . count($data) . ' records.');
        } catch (\Exception $e) {
            Log::error('Gagal sync Program Studi: ' . $e->getMessage());
        }
    }
}

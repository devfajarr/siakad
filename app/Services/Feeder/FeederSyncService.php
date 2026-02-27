<?php

namespace App\Services\Feeder;

use App\Services\NeoFeederService;
use App\Jobs\Feeder\PullMahasiswaJob;
use App\Jobs\Feeder\PullRiwayatPendidikanJob;
use App\Jobs\Feeder\PullMataKuliahJob;
use App\Jobs\Feeder\PullKurikulumJob;
use App\Jobs\Feeder\PullMatkulKurikulumJob;
use App\Jobs\Feeder\PullKelasKuliahJob;
use App\Jobs\Feeder\PullDosenPengajarJob;
use App\Jobs\Feeder\PullPesertaKelasJob;
use App\Jobs\Feeder\PullNilaiMahasiswaJob;
use Illuminate\Support\Facades\Bus;
use Illuminate\Support\Facades\Log;
use Exception;
use Throwable;

class FeederSyncService
{
    protected NeoFeederService $feederService;
    protected int $chunkSize = 100;

    public function __construct(NeoFeederService $feederService)
    {
        $this->feederService = $feederService;
    }

    /**
     * Dispatch sync jobs in batches for a specific entity.
     */
    public function dispatchSync(string $entity, array $filters = []): string
    {
        $jobClass = $this->getJobClass($entity);
        $act = $this->getFeederAction($entity);

        Log::info("SYNC_PULL: Mulai tarik data [{$entity}]", ['filters' => $filters]);

        // 1. Fetch data from Feeder
        $data = $this->feederService->execute($act, [
            'filter' => $this->buildFilterString($filters),
            'limit' => 0 // Fetch all for batching
        ]);

        if (empty($data)) {
            Log::info("SYNC_PULL: Tidak ada data [{$entity}] untuk ditarik.");
            return 'empty';
        }

        // 2. Chunk data and create jobs
        $chunks = array_chunk($data, $this->chunkSize);
        $jobs = [];

        foreach ($chunks as $chunk) {
            $jobs[] = new $jobClass($chunk);
        }

        // 3. Dispatch Batch
        $batch = Bus::batch($jobs)
            ->name("Sync {$entity}")
            ->then(function ($batch) use ($entity) {
                Log::info("SYNC_SUCCESS: Sinkronisasi [{$entity}] selesai.");
            })
            ->catch(function ($batch, Throwable $e) use ($entity) {
                Log::error("SYNC_ERROR: Batch [{$entity}] gagal", ['message' => $e->getMessage()]);
            })
            ->dispatch();

        return $batch->id;
    }

    protected function getJobClass(string $entity): string
    {
        return match ($entity) {
            'Mahasiswa' => PullMahasiswaJob::class,
            'RiwayatPendidikan' => PullRiwayatPendidikanJob::class,
            'MataKuliah' => PullMataKuliahJob::class,
            'Kurikulum' => PullKurikulumJob::class,
            'MatkulKurikulum' => PullMatkulKurikulumJob::class,
            'KelasKuliah' => PullKelasKuliahJob::class,
            'DosenPengajar' => PullDosenPengajarJob::class,
            'PesertaKelas' => PullPesertaKelasJob::class,
            'Nilai' => PullNilaiMahasiswaJob::class,
            default => throw new Exception("Entitas [{$entity}] tidak dikenali."),
        };
    }

    protected function getFeederAction(string $entity): string
    {
        return match ($entity) {
            'Mahasiswa' => 'GetBiodataMahasiswa',
            'RiwayatPendidikan' => 'GetListRiwayatPendidikanMahasiswa',
            'MataKuliah' => 'GetDetailMataKuliah',
            'Kurikulum' => 'GetListKurikulum',
            'MatkulKurikulum' => 'GetMatkulKurikulum',
            'KelasKuliah' => 'GetListKelasKuliah',
            'DosenPengajar' => 'GetDosenPengajarKelasKuliah',
            'PesertaKelas' => 'GetPesertaKelasKuliah',
            'Nilai' => 'GetNilaiPerkuliahanKelas',
            default => throw new Exception("Action Feeder untuk [{$entity}] tidak ditemukan."),
        };
    }

    protected function buildFilterString(array $filters): string
    {
        if (empty($filters))
            return "";

        $parts = [];
        foreach ($filters as $key => $value) {
            $parts[] = "{$key} = '{$value}'";
        }
        return implode(" AND ", $parts);
    }
}

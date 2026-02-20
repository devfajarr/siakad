<?php

namespace App\Console\Commands;

use App\Models\AlatTransportasi;
use App\Models\JenjangPendidikan;
use App\Models\JenisTinggal;
use App\Models\Pekerjaan;
use App\Models\Penghasilan;
use App\Services\AkademikServices\AkademikRefService;
use App\Services\ReferensiServices\AdministratifRefService;
use App\Services\ReferensiServices\PribadiRefService;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class SyncBiodataCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'sync:biodata';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Sync Biodata References (Alat Transportasi, Jenis Tinggal, Pekerjaan, Penghasilan, Jenjang Pendidikan)';

    /**
     * Execute the console command.
     */
    public function handle(
        AdministratifRefService $adminService,
        PribadiRefService $pribadiService,
        AkademikRefService $akademikService
    ) {
        $this->info('Starting Sync Biodata References...');
        $startTime = microtime(true);

        try {
            DB::beginTransaction();

            // 1. Jenis Tinggal
            $this->syncReference(
                'Jenis Tinggal',
                fn() => $adminService->getJenisTinggal(),
                JenisTinggal::class,
                ['id_jenis_tinggal', 'nama_jenis_tinggal']
            );

            // 2. Alat Transportasi
            $this->syncReference(
                'Alat Transportasi',
                fn() => $adminService->getAlatTransportasi(),
                AlatTransportasi::class,
                ['id_alat_transportasi', 'nama_alat_transportasi']
            );

            // 3. Pekerjaan
            $this->syncReference(
                'Pekerjaan',
                fn() => $pribadiService->getPekerjaan('', 500, 0),
                Pekerjaan::class,
                ['id_pekerjaan', 'nama_pekerjaan']
            );

            // 4. Penghasilan
            $this->syncReference(
                'Penghasilan',
                fn() => $pribadiService->getPenghasilan(), // Assuming this returns all
                Penghasilan::class,
                ['id_penghasilan', 'nama_penghasilan']
            );

            // 5. Jenjang Pendidikan
            $this->syncReference(
                'Jenjang Pendidikan',
                fn() => $akademikService->getJenjangPendidikan('', 500, 0), // Assuming limit needed
                JenjangPendidikan::class,
                ['id_jenjang_didik', 'nama_jenjang_didik']
            );

            DB::commit();
            $duration = microtime(true) - $startTime;
            $this->info("Biodata References Sync completed successfully in " . round($duration, 2) . " seconds.");

        } catch (\Exception $e) {
            DB::rollBack();
            $this->error("Sync failed: " . $e->getMessage());
            return Command::FAILURE;
        }

        return Command::SUCCESS;
    }

    private function syncReference($name, callable $fetcher, $modelClass, $mapping)
    {
        $this->line("Syncing $name...");

        $data = $fetcher();
        $count = count($data);

        if ($count === 0) {
            $this->warn("No data found for $name.");
            return;
        }

        $upsertData = [];
        $timestamp = now();
        $idKey = $mapping[0];
        $nameKey = $mapping[1];

        foreach ($data as $item) {
            $upsertData[] = [
                $idKey => $item[$idKey],
                $nameKey => $item[$nameKey],
                'created_at' => $timestamp,
                'updated_at' => $timestamp,
            ];
        }

        $modelClass::upsert(
            $upsertData,
            [$idKey],
            [$nameKey, 'updated_at']
        );

        $this->info("Synced $count records for $name.");
    }
}

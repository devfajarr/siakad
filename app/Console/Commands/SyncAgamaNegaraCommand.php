<?php

namespace App\Console\Commands;

use App\Models\Agama;
use App\Models\Negara;
use App\Services\ReferensiServices\PribadiRefService;
use App\Services\ReferensiServices\WilayahRefService;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class SyncAgamaNegaraCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'sync:agama-negara';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Sync Agama and Negara References';

    /**
     * Execute the console command.
     */
    public function handle(
        PribadiRefService $pribadiService,
        WilayahRefService $wilayahService
    ) {
        $this->info('Starting Sync Agama & Negara...');
        $startTime = microtime(true);

        try {
            DB::beginTransaction();

            // 1. Agama
            $this->syncReference(
                'Agama',
                fn() => $pribadiService->getAgama(),
                Agama::class,
                ['id_agama', 'nama_agama']
            );

            // 2. Negara
            $this->syncReference(
                'Negara',
                fn() => $wilayahService->getNegara(),
                Negara::class,
                ['id_negara', 'nama_negara']
            );

            DB::commit();
            $duration = microtime(true) - $startTime;
            $this->info("Sync completed successfully in " . round($duration, 2) . " seconds.");

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

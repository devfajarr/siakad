<?php

namespace App\Console\Commands;

use App\Models\ReferenceWilayah;
use App\Services\ReferensiServices\WilayahRefService;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class SyncWilayahCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'sync:wilayah';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Sync Wilayah data from Neo Feeder';

    /**
     * Execute the console command.
     */
    public function handle(WilayahRefService $wilayahService)
    {
        $this->info('Starting Sync Wilayah...');
        $startTime = microtime(true);

        $limit = 500;
        $offset = 0;
        $totalSynced = 0;
        $batchCount = 0;

        try {
            // DB::beginTransaction(); // Transaksi besar mungkin membebani jika data ribuan, pertimbangkan commit per batch atau matikan jika timeout.

            // Opsional: Truncate jika ingin full refresh
            // ReferenceWilayah::truncate();

            do {
                $this->line("Fetching batch " . ($batchCount + 1) . " (Limit: $limit, Offset: $offset)...");

                $data = $wilayahService->getWilayah('', $limit, $offset);
                $count = count($data);

                if ($count === 0) {
                    $this->info("No more data found.");
                    break;
                }

                $upsertData = [];
                $timestamp = now();

                foreach ($data as $item) {
                    $upsertData[] = [
                        'id_wilayah' => $item['id_wilayah'],
                        'nama_wilayah' => $item['nama_wilayah'],
                        'id_level_wilayah' => $item['id_level_wilayah'],
                        'id_induk_wilayah' => isset($item['id_induk_wilayah']) && !empty($item['id_induk_wilayah']) ? $item['id_induk_wilayah'] : null,
                        'id_negara' => isset($item['id_negara']) && !empty($item['id_negara']) ? $item['id_negara'] : null,
                        'created_at' => $timestamp,
                        'updated_at' => $timestamp,
                    ];
                }

                ReferenceWilayah::upsert(
                    $upsertData,
                    ['id_wilayah'],
                    ['nama_wilayah', 'id_level_wilayah', 'id_induk_wilayah', 'id_negara', 'updated_at']
                );

                $totalSynced += $count;
                $offset += $limit;
                $batchCount++;

                $this->info("Batch " . $batchCount . " synced: $count records. Total so far: $totalSynced");

                // Jeda sebentar untuk menghindari rate limit jika ada
                // usleep(100000); 

            } while ($count >= $limit);

            // DB::commit();
            $duration = microtime(true) - $startTime;
            $this->info("Sync completed successfully in " . round($duration, 2) . " seconds.");
            $this->info("Total Records Synced: $totalSynced");

        } catch (\Exception $e) {
            // DB::rollBack();
            $this->error("Sync failed: " . $e->getMessage());
            return Command::FAILURE;
        }

        return Command::SUCCESS;
    }
}

<?php

namespace App\Console\Commands;

use App\Models\JalurPendaftaran;
use App\Models\JenisDaftar;
use App\Models\Semester;
use App\Services\AkademikServices\AkademikRefService;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class SyncReferensiPendidikanCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'sync:referensi-pendidikan';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Sync Jenis Daftar, Jalur Pendaftaran, and Semester References';

    /**
     * Execute the console command.
     */
    public function handle(AkademikRefService $akademikService)
    {
        $this->info('Starting Sync Referensi Pendidikan...');
        $startTime = microtime(true);

        try {
            DB::beginTransaction();

            // 1. Jenis Daftar
            $this->syncReference(
                'Jenis Daftar',
                fn() => $akademikService->getJenisPendaftaran(),
                JenisDaftar::class,
                ['id_jenis_daftar' => 'id_jenis_daftar', 'nama_jenis_daftar' => 'nama_jenis_daftar']
            );

            // 2. Jalur Pendaftaran
            $this->syncReference(
                'Jalur Pendaftaran',
                fn() => $akademikService->getJalurMasuk(),
                JalurPendaftaran::class,
                ['id_jalur_daftar' => 'id_jalur_masuk', 'nama_jalur_daftar' => 'nama_jalur_masuk']
            );

            // 3. Semester (Updated with new fields)
            $this->syncReference(
                'Semester',
                fn() => $akademikService->getSemester(),
                Semester::class,
                [
                    'id_semester' => 'id_semester',
                    'nama_semester' => 'nama_semester',
                    'id_tahun_ajaran' => 'id_tahun_ajaran',
                    'semester' => 'semester',
                    'a_periode_aktif' => 'a_periode_aktif',
                    'tanggal_mulai' => 'tanggal_mulai',
                    'tanggal_selesai' => 'tanggal_selesai'
                ]
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

        // Mapping: ModelKey => ApiKey
        // The first key in mapping is assumed to be the Primary Key for upsert
        $modelKeys = array_keys($mapping);
        $primaryKey = $modelKeys[0];

        foreach ($data as $item) {
            $record = [
                'created_at' => $timestamp,
                'updated_at' => $timestamp,
            ];

            foreach ($mapping as $modelKey => $apiKey) {
                if (isset($item[$apiKey])) {
                    $record[$modelKey] = $item[$apiKey];
                }
            }

            // Validate Primary Key presence
            if (!isset($record[$primaryKey])) {
                // $this->warn("Skipping record: missing primary key $primaryKey");
                continue;
            }

            $upsertData[] = $record;
        }

        if (empty($upsertData)) {
            $this->warn("No valid data to sync for $name.");
            return;
        }

        $modelClass::upsert(
            $upsertData,
            [$primaryKey],
            array_merge($modelKeys, ['updated_at'])
        );

        $this->info("Synced " . count($upsertData) . " records for $name.");
    }
}

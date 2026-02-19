<?php

namespace App\Console\Commands;

use App\Models\MataKuliah;
use App\Services\AkademikServices\AkademikRefService;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class SyncMataKuliahFromServer extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'sync:matakuliah-from-server {--limit=500 : Limit data per batch} {--force : Force sync all data}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Sync Data Mata Kuliah dari Server API Pusat';

    /**
     * Execute the console command.
     */
    public function handle(AkademikRefService $akademikService)
    {
        $this->info('Mulai sinkronisasi Mata Kuliah dari Server...');
        $startTime = microtime(true);

        try {
            // 1. Get Total Count
            $this->info('Mengambil informasi jumlah data...');
            $total = $akademikService->getCountMataKuliah();

            if ($total === 0) {
                $this->warn('Tidak ada data Mata Kuliah di server.');
                return Command::SUCCESS;
            }

            $this->info("Ditemukan {$total} data Mata Kuliah.");

            // 2. Prepare for Batch Processing
            $limit = (int) $this->option('limit');
            $batches = ceil($total / $limit);
            $bar = $this->output->createProgressBar($total);
            $bar->start();

            $syncedCount = 0;
            $updatedCount = 0;
            $createdCount = 0;
            $failedCount = 0;

            for ($i = 0; $i < $batches; $i++) {
                $offset = $i * $limit;

                try {
                    $data = $akademikService->getMataKuliah('', $limit, $offset);

                    if (empty($data)) {
                        break;
                    }

                    foreach ($data as $item) {
                        try {
                            // Fetch Detail for extra fields
                            $detail = $akademikService->getDetailMataKuliah($item['id_matkul']);

                            // Merge detail data if available
                            if (!empty($detail)) {
                                // Feeder detail response usually returns a single item array or the item itself
                                $detailData = isset($detail[0]) ? $detail[0] : $detail;
                                $item = array_merge($item, $detailData);
                            }

                            $this->syncItem($item, $createdCount, $updatedCount);
                            $syncedCount++;
                        } catch (\Exception $e) {
                            $failedCount++;
                            Log::error("Gagal sync Mata Kuliah [{$item['kode_mata_kuliah']}]: " . $e->getMessage());
                        }
                        $bar->advance();
                    }

                    // Optional: Sleep to prevent rate limiting if necessary
                    // usleep(100000); 

                } catch (\Exception $e) {
                    $this->error("Gagal mengambil batch ke-" . ($i + 1) . ": " . $e->getMessage());
                    Log::error("Batch Sync Error: " . $e->getMessage());
                }
            }

            $bar->finish();
            $this->newLine(2);

            $duration = microtime(true) - $startTime;
            $this->info("Sinkronisasi selesai dalam " . round($duration, 2) . " detik.");
            $this->table(
                ['Keterangan', 'Jumlah'],
                [
                    ['Total Data Server', $total],
                    ['Berhasil Sinkron', $syncedCount],
                    ['Created (Baru)', $createdCount],
                    ['Updated (Diubah)', $updatedCount],
                    ['Gagal', $failedCount],
                ]
            );

            return Command::SUCCESS;

        } catch (\Exception $e) {
            $this->error("Terjadi kesalahan fatal: " . $e->getMessage());
            Log::error("Fatal Sync Error: " . $e->getMessage());
            return Command::FAILURE;
        }
    }

    private function syncItem(array $item, &$createdCount, &$updatedCount)
    {
        // Mapping Logic
        $attributes = [
            'id_matkul' => $item['id_matkul'], // Server UUID
            'kode_mk' => $item['kode_mata_kuliah'], // Ensure unique in DB
        ];

        // Helper to safe parsing date
        $parseDate = function ($date) {
            if (empty($date)) {
                return null;
            }

            try {
                return \Carbon\Carbon::parse($date)
                    ->setTimezone(config('app.timezone'))
                    ->format('Y-m-d');
            } catch (\Exception $e) {
                return null;
            }
        };


        $values = [
            'id_prodi' => $item['id_prodi'],
            'nama_mk' => $item['nama_mata_kuliah'],
            'sks' => (float) $item['sks_mata_kuliah'],
            'sks_tatap_muka' => (float) ($item['sks_tatap_muka'] ?? 0),
            'sks_praktek' => (float) ($item['sks_praktek'] ?? 0),
            'sks_praktek_lapangan' => (float) ($item['sks_praktek_lapangan'] ?? 0),
            'sks_simulasi' => (float) ($item['sks_simulasi'] ?? 0),
            'metode_kuliah' => $item['metode_kuliah'] ?? null,
            'tanggal_mulai_efektif' => $parseDate($item['tanggal_mulai_efektif'] ?? null),
            'tanggal_akhir_efektif' => $parseDate($item['tanggal_selesai_efektif'] ?? null),

            'jenis_mk' => $item['id_jenis_mata_kuliah'] ?? null, // Menggunakan ID sesuai dictionary
            'kelompok_mk' => $item['id_kelompok_mata_kuliah'] ?? null, // Menggunakan ID sesuai dictionary
            'semester' => isset($item['semester']) ? (int) $item['semester'] : null, // Sesuaikan field API jika ada
            'status_aktif' => isset($item['status_sync']) && $item['status_sync'] == 'aktif', // Logic status aktif

            // Monitoring Columns
            'sumber_data' => 'server',
            'status_sinkronisasi' => 'synced',
            'is_deleted_server' => false,
            'last_synced_at' => now(),
        ];

        // Gunakan updateOrCreate dengan kondisi unik (prioritas id_matkul, fallback kode_mk logic handled partly by DB constraint)
        // Kita gunakan kode_mk sebagai unique key utama juga jika id_matkul belum ada di lokal tapi kode sama?
        // Strategi: Match by id_matkul jika ada, atau kode_mk.

        $mataKuliah = MataKuliah::where('id_matkul', $item['id_matkul'])
            ->orWhere('kode_mk', $item['kode_mata_kuliah'])
            ->first();

        if ($mataKuliah) {
            $mataKuliah->update(array_merge($attributes, $values));
            $updatedCount++;
        } else {
            MataKuliah::create(array_merge($attributes, $values));
            $createdCount++;
        }
    }
}

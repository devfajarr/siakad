<?php

namespace App\Console\Commands;

use App\Models\KelasKuliah;
use App\Services\AkademikServices\AkademikRefService;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;

class SyncKelasKuliahFromServer extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'sync:kelas-kuliah-from-server
        {--limit=100 : Limit data per batch}
        {--filter= : Filter query tambahan (opsional)}
        {--force : Force sync semua data}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Sinkronisasi data Kelas Kuliah dari Neo Feeder Server ke database lokal';

    /**
     * Execute the console command.
     */
    public function handle(AkademikRefService $akademikService)
    {
        $this->info('═══════════════════════════════════════════════════');
        $this->info('  Sync Kelas Kuliah dari Server');
        $this->info('═══════════════════════════════════════════════════');
        $this->newLine();

        $startTime = microtime(true);
        $filter = $this->option('filter') ?? '';
        $batchSize = (int) $this->option('limit');

        $syncedCount = 0;
        $createdCount = 0;
        $updatedCount = 0;
        $failedCount = 0;
        $failedItems = [];
        $batch = 0;

        try {
            $this->info("📦 Mengambil data per batch ({$batchSize} data/batch)...");
            $this->newLine();

            while (true) {
                $offset = $batch * $batchSize;
                $batchNum = $batch + 1;

                try {
                    $this->line("  ▸ Batch #{$batchNum} (offset: {$offset})...");

                    $data = $akademikService->getListKelasKuliah($filter, $batchSize, $offset);

                    // Jika tidak ada data, berarti sudah selesai
                    if (empty($data)) {
                        $this->line("  ✓ Batch #{$batchNum}: Tidak ada data lagi.");
                        break;
                    }

                    $countInBatch = count($data);
                    $this->line("  ✓ Batch #{$batchNum}: Diterima {$countInBatch} data.");

                    foreach ($data as $item) {
                        try {
                            // Fetch detail untuk mendapatkan field lengkap
                            // (tanggal_mulai/akhir_efektif, bahasan, kapasitas, mode, dll.)
                            $detail = $akademikService->getDetailKelasKuliah($item['id_kelas_kuliah']);
                            if (!empty($detail)) {
                                $detailData = isset($detail[0]) ? $detail[0] : $detail;
                                $item = array_merge($item, $detailData);
                            }

                            $this->syncItem($item, $createdCount, $updatedCount);
                            $syncedCount++;
                        } catch (\Exception $e) {
                            $failedCount++;
                            $nama = $item['nama_kelas_kuliah'] ?? $item['id_kelas_kuliah'] ?? 'unknown';
                            $failedItems[] = $nama;
                            Log::error("Gagal sync Kelas Kuliah [{$nama}]: " . $e->getMessage());
                        }
                    }

                    // Jika data yang diterima kurang dari batch size, berarti sudah halaman terakhir
                    if ($countInBatch < $batchSize) {
                        break;
                    }

                    $batch++;

                } catch (\Exception $e) {
                    $this->newLine();
                    $this->error("Gagal mengambil batch #{$batchNum}: " . $e->getMessage());
                    Log::error("Batch Sync KelasKuliah Error: " . $e->getMessage());
                    // Stop jika batch gagal, agar tidak infinite loop
                    break;
                }
            }

            $this->newLine();

            // ─── Summary ────────────────────────────────────────
            $duration = round(microtime(true) - $startTime, 2);

            $this->info('═══════════════════════════════════════════════════');
            $this->info("  Sinkronisasi selesai dalam {$duration} detik");
            $this->info('═══════════════════════════════════════════════════');
            $this->newLine();

            $this->table(
                ['Keterangan', 'Jumlah'],
                [
                    ['Total Batch Diproses', $batch + 1],
                    ['Berhasil Sinkron', $syncedCount],
                    ['Baru (Created)', $createdCount],
                    ['Diperbarui (Updated)', $updatedCount],
                    ['Gagal', $failedCount],
                ]
            );

            if (!empty($failedItems)) {
                $this->newLine();
                $this->warn('Data gagal sync:');
                foreach (array_slice($failedItems, 0, 10) as $item) {
                    $this->line("  ✗ {$item}");
                }
                if (count($failedItems) > 10) {
                    $this->line("  ... dan " . (count($failedItems) - 10) . " lainnya. Cek log untuk detail.");
                }
            }

            return Command::SUCCESS;

        } catch (\Exception $e) {
            $this->newLine();
            $this->error("Terjadi kesalahan fatal: " . $e->getMessage());
            Log::error("Fatal Sync KelasKuliah Error: " . $e->getMessage());
            return Command::FAILURE;
        }
    }

    /**
     * Sync single Kelas Kuliah item ke database.
     *
     * Hanya mapping data kelas kuliah saja, TIDAK termasuk dosen pengajar.
     *
     * @param array $item Data dari API GetListKelasKuliah
     * @param int $createdCount Counter referensi
     * @param int $updatedCount Counter referensi
     */
    private function syncItem(array $item, int &$createdCount, int &$updatedCount): void
    {
        $parseDate = function ($date) {
            if (empty($date)) {
                return null;
            }
            try {
                return \Carbon\Carbon::parse($date)->format('Y-m-d');
            } catch (\Exception $e) {
                return null;
            }
        };

        $values = [
            'id_kelas_kuliah' => $item['id_kelas_kuliah'],
            'id_prodi' => $item['id_prodi'] ?? null,
            'id_semester' => $item['id_semester'] ?? null,
            'id_matkul' => $item['id_matkul'] ?? null,
            'nama_kelas_kuliah' => $item['nama_kelas_kuliah'],
            'sks_mk' => (float) ($item['sks_mk'] ?? 0),
            'sks_tm' => (float) ($item['sks_tm'] ?? 0),
            'sks_prak' => (float) ($item['sks_prak'] ?? 0),
            'sks_prak_lap' => (float) ($item['sks_prak_lap'] ?? 0),
            'sks_sim' => (float) ($item['sks_sim'] ?? 0),
            'bahasan' => $item['bahasan'] ?? null,
            'kapasitas' => isset($item['kapasitas']) ? (int) $item['kapasitas'] : null,
            'tanggal_mulai_efektif' => $parseDate($item['tanggal_mulai_efektif'] ?? null),
            'tanggal_akhir_efektif' => $parseDate($item['tanggal_akhir_efektif'] ?? null),
            'mode' => $item['mode'] ?? null,
            'lingkup' => $item['lingkup'] ?? null,
            'apa_untuk_pditt' => (int) ($item['apa_untuk_pditt'] ?? 0),
            'a_selenggara_pditt' => (int) ($item['a_selenggara_pditt'] ?? 0),
            'id_mou' => $item['id_mou'] ?? null,

            // Monitoring
            'sumber_data' => 'server',
            'status_sinkronisasi' => KelasKuliah::STATUS_SYNCED,
            'is_deleted_server' => false,
            'last_synced_at' => now(),
        ];

        // updateOrCreate berdasarkan id_kelas_kuliah (server UUID)
        $existing = KelasKuliah::where('id_kelas_kuliah', $item['id_kelas_kuliah'])->first();

        if ($existing) {
            $existing->update($values);
            $updatedCount++;
        } else {
            KelasKuliah::create($values);
            $createdCount++;
        }
    }
}

<?php

namespace App\Console\Commands;

use App\Models\SkalaNilaiProdi;
use App\Services\AkademikServices\AkademikRefService;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class SyncSkalaNilaiProdiFromServer extends Command
{
    protected $signature = 'sync:skala-nilai-prodi-from-server
        {--prodi= : Filter berdasarkan id_prodi (UUID, opsional)}';

    protected $description = 'Sinkronisasi data Skala Nilai Prodi dari Neo Feeder Server';

    public function handle(AkademikRefService $akademikService): int
    {
        $this->info('═══════════════════════════════════════════════════');
        $this->info('  Sync Skala Nilai Prodi dari Server');
        $this->info('═══════════════════════════════════════════════════');
        $this->newLine();

        $startTime = microtime(true);

        $created = 0;
        $updated = 0;
        $failed = 0;

        try {
            // ─── 1. Build filter ──────────────────────────────
            $filter = '';
            if ($this->option('prodi')) {
                $filter = "id_prodi='" . $this->option('prodi') . "'";
            }

            $this->info('📡 Mengambil data skala nilai dari server...');

            // ─── 2. Ambil data via GetDetailSkalaNilaiProdi ───
            // GetDetail mengembalikan per-item data (nilai_huruf, bobot, dll)
            // GetList hanya mengembalikan summary per prodi
            $data = $akademikService->getDetailSkalaNilaiProdi($filter);

            if (empty($data)) {
                $this->warn('Tidak ada data Skala Nilai yang dikembalikan server.');
                return Command::SUCCESS;
            }

            $this->info("📦 Diterima " . count($data) . " record skala nilai.");
            $this->newLine();

            $bar = $this->output->createProgressBar(count($data));
            $bar->setFormat(' %current%/%max% [%bar%] %percent:3s%% | %elapsed:6s%');
            $bar->start();

            // ─── 3. Upsert batch ──────────────────────────────
            $upsertData = [];
            $now = now();

            foreach ($data as $item) {
                try {
                    $idBobotNilai = $item['id_bobot_nilai'] ?? null;

                    if (empty($idBobotNilai)) {
                        $failed++;
                        Log::warning('Skala nilai tanpa id_bobot_nilai, skip.');
                        $bar->advance();
                        continue;
                    }

                    $upsertData[] = [
                        'id_bobot_nilai' => $idBobotNilai,
                        'id_prodi' => $item['id_prodi'] ?? null,
                        'nilai_huruf' => $item['nilai_huruf'] ?? null,
                        'nilai_indeks' => isset($item['nilai_indeks']) && $item['nilai_indeks'] !== '' ? (float) $item['nilai_indeks'] : 0,
                        'bobot_minimum' => isset($item['bobot_minimum']) && $item['bobot_minimum'] !== '' ? (float) $item['bobot_minimum'] : 0,
                        'bobot_maksimum' => isset($item['bobot_maksimum']) && $item['bobot_maksimum'] !== '' ? (float) $item['bobot_maksimum'] : 0,
                        'tanggal_mulai_efektif' => $this->parseDate($item['tanggal_mulai_efektif'] ?? null),
                        'tanggal_akhir_efektif' => $this->parseDate($item['tanggal_akhir_efektif'] ?? null),
                        'last_synced_at' => $now,
                        'created_at' => $now,
                        'updated_at' => $now,
                    ];

                    $bar->advance();

                } catch (\Exception $e) {
                    $failed++;
                    $identifier = $item['id_bobot_nilai'] ?? 'unknown';
                    Log::error("Gagal mapping skala nilai [{$identifier}]: " . $e->getMessage());
                    $bar->advance();
                }
            }

            // Batch upsert
            if (!empty($upsertData)) {
                $beforeCount = SkalaNilaiProdi::count();

                DB::table('skala_nilai_prodis')->upsert(
                    $upsertData,
                    ['id_bobot_nilai'],
                    ['id_prodi', 'nilai_huruf', 'nilai_indeks', 'bobot_minimum', 'bobot_maksimum', 'tanggal_mulai_efektif', 'tanggal_akhir_efektif', 'last_synced_at', 'updated_at']
                );

                $afterCount = SkalaNilaiProdi::count();
                $created = $afterCount - $beforeCount;
                $updated = count($upsertData) - $failed - $created;
            }

            $bar->finish();
            $this->newLine(2);

            // ─── 4. Summary ──────────────────────────────────
            $duration = round(microtime(true) - $startTime, 2);

            $this->info('═══════════════════════════════════════════════════');
            $this->info("  Sinkronisasi selesai dalam {$duration} detik");
            $this->info('═══════════════════════════════════════════════════');
            $this->newLine();

            $this->table(
                ['Keterangan', 'Jumlah'],
                [
                    ['Total Data Diterima', count($data)],
                    ['Baru (Created)', $created],
                    ['Diperbarui (Updated)', $updated],
                    ['Gagal', $failed],
                ]
            );

            return Command::SUCCESS;

        } catch (\Exception $e) {
            $this->newLine();
            $this->error("Terjadi kesalahan: " . $e->getMessage());
            Log::error("SyncSkalaNilaiProdi Error: " . $e->getMessage());
            return Command::FAILURE;
        }
    }

    /**
     * Parse tanggal dari format server (bisa DD-MM-YYYY atau YYYY-MM-DD).
     */
    private function parseDate(?string $date): ?string
    {
        if (empty($date)) {
            return null;
        }

        try {
            // Server bisa kirim DD-MM-YYYY
            if (preg_match('/^\d{2}-\d{2}-\d{4}$/', $date)) {
                return \Carbon\Carbon::createFromFormat('d-m-Y', $date)->toDateString();
            }

            return \Carbon\Carbon::parse($date)->toDateString();
        } catch (\Exception $e) {
            Log::warning("Gagal parse tanggal [{$date}]: " . $e->getMessage());
            return null;
        }
    }
}

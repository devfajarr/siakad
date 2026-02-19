<?php

namespace App\Console\Commands;

use App\Models\KelasKuliah;
use App\Models\PesertaKelasKuliah;
use App\Services\AkademikServices\AkademikRefService;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class SyncPesertaKelasKuliahFromServer extends Command
{
    protected $signature = 'sync:peserta-kelas-kuliah-from-server
        {--limit=100 : Limit peserta per batch API call}
        {--semester= : Filter berdasarkan id_semester (opsional)}
        {--kelas= : Sync hanya untuk id_kelas_kuliah tertentu (UUID)}
        {--chunk=50 : Jumlah kelas kuliah diproses per chunk DB}';

    protected $description = 'Sinkronisasi data Peserta Kelas Kuliah (KRS) dari Neo Feeder Server';

    public function handle(AkademikRefService $akademikService): int
    {
        $this->info('═══════════════════════════════════════════════════');
        $this->info('  Sync Peserta Kelas Kuliah dari Server');
        $this->info('═══════════════════════════════════════════════════');
        $this->newLine();

        $startTime = microtime(true);
        $batchSize = (int) $this->option('limit');
        $chunkSize = (int) $this->option('chunk');

        // Counters
        $totalKelas = 0;
        $totalPeserta = 0;
        $createdCount = 0;
        $updatedCount = 0;
        $failedKelas = 0;
        $failedPeserta = 0;

        try {
            // ─── 1. Query kelas_kuliah yang sudah synced dari server ──
            $query = KelasKuliah::where('sumber_data', 'server')
                ->where('is_deleted_server', false)
                ->whereNotNull('id_kelas_kuliah');

            if ($this->option('kelas')) {
                $query->where('id_kelas_kuliah', $this->option('kelas'));
            }

            if ($this->option('semester')) {
                $query->where('id_semester', $this->option('semester'));
            }

            $kelasCount = $query->count();

            if ($kelasCount === 0) {
                $this->warn('Tidak ada Kelas Kuliah server untuk diproses.');
                $this->warn('Pastikan sudah menjalankan sync:kelas-kuliah-from-server terlebih dahulu.');
                return Command::SUCCESS;
            }

            $this->info("📦 Memproses peserta dari {$kelasCount} kelas kuliah...");
            $this->newLine();

            $bar = $this->output->createProgressBar($kelasCount);
            $bar->setFormat(' %current%/%max% [%bar%] %percent:3s%% | %elapsed:6s% | %message%');
            $bar->setMessage('Memulai...');
            $bar->start();

            // ─── 2. Chunk kelas kuliah untuk efisiensi memori ──
            $query->chunk($chunkSize, function ($kelasChunk) use ($akademikService, $batchSize, $bar, &$totalKelas, &$totalPeserta, &$createdCount, &$updatedCount, &$failedKelas, &$failedPeserta) {
                foreach ($kelasChunk as $kelas) {
                    $totalKelas++;
                    $bar->setMessage("Kelas: {$kelas->nama_kelas_kuliah}");

                    try {
                        [$created, $updated, $failed] = $this->syncPesertaForKelas(
                            $akademikService,
                            $kelas->id_kelas_kuliah,
                            $batchSize
                        );

                        $totalPeserta += $created + $updated;
                        $createdCount += $created;
                        $updatedCount += $updated;
                        $failedPeserta += $failed;

                    } catch (\Exception $e) {
                        $failedKelas++;
                        Log::error("Gagal sync peserta kelas [{$kelas->nama_kelas_kuliah}]: " . $e->getMessage());
                    }

                    $bar->advance();
                }
            });

            $bar->setMessage('Selesai!');
            $bar->finish();
            $this->newLine(2);

            // ─── 3. Summary ──────────────────────────────────────
            $duration = round(microtime(true) - $startTime, 2);

            $this->info('═══════════════════════════════════════════════════');
            $this->info("  Sinkronisasi selesai dalam {$duration} detik");
            $this->info('═══════════════════════════════════════════════════');
            $this->newLine();

            $this->table(
                ['Keterangan', 'Jumlah'],
                [
                    ['Kelas Kuliah Diproses', $totalKelas],
                    ['Kelas Gagal', $failedKelas],
                    ['Total Peserta Sinkron', $totalPeserta],
                    ['Baru (Created)', $createdCount],
                    ['Diperbarui (Updated)', $updatedCount],
                    ['Peserta Gagal', $failedPeserta],
                ]
            );

            return Command::SUCCESS;

        } catch (\Exception $e) {
            $this->newLine();
            $this->error("Terjadi kesalahan fatal: " . $e->getMessage());
            Log::error("Fatal SyncPesertaKelasKuliah Error: " . $e->getMessage());
            return Command::FAILURE;
        }
    }

    /**
     * Sync semua peserta untuk satu kelas kuliah.
     * Menggunakan batch API + upsert DB untuk performa.
     *
     * @return array [created, updated, failed]
     */
    private function syncPesertaForKelas(
        AkademikRefService $akademikService,
        string $idKelasKuliah,
        int $batchSize
    ): array {
        $created = 0;
        $updated = 0;
        $failed = 0;
        $offset = 0;
        $filter = "id_kelas_kuliah='{$idKelasKuliah}'";

        while (true) {
            $data = $akademikService->getPesertaKelasKuliah($filter, $batchSize, $offset);

            if (empty($data)) {
                break;
            }

            // Batch upsert untuk performa
            $upsertData = [];
            $now = now();

            foreach ($data as $item) {
                try {
                    $idRegistrasi = $item['id_registrasi_mahasiswa'] ?? null;

                    if (empty($idRegistrasi)) {
                        $failed++;
                        Log::warning("Peserta tanpa id_registrasi_mahasiswa di kelas {$idKelasKuliah}");
                        continue;
                    }

                    $upsertData[] = [
                        'id_kelas_kuliah' => $idKelasKuliah,
                        'id_registrasi_mahasiswa' => $idRegistrasi,
                        'nilai_akhir' => isset($item['nilai_akhir']) && $item['nilai_akhir'] !== '' ? (float) $item['nilai_akhir'] : null,
                        'nilai_huruf' => $item['nilai_huruf'] ?? null,
                        'nilai_indeks' => isset($item['nilai_indeks']) && $item['nilai_indeks'] !== '' ? (float) $item['nilai_indeks'] : null,
                        'sumber_data' => 'server',
                        'status_sinkronisasi' => PesertaKelasKuliah::STATUS_SYNCED,
                        'is_deleted_server' => false,
                        'last_synced_at' => $now,
                        'created_at' => $now,
                        'updated_at' => $now,
                    ];
                } catch (\Exception $e) {
                    $failed++;
                    $nama = $item['nama_mahasiswa'] ?? $item['id_registrasi_mahasiswa'] ?? 'unknown';
                    Log::error("Gagal mapping peserta [{$nama}]: " . $e->getMessage());
                }
            }

            // Upsert batch — update jika composite key sudah ada
            if (!empty($upsertData)) {
                $beforeCount = PesertaKelasKuliah::where('id_kelas_kuliah', $idKelasKuliah)->count();

                DB::table('peserta_kelas_kuliah')->upsert(
                    $upsertData,
                    ['id_kelas_kuliah', 'id_registrasi_mahasiswa'], // unique key
                    ['nilai_akhir', 'nilai_huruf', 'nilai_indeks', 'sumber_data', 'status_sinkronisasi', 'is_deleted_server', 'last_synced_at', 'updated_at']
                );

                $afterCount = PesertaKelasKuliah::where('id_kelas_kuliah', $idKelasKuliah)->count();
                $newRecords = $afterCount - $beforeCount;
                $created += $newRecords;
                $updated += (count($upsertData) - $failed - $newRecords);
            }

            // Halaman terakhir jika data < batch size
            if (count($data) < $batchSize) {
                break;
            }

            $offset += count($data);
        }

        return [$created, $updated, $failed];
    }
}

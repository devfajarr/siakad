<?php

namespace App\Console\Commands;

use App\Models\KelasKuliah;
use App\Models\PesertaKelasKuliah;
use App\Services\AkademikServices\AkademikRefService;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class SyncNilaiPerkuliahanFromServer extends Command
{
    protected $signature = 'sync:nilai-perkuliahan-from-server
        {--limit=100 : Limit data per batch API call}
        {--semester= : Filter berdasarkan id_semester (opsional)}
        {--kelas= : Sync hanya untuk id_kelas_kuliah tertentu (UUID)}
        {--chunk=50 : Jumlah kelas kuliah diproses per chunk DB}';

    protected $description = 'Sinkronisasi data Nilai Perkuliahan dari Neo Feeder Server (update ke peserta_kelas_kuliah)';

    public function handle(AkademikRefService $akademikService): int
    {
        $this->info('═══════════════════════════════════════════════════');
        $this->info('  Sync Nilai Perkuliahan dari Server');
        $this->info('═══════════════════════════════════════════════════');
        $this->newLine();

        $startTime = microtime(true);
        $batchSize = (int) $this->option('limit');
        $chunkSize = (int) $this->option('chunk');

        $totalKelas = 0;
        $totalNilai = 0;
        $updatedCount = 0;
        $skippedCount = 0;
        $failedKelas = 0;
        $failedNilai = 0;

        try {
            // ─── 1. Query kelas_kuliah yang sudah synced ────────
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
                return Command::SUCCESS;
            }

            $this->info("📦 Mengambil nilai dari {$kelasCount} kelas kuliah...");
            $this->newLine();

            $bar = $this->output->createProgressBar($kelasCount);
            $bar->setFormat(' %current%/%max% [%bar%] %percent:3s%% | %elapsed:6s% | %message%');
            $bar->setMessage('Memulai...');
            $bar->start();

            // ─── 2. Chunk kelas kuliah ──────────────────────────
            $query->chunk($chunkSize, function ($kelasChunk) use ($akademikService, $batchSize, $bar, &$totalKelas, &$totalNilai, &$updatedCount, &$skippedCount, &$failedKelas, &$failedNilai) {
                foreach ($kelasChunk as $kelas) {
                    $totalKelas++;
                    $bar->setMessage("Kelas: {$kelas->nama_kelas_kuliah}");

                    try {
                        [$updated, $skipped, $failed] = $this->syncNilaiForKelas(
                            $akademikService,
                            $kelas->id_kelas_kuliah,
                            $batchSize
                        );

                        $totalNilai += $updated + $skipped;
                        $updatedCount += $updated;
                        $skippedCount += $skipped;
                        $failedNilai += $failed;

                    } catch (\Exception $e) {
                        $failedKelas++;
                        Log::error("Gagal sync nilai kelas [{$kelas->nama_kelas_kuliah}]: " . $e->getMessage());
                    }

                    $bar->advance();
                }
            });

            $bar->setMessage('Selesai!');
            $bar->finish();
            $this->newLine(2);

            // ─── 3. Summary ─────────────────────────────────────
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
                    ['Nilai Diperbarui', $updatedCount],
                    ['Peserta Belum Tersedia (Skip)', $skippedCount],
                    ['Nilai Gagal', $failedNilai],
                ]
            );

            if ($skippedCount > 0) {
                $this->newLine();
                $this->warn("⚠ {$skippedCount} nilai di-skip karena peserta belum ada di lokal.");
                $this->warn('  Pastikan sync:peserta-kelas-kuliah-from-server sudah dijalankan.');
            }

            return Command::SUCCESS;

        } catch (\Exception $e) {
            $this->newLine();
            $this->error("Terjadi kesalahan fatal: " . $e->getMessage());
            Log::error("Fatal SyncNilaiPerkuliahan Error: " . $e->getMessage());
            return Command::FAILURE;
        }
    }

    /**
     * Sync nilai untuk satu kelas kuliah.
     * Update kolom nilai di peserta_kelas_kuliah yang sudah ada.
     *
     * @return array [updated, skipped, failed]
     */
    private function syncNilaiForKelas(
        AkademikRefService $akademikService,
        string $idKelasKuliah,
        int $batchSize
    ): array {
        $updated = 0;
        $skipped = 0;
        $failed = 0;
        $offset = 0;
        $filter = "id_kelas_kuliah='{$idKelasKuliah}'";

        while (true) {
            $data = $akademikService->getListNilaiPerkuliahanKelas($filter, $batchSize, $offset);

            if (empty($data)) {
                break;
            }

            // Batch update via single query per batch
            $upsertData = [];
            $now = now();

            foreach ($data as $item) {
                try {
                    $idRegistrasi = $item['id_registrasi_mahasiswa'] ?? null;

                    if (empty($idRegistrasi)) {
                        $failed++;
                        continue;
                    }

                    $upsertData[] = [
                        'id_kelas_kuliah' => $idKelasKuliah,
                        'id_registrasi_mahasiswa' => $idRegistrasi,
                        'nilai_angka' => isset($item['nilai_angka']) && $item['nilai_angka'] !== '' ? (float) $item['nilai_angka'] : null,
                        'nilai_akhir' => isset($item['nilai_akhir']) && $item['nilai_akhir'] !== '' ? (float) $item['nilai_akhir'] : null,
                        'nilai_huruf' => $item['nilai_huruf'] ?? null,
                        'nilai_indeks' => isset($item['nilai_indeks']) && $item['nilai_indeks'] !== '' ? (float) $item['nilai_indeks'] : null,
                        'last_synced_at' => $now,
                        'updated_at' => $now,
                        'created_at' => $now,
                        'sumber_data' => 'server',
                        'status_sinkronisasi' => PesertaKelasKuliah::STATUS_SYNCED,
                        'is_deleted_server' => false,
                    ];
                } catch (\Exception $e) {
                    $failed++;
                    Log::error("Gagal mapping nilai [{$idKelasKuliah}]: " . $e->getMessage());
                }
            }

            if (!empty($upsertData)) {
                $beforeCount = DB::table('peserta_kelas_kuliah')
                    ->where('id_kelas_kuliah', $idKelasKuliah)
                    ->whereNotNull('nilai_angka')
                    ->count();

                // Upsert: update nilai jika peserta sudah ada, insert jika belum
                DB::table('peserta_kelas_kuliah')->upsert(
                    $upsertData,
                    ['id_kelas_kuliah', 'id_registrasi_mahasiswa'],
                    ['nilai_angka', 'nilai_akhir', 'nilai_huruf', 'nilai_indeks', 'last_synced_at', 'updated_at']
                );

                $afterCount = DB::table('peserta_kelas_kuliah')
                    ->where('id_kelas_kuliah', $idKelasKuliah)
                    ->whereNotNull('nilai_angka')
                    ->count();

                $updated += ($afterCount - $beforeCount) + min($beforeCount, count($upsertData));
            }

            if (count($data) < $batchSize) {
                break;
            }

            $offset += count($data);
        }

        return [$updated, $skipped, $failed];
    }
}

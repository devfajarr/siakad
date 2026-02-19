<?php

namespace App\Console\Commands;

use App\Models\DosenPengajarKelasKuliah;
use App\Models\DosenPenugasan;
use App\Models\KelasKuliah;
use App\Services\AkademikServices\AkademikRefService;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class SyncDosenPengajarKelasKuliahFromServer extends Command
{
    protected $signature = 'sync:dosen-pengajar-kk-from-server
        {--limit=100 : Limit data per batch API call}
        {--semester= : Filter berdasarkan id_semester (opsional)}
        {--kelas= : Sync hanya untuk id_kelas_kuliah tertentu (UUID)}
        {--chunk=50 : Jumlah kelas kuliah diproses per chunk DB}';

    protected $description = 'Sinkronisasi data Dosen Pengajar Kelas Kuliah dari Neo Feeder Server';

    /**
     * Cache lookup id_registrasi_dosen → id_dosen lokal.
     * Dibangun sekali di awal untuk performa.
     */
    private array $registrasiDosenMap = [];

    public function handle(AkademikRefService $akademikService): int
    {
        $this->info('═══════════════════════════════════════════════════');
        $this->info('  Sync Dosen Pengajar Kelas Kuliah dari Server');
        $this->info('═══════════════════════════════════════════════════');
        $this->newLine();

        $startTime = microtime(true);
        $batchSize = (int) $this->option('limit');
        $chunkSize = (int) $this->option('chunk');

        // Counters
        $totalKelas = 0;
        $totalDosen = 0;
        $createdCount = 0;
        $updatedCount = 0;
        $failedKelas = 0;
        $failedDosen = 0;
        $unmatchedDosen = 0;

        try {
            // ─── 1. Build lookup cache registrasi → id_dosen lokal ──
            $this->info('🔗 Membangun cache registrasi dosen...');
            $this->registrasiDosenMap = DosenPenugasan::whereNotNull('external_id')
                ->pluck('id_dosen', 'external_id')
                ->toArray();
            $this->info('   ✓ ' . count($this->registrasiDosenMap) . ' registrasi dosen di-cache.');
            $this->newLine();

            // ─── 2. Query kelas_kuliah yang sudah synced ────────
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

            $this->info("📦 Memproses dosen pengajar dari {$kelasCount} kelas kuliah...");
            $this->newLine();

            $bar = $this->output->createProgressBar($kelasCount);
            $bar->setFormat(' %current%/%max% [%bar%] %percent:3s%% | %elapsed:6s% | %message%');
            $bar->setMessage('Memulai...');
            $bar->start();

            // ─── 3. Chunk kelas kuliah ──────────────────────────
            $query->chunk($chunkSize, function ($kelasChunk) use ($akademikService, $batchSize, $bar, &$totalKelas, &$totalDosen, &$createdCount, &$updatedCount, &$failedKelas, &$failedDosen, &$unmatchedDosen) {
                foreach ($kelasChunk as $kelas) {
                    $totalKelas++;
                    $bar->setMessage("Kelas: {$kelas->nama_kelas_kuliah}");

                    try {
                        [$created, $updated, $failed, $unmatched] = $this->syncDosenForKelas(
                            $akademikService,
                            $kelas->id_kelas_kuliah,
                            $batchSize
                        );

                        $totalDosen += $created + $updated;
                        $createdCount += $created;
                        $updatedCount += $updated;
                        $failedDosen += $failed;
                        $unmatchedDosen += $unmatched;

                    } catch (\Exception $e) {
                        $failedKelas++;
                        Log::error("Gagal sync dosen pengajar kelas [{$kelas->nama_kelas_kuliah}]: " . $e->getMessage());
                    }

                    $bar->advance();
                }
            });

            $bar->setMessage('Selesai!');
            $bar->finish();
            $this->newLine(2);

            // ─── 4. Summary ─────────────────────────────────────
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
                    ['Total Dosen Pengajar Sinkron', $totalDosen],
                    ['Baru (Created)', $createdCount],
                    ['Diperbarui (Updated)', $updatedCount],
                    ['Dosen Gagal', $failedDosen],
                    ['Dosen Tidak Cocok (Registrasi)', $unmatchedDosen],
                ]
            );

            if ($unmatchedDosen > 0) {
                $this->newLine();
                $this->warn("⚠ {$unmatchedDosen} dosen tidak bisa di-match ke data lokal.");
                $this->warn('  Pastikan sync:dosen-from-pusat sudah dijalankan terlebih dahulu.');
            }

            return Command::SUCCESS;

        } catch (\Exception $e) {
            $this->newLine();
            $this->error("Terjadi kesalahan fatal: " . $e->getMessage());
            Log::error("Fatal SyncDosenPengajarKK Error: " . $e->getMessage());
            return Command::FAILURE;
        }
    }

    /**
     * Sync dosen pengajar untuk satu kelas kuliah.
     *
     * @return array [created, updated, failed, unmatched]
     */
    private function syncDosenForKelas(
        AkademikRefService $akademikService,
        string $idKelasKuliah,
        int $batchSize
    ): array {
        $created = 0;
        $updated = 0;
        $failed = 0;
        $unmatched = 0;
        $offset = 0;
        $filter = "id_kelas_kuliah='{$idKelasKuliah}'";

        while (true) {
            $data = $akademikService->getDosenPengajarKelasKuliah($filter, $batchSize, $offset);

            if (empty($data)) {
                break;
            }

            foreach ($data as $item) {
                try {
                    $idRegistrasiDosen = $item['id_registrasi_dosen'] ?? null;
                    $idAktivitasMengajar = $item['id_aktivitas_mengajar'] ?? null;

                    if (empty($idRegistrasiDosen)) {
                        $failed++;
                        Log::warning("Dosen pengajar tanpa id_registrasi_dosen di kelas {$idKelasKuliah}");
                        continue;
                    }

                    // Resolve id_dosen lokal dari cache
                    $idDosenLokal = $this->registrasiDosenMap[$idRegistrasiDosen] ?? null;

                    if (is_null($idDosenLokal)) {
                        $unmatched++;
                        Log::warning("Registrasi dosen [{$idRegistrasiDosen}] tidak ditemukan di lokal, kelas [{$idKelasKuliah}]");
                        continue;
                    }

                    $values = [
                        'id_aktivitas_mengajar' => $idAktivitasMengajar,
                        'id_kelas_kuliah' => $idKelasKuliah,
                        'id_dosen' => $idDosenLokal,
                        'id_registrasi_dosen' => $idRegistrasiDosen,
                        'sks_substansi' => isset($item['sks_substansi']) ? (float) $item['sks_substansi'] : null,
                        'rencana_minggu_pertemuan' => isset($item['rencana_minggu_pertemuan']) ? (int) $item['rencana_minggu_pertemuan'] : null,
                        'realisasi_minggu_pertemuan' => isset($item['realisasi_minggu_pertemuan']) ? (int) $item['realisasi_minggu_pertemuan'] : null,
                        'substansi_pilar' => $item['substansi_pilar'] ?? null,
                        // Monitoring
                        'sumber_data' => 'server',
                        'status_sinkronisasi' => DosenPengajarKelasKuliah::STATUS_SYNCED,
                        'is_deleted_server' => false,
                        'last_synced_at' => now(),
                    ];

                    // Upsert per record (karena perlu resolve id_dosen per item)
                    $existing = DosenPengajarKelasKuliah::where('id_kelas_kuliah', $idKelasKuliah)
                        ->where('id_dosen', $idDosenLokal)
                        ->first();

                    if ($existing) {
                        $existing->update($values);
                        $updated++;
                    } else {
                        DosenPengajarKelasKuliah::create($values);
                        $created++;
                    }

                } catch (\Exception $e) {
                    $failed++;
                    $nama = $item['nama_dosen'] ?? $item['id_registrasi_dosen'] ?? 'unknown';
                    Log::error("Gagal sync dosen pengajar [{$nama}]: " . $e->getMessage());
                }
            }

            if (count($data) < $batchSize) {
                break;
            }

            $offset += count($data);
        }

        return [$created, $updated, $failed, $unmatched];
    }
}

<?php

namespace App\Console\Commands;

use App\Models\DosenPengajarKelasKuliah;
use App\Models\DosenPenugasan;
use App\Models\KelasKuliah;
use App\Services\AkademikServices\AkademikRefService;
use Illuminate\Console\Command;
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

    /**
     * Counter untuk tracking mapping issues.
     */
    private int $mappingDuplicatesCount = 0;

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
        $overwriteCount = 0;

        try {
            // ─── 1. Build lookup cache registrasi → id_dosen lokal dengan validasi ──
            $this->info('🔗 Membangun cache registrasi dosen dengan validasi...');
            $this->buildRegistrasiDosenMap();
            $this->info('   ✓ '.count($this->registrasiDosenMap).' registrasi dosen di-cache.');
            if ($this->mappingDuplicatesCount > 0) {
                $this->warn("   ⚠ Ditemukan {$this->mappingDuplicatesCount} duplikasi external_id (menggunakan mapping pertama).");
            }
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
            $query->chunk($chunkSize, function ($kelasChunk) use ($akademikService, $batchSize, $bar, &$totalKelas, &$totalDosen, &$createdCount, &$updatedCount, &$failedKelas, &$failedDosen, &$unmatchedDosen, &$overwriteCount) {
                foreach ($kelasChunk as $kelas) {
                    $totalKelas++;
                    $bar->setMessage("Kelas: {$kelas->nama_kelas_kuliah}");

                    try {
                        [$created, $updated, $failed, $unmatched, $overwrite] = $this->syncDosenForKelas(
                            $akademikService,
                            $kelas->id_kelas_kuliah,
                            $batchSize
                        );

                        $totalDosen += $created + $updated;
                        $createdCount += $created;
                        $updatedCount += $updated;
                        $failedDosen += $failed;
                        $unmatchedDosen += $unmatched;
                        $overwriteCount += $overwrite;

                    } catch (\Exception $e) {
                        $failedKelas++;
                        Log::error("Gagal sync dosen pengajar kelas [{$kelas->nama_kelas_kuliah}]: ".$e->getMessage());
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
                    ['Overwrite Terdeteksi', $overwriteCount],
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
            $this->error('Terjadi kesalahan fatal: '.$e->getMessage());
            Log::error('Fatal SyncDosenPengajarKK Error: '.$e->getMessage());

            return Command::FAILURE;
        }
    }

    /**
     * Build mapping registrasi dosen dengan validasi duplikasi.
     */
    private function buildRegistrasiDosenMap(): void
    {
        $this->registrasiDosenMap = [];
        $duplicates = [];

        DosenPenugasan::whereNotNull('external_id')
            ->get()
            ->each(function ($penugasan) use (&$duplicates) {
                $externalId = $penugasan->external_id;
                $idDosen = $penugasan->id_dosen;

                if (isset($this->registrasiDosenMap[$externalId])) {
                    // Duplikasi external_id untuk id_dosen berbeda
                    if ($this->registrasiDosenMap[$externalId] !== $idDosen) {
                        $duplicates[] = [
                            'external_id' => $externalId,
                            'existing_id_dosen' => $this->registrasiDosenMap[$externalId],
                            'new_id_dosen' => $idDosen,
                        ];
                        $this->mappingDuplicatesCount++;
                        // Gunakan mapping pertama yang ditemukan (skip yang baru)
                        Log::warning('Duplikasi external_id ditemukan, menggunakan mapping pertama', [
                            'external_id' => $externalId,
                            'existing_id_dosen' => $this->registrasiDosenMap[$externalId],
                            'skipped_id_dosen' => $idDosen,
                        ]);
                    }
                    // Jika sama, tidak perlu melakukan apa-apa
                } else {
                    $this->registrasiDosenMap[$externalId] = $idDosen;
                }
            });

        if (! empty($duplicates)) {
            Log::warning('Total duplikasi external_id ditemukan saat build mapping', [
                'count' => count($duplicates),
                'duplicates' => $duplicates,
            ]);
        }
    }

    /**
     * Sync dosen pengajar untuk satu kelas kuliah.
     *
     * @return array [created, updated, failed, unmatched, overwrite]
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
        $overwrite = 0;
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

                    // ─── Validasi id_registrasi_dosen ──────────────────────
                    if (empty($idRegistrasiDosen)) {
                        $failed++;
                        Log::warning("Dosen pengajar tanpa id_registrasi_dosen di kelas {$idKelasKuliah}", [
                            'id_kelas_kuliah' => $idKelasKuliah,
                            'item' => $item,
                        ]);

                        continue;
                    }

                    // ─── Validasi id_aktivitas_mengajar ───────────────────
                    if (empty($idAktivitasMengajar)) {
                        $failed++;
                        Log::warning("Dosen pengajar tanpa id_aktivitas_mengajar di kelas {$idKelasKuliah}", [
                            'id_kelas_kuliah' => $idKelasKuliah,
                            'id_registrasi_dosen' => $idRegistrasiDosen,
                        ]);

                        continue;
                    }

                    // ─── Validasi id_registrasi_dosen ada di mapping ────────
                    if (! isset($this->registrasiDosenMap[$idRegistrasiDosen])) {
                        $unmatched++;
                        Log::warning('Registrasi dosen tidak ditemukan di mapping lokal', [
                            'id_registrasi_dosen' => $idRegistrasiDosen,
                            'id_kelas_kuliah' => $idKelasKuliah,
                            'id_aktivitas_mengajar' => $idAktivitasMengajar,
                        ]);

                        continue;
                    }

                    // Resolve id_dosen lokal dari cache
                    $idDosenLokal = $this->registrasiDosenMap[$idRegistrasiDosen];

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

                    // ─── PERBAIKAN: Gunakan id_aktivitas_mengajar sebagai key ──
                    // Ini lebih reliable karena id_aktivitas_mengajar adalah UUID unik dari server
                    $existing = DosenPengajarKelasKuliah::where('id_aktivitas_mengajar', $idAktivitasMengajar)->first();

                    if ($existing) {
                        // Deteksi overwrite jika id_registrasi_dosen berbeda
                        if ($existing->id_registrasi_dosen !== $idRegistrasiDosen) {
                            $overwrite++;
                            Log::warning('Overwrite terdeteksi: id_registrasi_dosen berbeda', [
                                'id_aktivitas_mengajar' => $idAktivitasMengajar,
                                'old_id_registrasi_dosen' => $existing->id_registrasi_dosen,
                                'new_id_registrasi_dosen' => $idRegistrasiDosen,
                                'old_id_dosen' => $existing->id_dosen,
                                'new_id_dosen' => $idDosenLokal,
                                'id_kelas_kuliah' => $idKelasKuliah,
                            ]);
                        }

                        $existing->update($values);
                        $updated++;

                        Log::info('Dosen pengajar di-update', [
                            'id_aktivitas_mengajar' => $idAktivitasMengajar,
                            'id_registrasi_dosen' => $idRegistrasiDosen,
                            'id_dosen_lokal' => $idDosenLokal,
                            'id_kelas_kuliah' => $idKelasKuliah,
                            'action' => 'updated',
                        ]);
                    } else {
                        DosenPengajarKelasKuliah::create($values);
                        $created++;

                        Log::info('Dosen pengajar di-create', [
                            'id_aktivitas_mengajar' => $idAktivitasMengajar,
                            'id_registrasi_dosen' => $idRegistrasiDosen,
                            'id_dosen_lokal' => $idDosenLokal,
                            'id_kelas_kuliah' => $idKelasKuliah,
                            'action' => 'created',
                        ]);
                    }

                } catch (\Exception $e) {
                    $failed++;
                    $nama = $item['nama_dosen'] ?? $item['id_registrasi_dosen'] ?? 'unknown';
                    Log::error('Gagal sync dosen pengajar', [
                        'nama' => $nama,
                        'id_registrasi_dosen' => $idRegistrasiDosen ?? 'N/A',
                        'id_aktivitas_mengajar' => $idAktivitasMengajar ?? 'N/A',
                        'id_kelas_kuliah' => $idKelasKuliah,
                        'error' => $e->getMessage(),
                        'trace' => $e->getTraceAsString(),
                    ]);
                }
            }

            if (count($data) < $batchSize) {
                break;
            }

            $offset += count($data);
        }

        return [$created, $updated, $failed, $unmatched, $overwrite];
    }
}

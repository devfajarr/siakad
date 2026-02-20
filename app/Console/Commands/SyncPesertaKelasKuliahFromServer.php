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
        {--limit=100 : Limit data per batch API call}
        {--semester= : Filter berdasarkan id_semester (opsional)}
        {--kelas= : Sync hanya untuk id_kelas_kuliah tertentu (UUID)}
        {--chunk=50 : Jumlah kelas kuliah diproses per chunk DB}
        {--skip-nilai : Skip sinkronisasi nilai (hanya sync peserta)}';

    protected $description = 'Sinkronisasi data Peserta Kelas Kuliah (KRS) beserta Nilai dari Neo Feeder Server';

    public function handle(AkademikRefService $akademikService): int
    {
        $withNilai = !$this->option('skip-nilai');

        $this->info('═══════════════════════════════════════════════════');
        $this->info('  Sync Peserta Kelas Kuliah' . ($withNilai ? ' + Nilai' : '') . ' dari Server');
        $this->info('═══════════════════════════════════════════════════');
        $this->newLine();

        $startTime = microtime(true);
        $batchSize = (int) $this->option('limit');
        $chunkSize = (int) $this->option('chunk');

        // Counters
        $stats = [
            'totalKelas' => 0,
            'failedKelas' => 0,
            'pesertaCreated' => 0,
            'pesertaUpdated' => 0,
            'pesertaFailed' => 0,
            'nilaiUpdated' => 0,
            'nilaiFailed' => 0,
        ];

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

            $this->info("📦 Memproses peserta" . ($withNilai ? ' + nilai' : '') . " dari {$kelasCount} kelas kuliah...");
            $this->newLine();

            $bar = $this->output->createProgressBar($kelasCount);
            $bar->setFormat(' %current%/%max% [%bar%] %percent:3s%% | %elapsed:6s% | %message%');
            $bar->setMessage('Memulai...');
            $bar->start();

            // ─── 2. Chunk kelas kuliah untuk efisiensi memori ──
            $query->chunk($chunkSize, function ($kelasChunk) use ($akademikService, $batchSize, $bar, $withNilai, &$stats) {
                foreach ($kelasChunk as $kelas) {
                    $stats['totalKelas']++;
                    $bar->setMessage("Kelas: {$kelas->nama_kelas_kuliah}");

                    try {
                        // Step A: Sync peserta
                        [$created, $updated, $failed] = $this->syncPesertaForKelas(
                            $akademikService,
                            $kelas->id_kelas_kuliah,
                            $batchSize
                        );

                        $stats['pesertaCreated'] += $created;
                        $stats['pesertaUpdated'] += $updated;
                        $stats['pesertaFailed'] += $failed;

                        // Step B: Sync nilai (merge langsung setelah peserta)
                        if ($withNilai) {
                            [$nilaiUpdated, $nilaiFailed] = $this->syncNilaiForKelas(
                                $akademikService,
                                $kelas->id_kelas_kuliah,
                                $batchSize
                            );

                            $stats['nilaiUpdated'] += $nilaiUpdated;
                            $stats['nilaiFailed'] += $nilaiFailed;
                        }

                    } catch (\Exception $e) {
                        $stats['failedKelas']++;
                        Log::error("Gagal sync kelas [{$kelas->nama_kelas_kuliah}]: " . $e->getMessage());
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

            $rows = [
                ['Kelas Kuliah Diproses', $stats['totalKelas']],
                ['Kelas Gagal', $stats['failedKelas']],
                ['─── Peserta ───', ''],
                ['Peserta Baru (Created)', $stats['pesertaCreated']],
                ['Peserta Diperbarui (Updated)', $stats['pesertaUpdated']],
                ['Peserta Gagal', $stats['pesertaFailed']],
            ];

            if ($withNilai) {
                $rows[] = ['─── Nilai ───', ''];
                $rows[] = ['Nilai Diperbarui', $stats['nilaiUpdated']];
                $rows[] = ['Nilai Gagal', $stats['nilaiFailed']];
            }

            $this->table(['Keterangan', 'Jumlah'], $rows);

            return Command::SUCCESS;

        } catch (\Exception $e) {
            $this->newLine();
            $this->error("Terjadi kesalahan fatal: " . $e->getMessage());
            Log::error("Fatal SyncPesertaKelasKuliah Error: " . $e->getMessage());
            return Command::FAILURE;
        }
    }

    /**
     * Sync peserta untuk satu kelas kuliah via GetPesertaKelasKuliah.
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
                        'nilai_angka' => isset($item['nilai_angka']) && $item['nilai_angka'] !== '' ? (float) $item['nilai_angka'] : null,
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

            if (!empty($upsertData)) {
                $beforeCount = PesertaKelasKuliah::where('id_kelas_kuliah', $idKelasKuliah)->count();

                DB::table('peserta_kelas_kuliah')->upsert(
                    $upsertData,
                    ['id_kelas_kuliah', 'id_registrasi_mahasiswa'],
                    ['nilai_angka', 'nilai_akhir', 'nilai_huruf', 'nilai_indeks', 'sumber_data', 'status_sinkronisasi', 'is_deleted_server', 'last_synced_at', 'updated_at']
                );

                $afterCount = PesertaKelasKuliah::where('id_kelas_kuliah', $idKelasKuliah)->count();
                $newRecords = $afterCount - $beforeCount;
                $created += $newRecords;
                $updated += (count($upsertData) - $failed - $newRecords);
            }

            if (count($data) < $batchSize) {
                break;
            }

            $offset += count($data);
        }

        return [$created, $updated, $failed];
    }

    /**
     * Sync nilai untuk satu kelas kuliah via GetDetailNilaiPerkuliahanKelas.
     * Update kolom nilai di peserta_kelas_kuliah yang sudah ada.
     *
     * CATATAN:
     * - GetListNilaiPerkuliahanKelas → kelas-level summary (jumlah_mahasiswa, nama_kelas, etc.)
     * - GetDetailNilaiPerkuliahanKelas → per-student grades (id_registrasi_mahasiswa, nilai_angka, etc.)
     *
     * @return array [updated, failed]
     */
    private function syncNilaiForKelas(
        AkademikRefService $akademikService,
        string $idKelasKuliah,
        int $batchSize
    ): array {
        $updated = 0;
        $failed = 0;
        $filter = "id_kelas_kuliah='{$idKelasKuliah}'";

        try {
            $data = $akademikService->getDetailNilaiPerkuliahanKelas($filter);
        } catch (\Exception $e) {
            Log::warning("GetDetailNilaiPerkuliahanKelas gagal untuk kelas [{$idKelasKuliah}]: " . $e->getMessage());
            return [0, 1];
        }

        if (empty($data)) {
            return [0, 0];
        }

        $now = now();

        foreach ($data as $item) {
            try {
                $idRegistrasi = $item['id_registrasi_mahasiswa'] ?? null;

                if (empty($idRegistrasi)) {
                    continue;
                }

                $nilaiAngka = isset($item['nilai_angka']) && $item['nilai_angka'] !== '' ? (float) $item['nilai_angka'] : null;
                $nilaiHuruf = $item['nilai_huruf'] ?? null;
                $nilaiIndeks = isset($item['nilai_indeks']) && $item['nilai_indeks'] !== '' ? (float) $item['nilai_indeks'] : null;
                $nilaiAkhir = isset($item['nilai_akhir']) && $item['nilai_akhir'] !== '' ? (float) $item['nilai_akhir'] : null;

                // Skip jika semua nilai null
                if (is_null($nilaiAngka) && is_null($nilaiHuruf) && is_null($nilaiIndeks) && is_null($nilaiAkhir)) {
                    continue;
                }

                $affected = DB::table('peserta_kelas_kuliah')
                    ->where('id_kelas_kuliah', $idKelasKuliah)
                    ->where('id_registrasi_mahasiswa', $idRegistrasi)
                    ->update([
                        'nilai_angka' => $nilaiAngka,
                        'nilai_akhir' => $nilaiAkhir,
                        'nilai_huruf' => $nilaiHuruf,
                        'nilai_indeks' => $nilaiIndeks,
                        'last_synced_at' => $now,
                        'updated_at' => $now,
                    ]);

                if ($affected > 0) {
                    $updated++;
                }
            } catch (\Exception $e) {
                $failed++;
                Log::error("Gagal update nilai [{$idKelasKuliah}]: " . $e->getMessage());
            }
        }

        return [$updated, $failed];
    }
}


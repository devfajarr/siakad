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
        $chunkSize = (int) $this->option('chunk');

        $totalKelas = 0;
        $updatedCount = 0;
        $skippedCount = 0;
        $failedKelas = 0;

        try {
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
            $this->info("   (Menggunakan GetDetailNilaiPerkuliahanKelas per kelas)");
            $this->newLine();

            $bar = $this->output->createProgressBar($kelasCount);
            $bar->setFormat(' %current%/%max% [%bar%] %percent:3s%% | %elapsed:6s% | %message%');
            $bar->setMessage('Memulai...');
            $bar->start();

            $query->chunk($chunkSize, function ($kelasChunk) use ($akademikService, $bar, &$totalKelas, &$updatedCount, &$skippedCount, &$failedKelas) {
                foreach ($kelasChunk as $kelas) {
                    $totalKelas++;
                    $bar->setMessage("Kelas: {$kelas->nama_kelas_kuliah}");

                    try {
                        [$updated, $skipped] = $this->syncNilaiForKelas(
                            $akademikService,
                            $kelas->id_kelas_kuliah
                        );

                        $updatedCount += $updated;
                        $skippedCount += $skipped;

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
                    ['Nilai Mahasiswa Diperbarui', $updatedCount],
                    ['Mahasiswa Tanpa Nilai (Skip)', $skippedCount],
                ]
            );

            return Command::SUCCESS;

        } catch (\Exception $e) {
            $this->newLine();
            $this->error("Terjadi kesalahan fatal: " . $e->getMessage());
            Log::error("Fatal SyncNilaiPerkuliahan Error: " . $e->getMessage());
            return Command::FAILURE;
        }
    }

    /**
     * Sync nilai untuk satu kelas kuliah via GetDetailNilaiPerkuliahanKelas.
     *
     * GetListNilaiPerkuliahanKelas → kelas-level summary (jumlah_mahasiswa, nama_kelas, etc.)
     * GetDetailNilaiPerkuliahanKelas → per-student grades (id_registrasi_mahasiswa, nilai_angka, etc.)
     *
     * @return array [updated, skipped]
     */
    private function syncNilaiForKelas(
        AkademikRefService $akademikService,
        string $idKelasKuliah
    ): array {
        $updated = 0;
        $skipped = 0;
        $filter = "id_kelas_kuliah='{$idKelasKuliah}'";

        $data = $akademikService->getDetailNilaiPerkuliahanKelas($filter);

        if (empty($data)) {
            return [0, 0];
        }

        $now = now();

        foreach ($data as $item) {
            // Pastikan ini item per-mahasiswa (ada id_registrasi_mahasiswa)
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
                $skipped++;
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
        }

        return [$updated, $skipped];
    }
}

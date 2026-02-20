<?php

namespace App\Console\Commands;

use App\Models\DosenPengajarKelasKuliah;
use App\Models\DosenPenugasan;
use App\Models\KelasKuliah;
use App\Services\AkademikServices\AkademikRefService;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class InvestigateDosenPengajarBug extends Command
{
    protected $signature = 'investigate:dosen-pengajar-bug
        {--kelas= : ID kelas kuliah spesifik untuk investigasi}
        {--api-only : Hanya analisis API structure, tidak query database}
        {--compare : Bandingkan data API vs lokal untuk kelas tertentu}';

    protected $description = 'Investigasi bug data dosen pengajar yang berulang/tidak sesuai';

    public function handle(AkademikRefService $akademikService): int
    {
        $this->info('═══════════════════════════════════════════════════');
        $this->info('  INVESTIGASI BUG DOSEN PENGAJAR KELAS KULIAH');
        $this->info('═══════════════════════════════════════════════════');
        $this->newLine();

        try {
            // ─── 1. Analisis Struktur API ────────────────────────────
            $this->info('📋 [1/6] Analisis Struktur API menggunakan GetDictionary...');
            $this->analyzeApiStructure($akademikService);
            $this->newLine();

            if ($this->option('api-only')) {
                return Command::SUCCESS;
            }

            // ─── 2. Analisis Mapping Registrasi Dosen ──────────────
            $this->info('🔗 [2/6] Analisis Mapping id_registrasi_dosen → id_dosen...');
            $this->analyzeRegistrasiDosenMapping();
            $this->newLine();

            // ─── 3. Analisis Duplikasi di Database Lokal ───────────
            $this->info('🔍 [3/6] Analisis Duplikasi Data di Database Lokal...');
            $this->analyzeLocalDuplicates();
            $this->newLine();

            // ─── 4. Analisis Sync Logic ─────────────────────────────
            $this->info('⚙️  [4/6] Analisis Sync Logic...');
            $this->analyzeSyncLogic();
            $this->newLine();

            // ─── 5. Perbandingan Data API vs Lokal ──────────────────
            if ($this->option('compare') || $this->option('kelas')) {
                $this->info('📊 [5/6] Perbandingan Data API vs Lokal...');
                $this->compareApiVsLocal($akademikService);
                $this->newLine();
            }

            // ─── 6. Rekomendasi ──────────────────────────────────────
            $this->info('💡 [6/6] Rekomendasi Perbaikan...');
            $this->showRecommendations();
            $this->newLine();

        } catch (\Exception $e) {
            $this->error('Terjadi kesalahan: '.$e->getMessage());
            Log::error('InvestigateDosenPengajarBug Error: '.$e->getMessage());

            return Command::FAILURE;
        }

        return Command::SUCCESS;
    }

    /**
     * Analisis struktur API menggunakan GetDictionary.
     */
    private function analyzeApiStructure(AkademikRefService $akademikService): void
    {
        $functions = [
            'GetDosenPengajarKelasKuliah',
            'InsertDosenPengajarKelasKuliah',
            'UpdateDosenPengajarKelasKuliah',
            'DeleteDosenPengajarKelasKuliah',
            'GetListDosen',
            'DetailBiodataDosen',
            'GetListPenugasanDosen',
            'GetAktivitasMengajarDosen',
        ];

        foreach ($functions as $function) {
            try {
                $this->line("   → Analisis: {$function}");
                $dictionary = $akademikService->getDictionary($function);

                if (empty($dictionary)) {
                    $this->warn("      ⚠ Dictionary kosong untuk {$function}");

                    continue;
                }

                // Tampilkan struktur field
                if (isset($dictionary['data']) && is_array($dictionary['data'])) {
                    $fields = array_keys($dictionary['data'][0] ?? []);
                    $this->line('      Fields: '.implode(', ', $fields));

                    // Highlight field penting
                    $importantFields = ['id_aktivitas_mengajar', 'id_registrasi_dosen', 'id_dosen', 'id_kelas_kuliah'];
                    foreach ($importantFields as $field) {
                        if (in_array($field, $fields)) {
                            $this->line("      ✓ {$field} ditemukan");
                        }
                    }
                } else {
                    $this->line('      Response: '.json_encode($dictionary, JSON_PRETTY_PRINT));
                }

            } catch (\Exception $e) {
                $this->warn('      ⚠ Error: '.$e->getMessage());
            }
        }
    }

    /**
     * Analisis mapping id_registrasi_dosen ke id_dosen lokal.
     */
    private function analyzeRegistrasiDosenMapping(): void
    {
        // Build map seperti di sync command
        $registrasiDosenMap = DosenPenugasan::whereNotNull('external_id')
            ->pluck('id_dosen', 'external_id')
            ->toArray();

        $this->line('   Total mapping: '.count($registrasiDosenMap));

        // Cek duplikasi: multiple external_id untuk satu id_dosen
        $dosenToRegistrasi = [];
        foreach ($registrasiDosenMap as $externalId => $idDosen) {
            if (! isset($dosenToRegistrasi[$idDosen])) {
                $dosenToRegistrasi[$idDosen] = [];
            }
            $dosenToRegistrasi[$idDosen][] = $externalId;
        }

        $duplicates = array_filter($dosenToRegistrasi, fn ($regs) => count($regs) > 1);
        if (! empty($duplicates)) {
            $this->warn('   ⚠ Ditemukan '.count($duplicates).' id_dosen dengan multiple external_id:');
            foreach ($duplicates as $idDosen => $externalIds) {
                $this->line("      id_dosen {$idDosen}: ".implode(', ', $externalIds));
            }
        } else {
            $this->line('   ✓ Tidak ada duplikasi mapping');
        }

        // Cek duplikasi: satu external_id untuk multiple id_dosen (tidak mungkin dengan pluck, tapi cek)
        $registrasiCounts = array_count_values($registrasiDosenMap);
        $duplicateRegistrasi = array_filter($registrasiCounts, fn ($count) => $count > 1);
        if (! empty($duplicateRegistrasi)) {
            $this->warn('   ⚠ Ditemukan external_id yang ter-mapping ke multiple id_dosen');
        }

        // Cek null/empty external_id
        $nullExternalId = DosenPenugasan::whereNull('external_id')->orWhere('external_id', '')->count();
        if ($nullExternalId > 0) {
            $this->warn("   ⚠ Ditemukan {$nullExternalId} DosenPenugasan tanpa external_id");
        }
    }

    /**
     * Analisis duplikasi data di database lokal.
     */
    private function analyzeLocalDuplicates(): void
    {
        // Cek kelas dengan multiple dosen pengajar yang sama
        $duplicates = DB::table('dosen_pengajar_kelas_kuliah')
            ->select('id_kelas_kuliah', 'id_dosen', DB::raw('COUNT(*) as count'))
            ->groupBy('id_kelas_kuliah', 'id_dosen')
            ->having('count', '>', 1)
            ->get();

        if ($duplicates->isNotEmpty()) {
            $this->warn('   ⚠ Ditemukan duplikasi berdasarkan (id_kelas_kuliah, id_dosen):');
            foreach ($duplicates as $dup) {
                $this->line("      Kelas: {$dup->id_kelas_kuliah}, Dosen: {$dup->id_dosen}, Count: {$dup->cnt}");
            }
        } else {
            $this->line('   ✓ Tidak ada duplikasi berdasarkan (id_kelas_kuliah, id_dosen)');
        }

        // Cek kelas dengan id_dosen yang sama berulang
        $sameDosenPerKelas = DB::table('dosen_pengajar_kelas_kuliah')
            ->select('id_kelas_kuliah', DB::raw('COUNT(DISTINCT id_dosen) as unique_dosen'), DB::raw('COUNT(*) as total_records'))
            ->groupBy('id_kelas_kuliah')
            ->havingRaw('COUNT(*) > COUNT(DISTINCT id_dosen)')
            ->get();

        if ($sameDosenPerKelas->isNotEmpty()) {
            $this->warn('   ⚠ Ditemukan kelas dengan id_dosen berulang:');
            foreach ($sameDosenPerKelas as $item) {
                $this->line("      Kelas: {$item->id_kelas_kuliah}, Unique Dosen: {$item->unique_dosen}, Total Records: {$item->total_records}");
            }
        }

        // Cek penggunaan id_aktivitas_mengajar sebagai unique identifier
        $nullAktivitas = DB::table('dosen_pengajar_kelas_kuliah')
            ->whereNull('id_aktivitas_mengajar')
            ->count();

        if ($nullAktivitas > 0) {
            $this->warn("   ⚠ Ditemukan {$nullAktivitas} record tanpa id_aktivitas_mengajar");
        }

        $duplicateAktivitas = DB::table('dosen_pengajar_kelas_kuliah')
            ->select('id_aktivitas_mengajar', DB::raw('COUNT(*) as count'))
            ->whereNotNull('id_aktivitas_mengajar')
            ->groupBy('id_aktivitas_mengajar')
            ->having('count', '>', 1)
            ->get();

        if ($duplicateAktivitas->isNotEmpty()) {
            $this->warn('   ⚠ Ditemukan duplikasi id_aktivitas_mengajar:');
            foreach ($duplicateAktivitas as $dup) {
                $this->line("      id_aktivitas_mengajar: {$dup->id_aktivitas_mengajar}, Count: {$dup->count}");
            }
        } else {
            $this->line('   ✓ id_aktivitas_mengajar unik (jika tidak null)');
        }
    }

    /**
     * Analisis sync logic untuk menemukan potensi bug.
     */
    private function analyzeSyncLogic(): void
    {
        $this->line('   Analisis Sync Logic di SyncDosenPengajarKelasKuliahFromServer:');

        // Cek apakah sync menggunakan id_aktivitas_mengajar sebagai key
        $this->line('   → Sync menggunakan (id_kelas_kuliah, id_dosen) sebagai key untuk mencari existing record');
        $this->line('   → id_aktivitas_mengajar TIDAK digunakan sebagai unique identifier saat upsert');
        $this->warn('   ⚠ POTENSI BUG: Jika mapping id_registrasi_dosen → id_dosen salah,');
        $this->warn('      multiple dosen berbeda bisa ter-resolve ke id_dosen yang sama,');
        $this->warn('      menyebabkan overwrite record sebelumnya.');
    }

    /**
     * Bandingkan data API vs lokal untuk kelas tertentu.
     */
    private function compareApiVsLocal(AkademikRefService $akademikService): void
    {
        $idKelasKuliah = $this->option('kelas');

        if (! $idKelasKuliah) {
            // Ambil kelas pertama yang ada
            $kelas = KelasKuliah::where('sumber_data', 'server')
                ->where('is_deleted_server', false)
                ->first();

            if (! $kelas) {
                $this->warn('   Tidak ada kelas kuliah server untuk dibandingkan');

                return;
            }

            $idKelasKuliah = $kelas->id_kelas_kuliah;
            $this->line("   Menggunakan kelas: {$kelas->nama_kelas_kuliah} ({$idKelasKuliah})");
        }

        // Ambil data dari API
        $this->line('   Mengambil data dari API...');
        $apiData = $akademikService->getDosenPengajarKelasKuliah(
            "id_kelas_kuliah='{$idKelasKuliah}'",
            100,
            0
        );

        // Ambil data dari lokal
        $localData = DosenPengajarKelasKuliah::where('id_kelas_kuliah', $idKelasKuliah)
            ->get()
            ->toArray();

        $this->line('   Data dari API: '.count($apiData).' record');
        $this->line('   Data dari Lokal: '.count($localData).' record');

        if (count($apiData) !== count($localData)) {
            $this->warn('   ⚠ Jumlah record berbeda!');
        }

        // Bandingkan id_registrasi_dosen
        $apiRegistrasiIds = array_column($apiData, 'id_registrasi_dosen');
        $localRegistrasiIds = array_column($localData, 'id_registrasi_dosen');

        $this->line('   Unique id_registrasi_dosen dari API: '.count(array_unique($apiRegistrasiIds)));
        $this->line('   Unique id_registrasi_dosen dari Lokal: '.count(array_unique($localRegistrasiIds)));

        // Bandingkan id_dosen lokal
        $localDosenIds = array_column($localData, 'id_dosen');
        $uniqueLocalDosenIds = array_unique($localDosenIds);
        $this->line('   Unique id_dosen lokal: '.count($uniqueLocalDosenIds));

        if (count($uniqueLocalDosenIds) < count($localDosenIds)) {
            $this->warn('   ⚠ Ditemukan id_dosen yang berulang di lokal!');
            $dosenCounts = array_count_values($localDosenIds);
            $repeated = array_filter($dosenCounts, fn ($count) => $count > 1);
            foreach ($repeated as $idDosen => $count) {
                $this->line("      id_dosen {$idDosen}: muncul {$count} kali");
            }
        }

        // Tampilkan detail perbandingan
        $this->newLine();
        $this->line('   Detail Perbandingan:');
        $this->table(
            ['Source', 'id_aktivitas_mengajar', 'id_registrasi_dosen', 'id_dosen (lokal)', 'id_kelas_kuliah'],
            array_merge(
                array_map(fn ($item) => [
                    'API',
                    $item['id_aktivitas_mengajar'] ?? 'N/A',
                    $item['id_registrasi_dosen'] ?? 'N/A',
                    'N/A',
                    $item['id_kelas_kuliah'] ?? 'N/A',
                ], array_slice($apiData, 0, 10)),
                array_map(fn ($item) => [
                    'Lokal',
                    $item['id_aktivitas_mengajar'] ?? 'N/A',
                    $item['id_registrasi_dosen'] ?? 'N/A',
                    $item['id_dosen'] ?? 'N/A',
                    $item['id_kelas_kuliah'] ?? 'N/A',
                ], array_slice($localData, 0, 10))
            )
        );
    }

    /**
     * Tampilkan rekomendasi perbaikan.
     */
    private function showRecommendations(): void
    {
        $this->line('   📝 REKOMENDASI PERBAIKAN:');
        $this->newLine();

        $this->line('   1. Gunakan id_aktivitas_mengajar sebagai primary key untuk upsert');
        $this->line('      → id_aktivitas_mengajar adalah UUID unik dari server');
        $this->line('      → Lebih reliable daripada composite (id_kelas_kuliah, id_dosen)');
        $this->newLine();

        $this->line('   2. Perbaiki mapping registrasi dosen:');
        $this->line('      → Cek apakah ada multiple external_id untuk satu id_dosen');
        $this->line('      → Pastikan mapping konsisten dan tidak ada overwrite');
        $this->newLine();

        $this->line('   3. Tambahkan logging saat sync:');
        $this->line('      → Log setiap mapping id_registrasi_dosen → id_dosen');
        $this->line('      → Log setiap overwrite yang terjadi');
        $this->newLine();

        $this->line('   4. Validasi data sebelum sync:');
        $this->line('      → Pastikan id_registrasi_dosen dari API ada di DosenPenugasan');
        $this->line('      → Pastikan mapping tidak ambigu');
        $this->newLine();

        $this->line('   5. Pertimbangkan menggunakan id_aktivitas_mengajar sebagai unique constraint:');
        $this->line('      → Tambahkan unique index pada id_aktivitas_mengajar (jika belum ada)');
        $this->line("      → Gunakan untuk upsert: updateOrCreate(['id_aktivitas_mengajar' => ...])");
    }
}

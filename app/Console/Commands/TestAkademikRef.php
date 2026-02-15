<?php

namespace App\Console\Commands;

use App\Services\AkademikServices\AkademikRefService;
use Illuminate\Console\Command;

class TestAkademikRef extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'akademik:test-ref';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Test internal AkademikRefService methods for fetching reference data';

    /**
     * Execute the console command.
     */
    public function handle(AkademikRefService $service)
    {
        $this->info('Starting AkademikRefService Test...');

        try {
            // 1. Get Tahun Ajaran
            $this->info('Testing getTahunAjaran()...');
            $ta = $service->getTahunAjaran();
            $this->line('Raw Response: ' . json_encode(array_slice($ta, 0, 5), JSON_PRETTY_PRINT));
            $this->table(['ID', 'Nama', 'Aktif'], array_slice($ta, 0, 5));
            $this->info('Total Record: ' . count($ta));
            $this->newLine();

            // 2. Get Semester
            $this->info('Testing getSemester()...');
            $smt = $service->getSemester();
            $this->line('Raw Response: ' . json_encode(array_slice($smt, 0, 5), JSON_PRETTY_PRINT));
            $this->table(['ID', 'Nama', 'Semester', 'Aktif'], array_map(function ($item) {
                return [
                    $item['id_semester'] ?? '-',
                    $item['nama_semester'] ?? '-',
                    $item['semester'] ?? '-',
                    $item['a_periode_aktif'] ?? '-'
                ];
            }, array_slice($smt, 0, 5)));
            $this->info('Total Record: ' . count($smt));
            $this->newLine();

            // 3. Get Prodi
            $this->info('Testing getProdi()...');
            $prodi = $service->getProdi();
            $this->line('Raw Response: ' . json_encode(array_slice($prodi, 0, 5), JSON_PRETTY_PRINT));
            $this->table(['ID', 'Kode', 'Nama', 'Jenjang'], array_map(function ($item) {
                return [
                    $item['id_prodi'] ?? '-',
                    $item['kode_program_studi'] ?? '-',
                    $item['nama_program_studi'] ?? '-',
                    $item['nama_jenjang_pendidikan'] ?? '-'
                ];
            }, array_slice($prodi, 0, 5)));
            $this->info('Total Record: ' . count($prodi));
            $this->newLine();

            // 4. Get  Kurikulum (Limit 5)
            $this->info('Testing getKurikulum(limit=5)...');
            $kurikulum = $service->getKurikulum('', 5, 0);
            $this->line('Raw Response: ' . json_encode($kurikulum, JSON_PRETTY_PRINT));
            $this->table(['ID', 'Nama', 'SKS Lulus', 'SKS Wajib'], array_map(function ($item) {
                return [
                    $item['id_kurikulum'] ?? '-',
                    $item['nama_kurikulum'] ?? '-',
                    $item['jumlah_sks_lulus'] ?? '-',
                    $item['jumlah_sks_wajib'] ?? '-'
                ];
            }, $kurikulum));
            $this->newLine();

            // 5. Get Mata Kuliah (Limit 5)
            $this->info('Testing getMataKuliah(limit=5)...');
            $mk = $service->getMataKuliah('', 5, 0);
            $this->line('Raw Response: ' . json_encode($mk, JSON_PRETTY_PRINT));
            $this->table(['ID', 'Kode', 'Nama', 'SKS'], array_map(function ($item) {
                return [
                    $item['id_matkul'] ?? '-',
                    $item['kode_mata_kuliah'] ?? '-',
                    $item['nama_mata_kuliah'] ?? '-',
                    $item['sks_mata_kuliah'] ?? '-'
                ];
            }, $mk));
            $this->info('Total Record: ' . count($mk));
            $this->newLine();

            if (!empty($kurikulum)) {
                $firstKurikulumId = $kurikulum[0]['id_kurikulum'];
                $this->info("Testing getMatkulKurikulum (from kurikulum ID: {$firstKurikulumId})...");
                $matkulKur = $service->getMatkulKurikulum($firstKurikulumId, 5, 0);
                $this->line('Raw Response: ' . json_encode($matkulKur, JSON_PRETTY_PRINT));
                $this->table(['ID MK', 'Kode MK', 'Nama MK', 'SKS', 'Semester'], array_map(function ($item) {
                    return [
                        $item['id_matkul'] ?? '-',
                        $item['kode_mata_kuliah'] ?? '-',
                        $item['nama_mata_kuliah'] ?? '-',
                        $item['sks_mata_kuliah'] ?? '-',
                        $item['semester'] ?? '-'
                    ];
                }, $matkulKur));
                $this->info('Total Record: ' . count($matkulKur));
                $this->newLine();
            }

            $this->info('All tests completed successfully!');

        } catch (\Exception $e) {
            $this->error('Test Failed: ' . $e->getMessage());
            return Command::FAILURE;
        }

        return Command::SUCCESS;
    }
}

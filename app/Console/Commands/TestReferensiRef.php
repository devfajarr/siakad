<?php

namespace App\Console\Commands;

use App\Services\ReferensiServices\AdministratifRefService;
use App\Services\ReferensiServices\WilayahRefService;
use Illuminate\Console\Command;

class TestReferensiRef extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'referensi:test-ref';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Test Wilayah and Administratif Reference Services';

    /**
     * Execute the console command.
     */
    public function handle(WilayahRefService $wilayahService, AdministratifRefService $adminService, \App\Services\ReferensiServices\PribadiRefService $pribadiService)
    {
        $this->info('Starting Referensi Services Test...');

        try {
            // --- A. WILAYAH ---
            $this->info('--- GROUP WILAYAH ---');

            // 1. Get Wilayah (Limit 5)
            $this->info('Testing getWilayah(limit=5)...');
            $wilayah = $wilayahService->getWilayah('', 5, 0);
            $this->table(['ID', 'Nama', 'ID Level'], array_map(function ($item) {
                return [
                    $item['id_wilayah'] ?? '-',
                    $item['nama_wilayah'] ?? '-',
                    $item['id_level_wilayah'] ?? '-',
                ];
            }, $wilayah));
            $this->info('Total Record: ' . count($wilayah));
            $this->newLine();

            // 2. Get Negara (Limit 5)
            $this->info('Testing getNegara(limit=5)...');
            $negara = $wilayahService->getNegara('', 5, 0);
            $this->table(['ID', 'Nama'], array_map(function ($item) {
                return [
                    $item['id_negara'] ?? '-',
                    $item['nama_negara'] ?? '-',
                ];
            }, $negara));
            $this->info('Total Record: ' . count($negara));
            $this->newLine();

            // 3. Get Level Wilayah
            $this->info('Testing getLevelWilayah()...');
            $level = $wilayahService->getLevelWilayah();
            $this->table(['ID', 'Nama'], array_map(function ($item) {
                return [
                    $item['id_level_wilayah'] ?? '-',
                    $item['nama_level_wilayah'] ?? '-',
                ];
            }, $level));
            $this->info('Total Record: ' . count($level));
            $this->newLine();


            // --- B. ADMINISTRATIF ---
            $this->info('--- GROUP ADMINISTRATIF ---');

            // 1. Get Jenis Tinggal
            $this->info('Testing getJenisTinggal()...');
            $tinggal = $adminService->getJenisTinggal();
            $this->table(['ID', 'Nama'], array_map(function ($item) {
                return [
                    $item['id_jenis_tinggal'] ?? '-',
                    $item['nama_jenis_tinggal'] ?? '-',
                ];
            }, $tinggal));
            $this->info('Total Record: ' . count($tinggal));
            $this->newLine();

            // 2. Get Alat Transportasi
            $this->info('Testing getAlatTransportasi()...');
            $transport = $adminService->getAlatTransportasi();
            $this->table(['ID', 'Nama'], array_map(function ($item) {
                return [
                    $item['id_alat_transportasi'] ?? '-',
                    $item['nama_alat_transportasi'] ?? '-',
                ];
            }, $transport));
            $this->info('Total Record: ' . count($transport));
            $this->newLine();


            // --- C. PRIBADI & LATAR BELAKANG ---
            $this->info('--- GROUP PRIBADI & LATAR BELAKANG ---');

            // 1. Get Agama
            $this->info('Testing getAgama()...');
            $agama = $pribadiService->getAgama();
            $this->table(['ID', 'Nama'], array_map(function ($item) {
                return [
                    $item['id_agama'] ?? '-',
                    $item['nama_agama'] ?? '-',
                ];
            }, $agama));
            $this->info('Total Record: ' . count($agama));
            $this->newLine();

            // 2. Get Kebutuhan Khusus
            $this->info('Testing getKebutuhanKhusus()...');
            $kebutuhan = $pribadiService->getKebutuhanKhusus('', 5, 0);
            $this->table(['ID', 'Nama', 'Ket'], array_map(function ($item) {
                return [
                    $item['id_kebutuhan_khusus'] ?? '-',
                    $item['nama_kebutuhan_khusus'] ?? '-',
                    $item['keterangan'] ?? '-',
                ];
            }, $kebutuhan));
            $this->info('Total Record: ' . count($kebutuhan));
            $this->newLine();

            // 3. Get Pekerjaan (Limit 5)
            $this->info('Testing getPekerjaan(limit=5)...');
            $pekerjaan = $pribadiService->getPekerjaan('', 5, 0);
            $this->table(['ID', 'Nama'], array_map(function ($item) {
                return [
                    $item['id_pekerjaan'] ?? '-',
                    $item['nama_pekerjaan'] ?? '-',
                ];
            }, $pekerjaan));
            $this->info('Total Record: ' . count($pekerjaan));
            $this->newLine();

            // 4. Get Penghasilan
            $this->info('Testing getPenghasilan()...');
            $penghasilan = $pribadiService->getPenghasilan();
            $this->table(['ID', 'Nama'], array_map(function ($item) {
                return [
                    $item['id_penghasilan'] ?? '-',
                    $item['nama_penghasilan'] ?? '-',
                ];
            }, $penghasilan));
            $this->info('Total Record: ' . count($penghasilan));
            $this->newLine();

            // 5. Get Pembiayaan
            $this->info('Testing getPembiayaan()...');
            $pembiayaan = $pribadiService->getPembiayaan();
            $this->table(['ID', 'Nama'], array_map(function ($item) {
                return [
                    $item['id_pembiayaan'] ?? '-',
                    $item['nama_pembiayaan'] ?? '-',
                ];
            }, $pembiayaan));
            $this->info('Total Record: ' . count($pembiayaan));
            $this->newLine();

            $this->info('All tests completed successfully!');

        } catch (\Exception $e) {
            $this->error('Test Failed: ' . $e->getMessage());
            return Command::FAILURE;
        }

        return Command::SUCCESS;
    }
}

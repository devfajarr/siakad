<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Services\Feeder\Reference\ReferencePembiayaanService;
use App\Services\Feeder\Reference\ReferenceProgramStudiService;
use App\Services\Feeder\Reference\ReferenceProfilPTService;

class TestReferenceSync extends Command
{
    protected $signature = 'test:reference-sync';
    protected $description = 'Test auto-sync of reference data from Feeder API';

    public function handle(
        ReferencePembiayaanService $pembiayaanService,
        ReferenceProgramStudiService $prodiService,
        ReferenceProfilPTService $ptService,
    ): int {
        $this->info('Testing Pembiayaan sync...');
        $pembiayaan = $pembiayaanService->get();
        $this->info("  → Pembiayaan: {$pembiayaan->count()} records");

        $this->info('Testing Program Studi sync...');
        $prodi = $prodiService->get();
        $this->info("  → Program Studi: {$prodi->count()} records");

        $this->info('Testing Profil PT sync...');
        $pt = $ptService->get();
        $this->info("  → Profil PT: {$pt->count()} records");

        $this->newLine();
        $this->info('✅ All reference data synced successfully!');
        return Command::SUCCESS;
    }
}

<?php

namespace App\Console\Commands;

use App\Services\AkademikServices\AkademikRefService;
use Illuminate\Console\Command;

class DebugDosenDictionary extends Command
{
    protected $signature = 'debug:dosen-dictionary';

    protected $description = 'Debug Dosen & Penugasan Dictionary and Sample Data';

    public function handle(AkademikRefService $service)
    {
        $functions = [
            'GetDosenPengajarKelasKuliah',
            'GetListDosen',
            'DetailBiodataDosen',
            'GetListPenugasanDosen',
            'GetAktivitasMengajarDosen',
        ];

        $this->info('=== DICTIONARY ANALYSIS ===');

        foreach ($functions as $func) {
            $this->info("\nFunction: $func");
            try {
                $dict = $service->getDictionary($func);
                // Print key fields (column name, description, type)
                foreach ($dict as $key => $val) {
                    // The dictionary format usually implies keys as column names, or it returns an array of metadata
                    // We'll print a sample to understand
                    $this->line('- '.json_encode($key).' : '.json_encode($val));
                }
            } catch (\Exception $e) {
                $this->error("Error fetching dictionary for $func: ".$e->getMessage());
            }
        }

        $this->info("\n=== SAMPLE DATA ANALYSIS ===");

        // Get a sample class
        $sampleClassId = \App\Models\KelasKuliah::where('sumber_data', 'server')->value('id_kelas_kuliah');

        if ($sampleClassId) {
            $this->info("Sample Kelas ID: $sampleClassId");
            try {
                $data = $service->getDosenPengajarKelasKuliah("id_kelas_kuliah='$sampleClassId'", 10);
                $this->info('Data Count: '.count($data));
                foreach ($data as $item) {
                    $this->line(json_encode($item, JSON_PRETTY_PRINT));
                }
            } catch (\Exception $e) {
                $this->error('Error fetching sample data: '.$e->getMessage());
            }
        } else {
            $this->warn('No synced class found to test.');
        }

        return Command::SUCCESS;
    }
}

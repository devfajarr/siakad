<?php

namespace App\Console\Commands;

use App\Services\FeederUtilityService;
use Illuminate\Console\Command;

class InspectFeederFunction extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'feeder:dictionary {function : Nama fungsi Feeder yang ingin dicek, misal InsertRiwayatPendidikan}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Inspect the dictionary/structure of a Feeder function';

    /**
     * Execute the console command.
     */
    public function handle(FeederUtilityService $feederService)
    {
        $functionName = $this->argument('function');
        $this->info("Inspecting function: $functionName");

        try {
            $dictionary = $feederService->getDictionary($functionName);

            // Tampilkan hasil dalam format yang rapi
            if (empty($dictionary)) {
                $this->warn("Dictionary kosong atau fungsi tidak ditemukan.");
            } else {
                $this->info(json_encode($dictionary, JSON_PRETTY_PRINT));

                // Jika ingin menampilkan sebagai tabel (opsional, jika struktur datanya flat)
                // $headers = array_keys(reset($dictionary));
                // $this->table($headers, $dictionary);
            }

        } catch (\Exception $e) {
            $this->error("Error: " . $e->getMessage());
            return Command::FAILURE;
        }

        return Command::SUCCESS;
    }
}

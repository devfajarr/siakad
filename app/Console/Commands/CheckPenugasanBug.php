<?php

namespace App\Console\Commands;

use App\Services\Feeder\DosenFeederService;
use Illuminate\Console\Command;

class CheckPenugasanBug extends Command
{
    protected $signature = 'debug:check-penugasan-bug {id_dosen? : UUID Dosen from Feeder}';

    protected $description = 'Check if GetListPenugasanDosen returns incorrect data';

    public function handle(DosenFeederService $service)
    {
        $idDosen = $this->argument('id_dosen');

        if (! $idDosen) {
            // Pick a random dosen from DB that has external_id
            $dosen = \App\Models\Dosen::whereNotNull('external_id')->where('status_sinkronisasi', 'pusat')->first();
            if (! $dosen) {
                $this->error('No synced dosen found in DB.');

                return;
            }
            $idDosen = $dosen->external_id;
            $this->info("Using Dosen: {$dosen->nama} ({$idDosen})");
        }

        $this->info("Requesting Penugasan for ID: $idDosen");

        // This calls the service method which we suspect is buggy
        $data = $service->getListPenugasanDosen($idDosen);

        $this->info('Total Records: '.count($data));

        $otherDosenCount = 0;
        foreach ($data as $item) {
            if (isset($item['id_dosen']) && $item['id_dosen'] !== $idDosen) {
                $otherDosenCount++;
                if ($otherDosenCount <= 5) {
                    $this->warn('Found Penugasan for OTHER Dosen: '.$item['id_dosen'].' - '.($item['nama_dosen'] ?? 'No Name'));
                }
            }
        }

        if ($otherDosenCount > 0) {
            $this->error("BUG CONFIRMED: Found $otherDosenCount records belonging to other dosens!");
            $this->error("The API is likely ignoring the 'id_dosen' parameter and returning all/mixed data.");
        } else {
            $this->info('No data mismatch found (or API returned empty/correct data).');
            // Also check if data is empty, which might mean the param is invalid but API returns empty instead of all
            if (empty($data)) {
                $this->warn('Returned empty data. Parameter might be invalid.');
            }
        }
    }
}

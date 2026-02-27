<?php

namespace App\Console\Commands\Feeder;

use Illuminate\Console\Command;
use App\Services\Feeder\SyncSkalaNilaiService;

class SyncSkalaNilai extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'feeder:pull-skala-nilai';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Tarik data Skala Nilai Prodi dari Feeder PDDIKTI ke database lokal';

    /**
     * Execute the console command.
     */
    public function handle(SyncSkalaNilaiService $syncService)
    {
        $this->info('Memulai sinkronisasi Skala Nilai Prodi...');

        try {
            $count = $syncService->pull();
            $this->info("Berhasil mensinkronkan $count data skala nilai.");
        } catch (\Exception $e) {
            $this->error('Gagal sinkronisasi: ' . $e->getMessage());
        }
    }
}

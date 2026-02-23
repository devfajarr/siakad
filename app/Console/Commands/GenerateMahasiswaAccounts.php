<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;

class GenerateMahasiswaAccounts extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'mahasiswa:generate-accounts';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Generate user accounts for mahasiswas that do not have one';

    /**
     * Execute the console command.
     */
    public function handle(\App\Services\MahasiswaAccountGenerationService $service)
    {
        $mahasiswas = \App\Models\Mahasiswa::whereNull('user_id')->get();

        if ($mahasiswas->isEmpty()) {
            $this->info('Semua mahasiswa sudah memiliki akun.');
            return;
        }

        $this->info("Menemukan {$mahasiswas->count()} mahasiswa tanpa akun. Memulai generate...");

        $bar = $this->output->createProgressBar(count($mahasiswas));
        $bar->start();

        foreach ($mahasiswas as $mahasiswa) {
            try {
                $service->generate($mahasiswa);
            } catch (\Exception $e) {
                // Log error if needed, but continue the loop
                $this->error("\nGagal generate untuk ID {$mahasiswa->id}: " . $e->getMessage());
            }
            $bar->advance();
        }

        $bar->finish();
        $this->newLine();
        $this->info('Proses generate akun selesai.');
    }
}

<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;

class TestFeederConnection extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'feeder:test-connection';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Test connection to Neo Feeder by attempting to retrieve an authentication token';

    /**
     * Execute the console command.
     */
    public function handle(\App\Services\FeederAuthService $feederService)
    {
        $this->info('Testing connection to Neo Feeder...');
        $this->info('URL: ' . config('services.feeder.url'));
        $this->info('Username: ' . config('services.feeder.username'));

        try {
            $token = $feederService->getToken();
            $this->info('Connection successful!');
            $this->info('Token: ' . $token);

            // Check if token came from cache
            if (\Illuminate\Support\Facades\Cache::has('feeder_token')) {
                $this->comment('(Token retrieved from cache)');
            } else {
                $this->comment('(New token generated)');
            }

            return Command::SUCCESS;
        } catch (\Exception $e) {
            $this->error('Connection failed!');
            $this->error('Error: ' . $e->getMessage());
            return Command::FAILURE;
        }
    }
}

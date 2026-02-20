<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Services\NeoFeederService;
use App\Services\FeederAuthService;

class ProbeFeederAllPt extends Command
{
    protected $signature = 'feeder:probe-allpt';
    protected $description = 'Probe GetAllPt and GetAllProdi to check schema';

    public function handle(): int
    {
        $svc = app(NeoFeederService::class);

        // Try GetAllPt with limit 2
        $this->info('--- Probing GetAllPt (limit 2) ---');
        try {
            $ref = new \ReflectionMethod($svc, 'sendRequest');
            $ref->setAccessible(true);
            $data = $ref->invoke($svc, 'GetAllPt', ['limit' => 2, 'offset' => 0]);
            $this->line(json_encode($data, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
        } catch (\Exception $e) {
            $this->error('GetAllPt failed: ' . $e->getMessage());
        }

        $this->newLine();

        // Try GetAllProdi with limit 2
        $this->info('--- Probing GetAllProdi (limit 2) ---');
        try {
            $ref = new \ReflectionMethod($svc, 'sendRequest');
            $ref->setAccessible(true);
            $data = $ref->invoke($svc, 'GetAllProdi', ['limit' => 2, 'offset' => 0]);
            $this->line(json_encode($data, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
        } catch (\Exception $e) {
            $this->error('GetAllProdi failed: ' . $e->getMessage());
        }

        return Command::SUCCESS;
    }
}

<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Services\AkademikServices\AkademikRefService;

class DebugMatkulKurikulumList extends Command
{
    protected $signature = 'debug:matkul-kurikulum {id_kurikulum?}';
    protected $description = 'Debug: Inspect GetMatkulKurikulum & GetDictionary response fields';

    protected $akademikService;

    public function __construct(AkademikRefService $akademikService)
    {
        parent::__construct();
        $this->akademikService = $akademikService;
    }

    public function handle()
    {
        // Step 1: GetDictionary for GetMatkulKurikulum
        $this->info('=== GetDictionary: GetMatkulKurikulum ===');
        try {
            $dict = $this->akademikService->getDictionary('GetMatkulKurikulum');
            $this->line(json_encode($dict, JSON_PRETTY_PRINT));
        } catch (\Exception $e) {
            $this->error('GetDictionary Error: ' . $e->getMessage());
        }

        $this->newLine();

        // Step 2: GetDictionary for InsertMatkulKurikulum
        $this->info('=== GetDictionary: InsertMatkulKurikulum ===');
        try {
            $dict = $this->akademikService->getDictionary('InsertMatkulKurikulum');
            $this->line(json_encode($dict, JSON_PRETTY_PRINT));
        } catch (\Exception $e) {
            $this->error('InsertMatkulKurikulum Error: ' . $e->getMessage());
        }

        $this->newLine();

        // Step 3: GetDictionary for DeleteMatkulKurikulum
        $this->info('=== GetDictionary: DeleteMatkulKurikulum ===');
        try {
            $dict = $this->akademikService->getDictionary('DeleteMatkulKurikulum');
            $this->line(json_encode($dict, JSON_PRETTY_PRINT));
        } catch (\Exception $e) {
            $this->error('DeleteMatkulKurikulum Error: ' . $e->getMessage());
        }

        $this->newLine();

        // Step 4: Fetch sample data if id_kurikulum is provided
        $idKurikulum = $this->argument('id_kurikulum');
        if ($idKurikulum) {
            $this->info("=== GetMatkulKurikulum (id_kurikulum: {$idKurikulum}) - Sample 3 ===");
            try {
                $data = $this->akademikService->getMatkulKurikulum($idKurikulum, 3, 0);
                if (!empty($data)) {
                    $this->info('Fields: ' . implode(', ', array_keys($data[0])));
                    $this->newLine();
                    foreach ($data as $index => $item) {
                        $this->info("--- Record {$index} ---");
                        $this->line(json_encode($item, JSON_PRETTY_PRINT));
                    }
                } else {
                    $this->warn('No data returned for this kurikulum.');
                }
            } catch (\Exception $e) {
                $this->error('GetMatkulKurikulum Error: ' . $e->getMessage());
            }
        } else {
            $this->info('Tip: Pass an id_kurikulum argument to see sample data.');
            $this->info('Example: php artisan debug:matkul-kurikulum <UUID>');
        }

        return Command::SUCCESS;
    }
}

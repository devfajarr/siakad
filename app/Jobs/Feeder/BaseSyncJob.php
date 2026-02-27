<?php

namespace App\Jobs\Feeder;

use Illuminate\Bus\Batchable;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;
use Exception;

abstract class BaseSyncJob implements ShouldQueue
{
    use Batchable, Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected array $data;
    protected string $entityName;

    /**
     * Create a new job instance.
     */
    public function __construct(array $data)
    {
        $this->data = $data;
        $this->entityName = $this->getEntityName();
    }

    /**
     * Get the descriptive name of the entity for logging.
     */
    abstract protected function getEntityName(): string;

    /**
     * Logic to sync a single row.
     */
    abstract protected function syncRow(array $row): void;

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        if ($this->batch() && $this->batch()->cancelled()) {
            return;
        }

        foreach ($this->data as $item) {
            try {
                $this->syncRow($item);
            } catch (Exception $e) {
                Log::error("SYNC_ERROR: [{$this->entityName}] Gagal sinkronisasi data", [
                    'item' => $item,
                    'error' => $e->getMessage()
                ]);

                // We don't necessarily want to fail the whole job for one row error, 
                // but we should record it.
            }
        }
    }
}

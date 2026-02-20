<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Kurikulum;
use App\Services\AkademikServices\AkademikRefService;
use Illuminate\Support\Carbon;

class SyncKurikulumFromServer extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'sync:kurikulum-from-server {--limit= : Batasi jumlah data yang diambil (untuk testing)}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Sinkronisasi data Kurikulum dari Neo Feeder Server';

    /**
     * Service Akademik Reference
     */
    protected $akademikService;

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct(AkademikRefService $akademikService)
    {
        parent::__construct();
        $this->akademikService = $akademikService;
    }

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        $this->info('Mulai sinkronisasi Kurikulum dari Server...');

        try {
            // 1. Ambil Jumlah Data (Optimasi)
            $this->info('Mengambil informasi jumlah data...');

            // Filter optional: only aktif? for now all.
            $filter = '';

            $totalData = $this->akademikService->getCountKurikulum($filter);

            // Limit for testing
            if ($this->option('limit')) {
                $limitOption = (int) $this->option('limit');
                $totalData = min($totalData, $limitOption);
                $this->warn("Limit aktif: Hanya mengambil {$totalData} data.");
            }

            if ($totalData == 0) {
                $this->warn('Tidak ada data Kurikulum di server.');
                return Command::SUCCESS;
            }

            $this->info("Ditemukan {$totalData} data Kurikulum.");

            // 2. Batch Processing
            $batchSize = 100; // Optimal batch size
            $bar = $this->output->createProgressBar($totalData);
            $bar->start();

            $processed = 0;

            while ($processed < $totalData) {
                $limit = $batchSize;

                // Adjust limit for the last batch if using --limit option
                if ($this->option('limit') && ($processed + $batchSize > $totalData)) {
                    $limit = $totalData - $processed;
                }

                $items = $this->akademikService->getKurikulum($filter, $limit, $processed);

                if (empty($items)) {
                    break;
                }

                foreach ($items as $item) {
                    $this->syncItem($item);
                    $bar->advance();
                }

                $processed += count($items);

                // Safety break for limit option
                if ($this->option('limit') && $processed >= $totalData) {
                    break;
                }
            }

            $bar->finish();
            $this->newLine();
            $this->info('Sinkronisasi Kurikulum selesai!');

        } catch (\Exception $e) {
            $this->error('Terjadi kesalahan: ' . $e->getMessage());
            return Command::FAILURE;
        }

        return Command::SUCCESS;
    }

    /**
     * Sync single item to database
     * 
     * @param array $item Data dari API
     */
    protected function syncItem(array $item)
    {
        // Mapping Data Match 'InsertKurikulum' dictionary & 'GetKurikulum' response
        $data = [
            'id_kurikulum' => $item['id_kurikulum'],
            'nama_kurikulum' => $item['nama_kurikulum'],
            'id_prodi' => $item['id_prodi'],
            'id_semester' => $item['id_semester'],
            'jumlah_sks_lulus' => $item['jumlah_sks_lulus'],
            'jumlah_sks_wajib' => $item['jumlah_sks_wajib'],
            'jumlah_sks_pilihan' => $item['jumlah_sks_pilihan'],

            // Sync Meta
            'sumber_data' => 'server',
            'status_sinkronisasi' => Kurikulum::STATUS_SYNCED,
            'is_deleted_server' => false,
            'last_synced_at' => now(),
        ];

        // Update or Create based on id_kurikulum (Server Key)
        Kurikulum::updateOrCreate(
            ['id_kurikulum' => $data['id_kurikulum']],
            $data
        );
    }
}

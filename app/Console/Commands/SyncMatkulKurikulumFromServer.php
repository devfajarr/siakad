<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Kurikulum;
use App\Services\AkademikServices\AkademikRefService;
use Illuminate\Support\Facades\DB;

class SyncMatkulKurikulumFromServer extends Command
{
    protected $signature = 'sync:matkul-kurikulum-from-server
        {--limit= : Batasi jumlah kurikulum yang diproses (untuk testing)}
        {--id= : Sync hanya untuk kurikulum tertentu (UUID)}';

    protected $description = 'Sinkronisasi data Mata Kuliah Kurikulum dari Neo Feeder Server';

    protected $akademikService;

    public function __construct(AkademikRefService $akademikService)
    {
        parent::__construct();
        $this->akademikService = $akademikService;
    }

    public function handle()
    {
        $this->info('Mulai sinkronisasi Mata Kuliah Kurikulum dari Server...');

        try {
            // Get kurikulum records to sync
            $query = Kurikulum::where('sumber_data', 'server')
                ->where('is_deleted_server', false);

            if ($this->option('id')) {
                $query->where('id_kurikulum', $this->option('id'));
            }

            $kurikulums = $query->get();

            if ($this->option('limit')) {
                $kurikulums = $kurikulums->take((int) $this->option('limit'));
            }

            if ($kurikulums->isEmpty()) {
                $this->warn('Tidak ada kurikulum server untuk disinkronisasi.');
                return Command::SUCCESS;
            }

            $this->info("Memproses {$kurikulums->count()} kurikulum...");
            $bar = $this->output->createProgressBar($kurikulums->count());
            $bar->start();

            $totalMatkul = 0;

            foreach ($kurikulums as $kurikulum) {
                $count = $this->syncMatkulForKurikulum($kurikulum);
                $totalMatkul += $count;
                $bar->advance();
            }

            $bar->finish();
            $this->newLine();
            $this->info("Sinkronisasi selesai! Total {$totalMatkul} mata kuliah kurikulum diproses.");

        } catch (\Exception $e) {
            $this->error('Terjadi kesalahan: ' . $e->getMessage());
            return Command::FAILURE;
        }

        return Command::SUCCESS;
    }

    protected function syncMatkulForKurikulum(Kurikulum $kurikulum): int
    {
        $offset = 0;
        $batchSize = 100;
        $count = 0;

        while (true) {
            $items = $this->akademikService->getMatkulKurikulum(
                $kurikulum->id_kurikulum,
                $batchSize,
                $offset
            );

            if (empty($items)) {
                break;
            }

            foreach ($items as $item) {
                $this->syncItem($item);
                $count++;
            }

            $offset += count($items);

            // If we got fewer items than the batch size, we're done
            if (count($items) < $batchSize) {
                break;
            }
        }

        return $count;
    }

    protected function syncItem(array $item)
    {
        $exists = DB::table('matkul_kurikulums')
            ->where('id_kurikulum', $item['id_kurikulum'])
            ->where('id_matkul', $item['id_matkul'])
            ->exists();

        $data = [
            'semester' => (int) $item['semester'],
            'sks_mata_kuliah' => $item['sks_mata_kuliah'],
            'sks_tatap_muka' => $item['sks_tatap_muka'],
            'sks_praktek' => $item['sks_praktek'],
            'sks_praktek_lapangan' => $item['sks_praktek_lapangan'],
            'sks_simulasi' => $item['sks_simulasi'],
            'apakah_wajib' => (int) $item['apakah_wajib'],
            'sumber_data' => 'server',
            'status_sinkronisasi' => 'synced',
            'is_deleted_server' => false,
            'last_synced_at' => now(),
            'updated_at' => now(),
        ];

        if (!$exists) {
            $data['id_kurikulum'] = $item['id_kurikulum'];
            $data['id_matkul'] = $item['id_matkul'];
            $data['created_at'] = now();
            DB::table('matkul_kurikulums')->insert($data);
        } else {
            DB::table('matkul_kurikulums')
                ->where('id_kurikulum', $item['id_kurikulum'])
                ->where('id_matkul', $item['id_matkul'])
                ->update($data);
        }
    }
}

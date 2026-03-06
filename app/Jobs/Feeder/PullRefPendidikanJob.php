<?php

namespace App\Jobs\Feeder;

use App\Models\JalurPendaftaran;
use App\Models\JenisDaftar;
use App\Models\Semester;
use App\Models\ProgramStudi;
use App\Services\AkademikServices\AkademikRefService;
use App\Services\Feeder\Reference\ReferenceProfilPTService;
use Illuminate\Bus\Batchable;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Throwable;

class PullRefPendidikanJob implements ShouldQueue
{
    use Batchable, Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $tries = 3;
    public array $backoff = [5, 15, 30];
    public int $timeout = 120;

    public function handle(): void
    {
        if ($this->batch() && $this->batch()->cancelled()) {
            return;
        }

        $attempt = $this->attempts();
        Log::info("SYNC_JOB: [Ref.Pendidikan] Memulai sinkronisasi referensi pendidikan (percobaan ke-{$attempt})");

        try {
            DB::beginTransaction();

            $akademikService = app(AkademikRefService::class);

            // 1. Jenis Daftar
            $this->syncMapped(
                'Jenis Daftar',
                fn() => $akademikService->getJenisPendaftaran(),
                JenisDaftar::class,
                ['id_jenis_daftar' => 'id_jenis_daftar', 'nama_jenis_daftar' => 'nama_jenis_daftar']
            );

            // 2. Jalur Pendaftaran
            $this->syncMapped(
                'Jalur Pendaftaran',
                fn() => $akademikService->getJalurMasuk(),
                JalurPendaftaran::class,
                ['id_jalur_daftar' => 'id_jalur_masuk', 'nama_jalur_daftar' => 'nama_jalur_masuk']
            );

            // 3. Semester
            $this->syncMapped(
                'Semester',
                fn() => $akademikService->getSemester(),
                Semester::class,
                [
                    'id_semester' => 'id_semester',
                    'nama_semester' => 'nama_semester',
                    'id_tahun_ajaran' => 'id_tahun_ajaran',
                    'semester' => 'semester',
                    'a_periode_aktif' => 'a_periode_aktif',
                    'tanggal_mulai' => 'tanggal_mulai',
                    'tanggal_selesai' => 'tanggal_selesai',
                ]
            );

            DB::commit();

            // 4. Profil PT (dilakukan di luar transaksi karena punya transaksi sendiri)
            Log::info("SYNC_PULL: [Ref.Pendidikan] Menarik data Profil PT...");
            $profilPtService = app(ReferenceProfilPTService::class);
            $profilPtService->syncFromFeeder();

            // 5. Prodi Lokal
            $this->syncProdiLokal($akademikService);

            Log::info("SYNC_SUCCESS: [Ref.Pendidikan] Sinkronisasi referensi pendidikan selesai.");
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error("SYNC_ERROR: [Ref.Pendidikan] Gagal sinkronisasi", ['error' => $e->getMessage()]);
            throw $e;
        }
    }

    private function syncMapped(string $name, callable $fetcher, string $modelClass, array $mapping): void
    {
        Log::info("SYNC_PULL: [Ref.Pendidikan] Menarik data {$name}...");
        $data = $fetcher();
        $count = count($data);

        if ($count === 0) {
            Log::warning("SYNC_PULL: [Ref.Pendidikan] Tidak ada data untuk {$name}.");
            return;
        }

        $modelKeys = array_keys($mapping);
        $primaryKey = $modelKeys[0];
        $timestamp = now();

        $upsertData = [];
        foreach ($data as $item) {
            $record = [
                'created_at' => $timestamp,
                'updated_at' => $timestamp,
            ];
            foreach ($mapping as $modelKey => $apiKey) {
                if (isset($item[$apiKey])) {
                    $record[$modelKey] = $item[$apiKey];
                }
            }
            if (!isset($record[$primaryKey]))
                continue;
            $upsertData[] = $record;
        }

        if (!empty($upsertData)) {
            $modelClass::upsert($upsertData, [$primaryKey], array_merge($modelKeys, ['updated_at']));
        }

        Log::info("SYNC_PULL: [Ref.Pendidikan] {$name} selesai: {$count} records.");
    }

    private function syncProdiLokal(AkademikRefService $akademikService): void
    {
        Log::info("SYNC_PULL: [Ref.Pendidikan] Menarik data Prodi Lokal...");
        try {
            $data = $akademikService->getProdi();

            if (empty($data)) {
                Log::warning("SYNC_PULL: [Ref.Pendidikan] Tidak ada data Prodi Lokal.");
                return;
            }

            $timestamp = now();
            $upsertData = [];
            foreach ($data as $item) {
                if (empty($item['id_prodi']))
                    continue;
                $upsertData[] = [
                    'id_prodi' => $item['id_prodi'],
                    'kode_program_studi' => $item['kode_program_studi'] ?? null,
                    'nama_program_studi' => $item['nama_program_studi'] ?? null,
                    'status' => $item['status'] ?? null,
                    'id_jenjang_pendidikan' => $item['id_jenjang_pendidikan'] ?? null,
                    'nama_jenjang_pendidikan' => $item['nama_jenjang_pendidikan'] ?? null,
                    'created_at' => $timestamp,
                    'updated_at' => $timestamp,
                ];
            }

            if (!empty($upsertData)) {
                ProgramStudi::upsert($upsertData, ['id_prodi'], ['kode_program_studi', 'nama_program_studi', 'status', 'id_jenjang_pendidikan', 'nama_jenjang_pendidikan', 'updated_at']);
            }

            Log::info("SYNC_PULL: [Ref.Pendidikan] Prodi Lokal selesai: " . count($upsertData) . " records.");
        } catch (\Exception $e) {
            Log::warning("SYNC_PULL: [Ref.Pendidikan] Gagal sync Prodi Lokal: " . $e->getMessage());
        }
    }

    public function failed(Throwable $exception): void
    {
        Log::error("SYNC_FAILED: [Ref.Pendidikan] Job GAGAL PERMANEN setelah {$this->tries}x percobaan", [
            'error' => $exception->getMessage(),
        ]);
    }
}

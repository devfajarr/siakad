<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Services\Feeder\FeederSyncService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Bus;
use Illuminate\Support\Facades\Log;

class SyncManagerController extends Controller
{
    protected FeederSyncService $syncService;

    public function __construct(FeederSyncService $syncService)
    {
        $this->syncService = $syncService;
    }

    /**
     * Dashboard Sync Manager.
     */
    public function index()
    {
        $entities = [
            ['name' => 'Mahasiswa', 'icon' => 'users'],
            ['name' => 'RiwayatPendidikan', 'icon' => 'graduation-cap'],
            ['name' => 'MataKuliah', 'icon' => 'book'],
            ['name' => 'Kurikulum', 'icon' => 'list-alt'],
            ['name' => 'MatkulKurikulum', 'icon' => 'link'],
            ['name' => 'KelasKuliah', 'icon' => 'chalkboard-teacher'],
            ['name' => 'DosenPengajar', 'icon' => 'user-tie'],
            ['name' => 'PesertaKelas', 'icon' => 'user-graduate'],
            ['name' => 'Nilai', 'icon' => 'edit-box'],
        ];

        return view('admin.sync.index', compact('entities'));
    }

    /**
     * Dispatch sync for a specific entity.
     */
    public function dispatchSync(Request $request)
    {
        $request->validate([
            'entity' => 'required|string'
        ]);

        try {
            $batchId = $this->syncService->dispatchSync($request->entity);

            if ($batchId === 'empty') {
                return response()->json([
                    'status' => 'success',
                    'message' => 'Tidak ada data baru untuk disinkronkan.',
                    'batchId' => null
                ]);
            }

            return response()->json([
                'status' => 'success',
                'batchId' => $batchId
            ]);
        } catch (\Exception $e) {
            Log::error("SYSTEM_ERROR: Gagal dispatch sync", ['message' => $e->getMessage()]);
            return response()->json([
                'status' => 'error',
                'message' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Check batch progress.
     */
    public function checkBatch($batchId)
    {
        $batch = Bus::findBatch($batchId);

        if (!$batch) {
            return response()->json(['status' => 'not_found'], 404);
        }

        return response()->json([
            'id' => $batch->id,
            'progress' => $batch->progress(),
            'total_jobs' => $batch->totalJobs,
            'pending_jobs' => $batch->pendingJobs,
            'failed_jobs' => $batch->failedJobs,
            'finished' => $batch->finished(),
            'cancelled' => $batch->cancelled(),
        ]);
    }
}

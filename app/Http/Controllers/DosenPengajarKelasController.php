<?php

namespace App\Http\Controllers;

use App\Http\Requests\KelasDosen\StoreDosenPengajarRequest;
use App\Models\KelasDosen;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;
use Throwable;

class DosenPengajarKelasController extends Controller
{
    /**
     * Store a newly created Dosen Pengajar Kelas Kuliah.
     */
    public function store(StoreDosenPengajarRequest $request): RedirectResponse
    {
        $data = $request->validated();
        $createdKelasDosen = null;

        try {
            DB::transaction(function () use ($data, &$createdKelasDosen): void {
                $isDuplicate = KelasDosen::query()
                    ->where('kelas_kuliah_id', $data['kelas_kuliah_id'])
                    ->where('dosen_id', $data['dosen_id'])
                    ->lockForUpdate()
                    ->exists();

                if ($isDuplicate) {
                    throw ValidationException::withMessages([
                        'dosen_id' => 'Dosen sudah terdaftar pada kelas kuliah ini.',
                    ]);
                }

                $createdKelasDosen = KelasDosen::create([
                    'kelas_kuliah_id' => $data['kelas_kuliah_id'],
                    'dosen_id' => $data['dosen_id'],
                    'bobot_sks' => $data['bobot_sks'],
                    'jumlah_rencana_pertemuan' => $data['jumlah_rencana_pertemuan'],
                    'jumlah_realisasi_pertemuan' => $data['jumlah_realisasi_pertemuan'] ?? null,
                    'jenis_evaluasi' => $data['jenis_evaluasi'],
                    'status_sinkronisasi' => KelasDosen::STATUS_PENDING,
                    'sync_action' => KelasDosen::SYNC_ACTION_INSERT,
                    'is_from_server' => false,
                    'is_deleted_server' => false,
                    'error_message' => null,
                ]);
            });
        } catch (Throwable $exception) {
            if ($exception instanceof ValidationException) {
                throw $exception;
            }

            return redirect()
                ->back()
                ->withInput()
                ->with('error', 'Gagal menambahkan dosen pengajar: ' . $exception->getMessage());
        }

        return redirect()
            ->route('admin.kelas-kuliah.show', $createdKelasDosen->kelas_kuliah_id ?? 0)
            ->with('success', 'Dosen pengajar berhasil ditambahkan.');
    }

    /**
     * Remove the specified Dosen Pengajar Kelas Kuliah.
     *
     * Jika belum pernah sync ke server -> hard delete.
     * Jika sudah pernah sync -> mark deleted_local.
     */
    public function destroy(KelasDosen $kelasDosen): RedirectResponse
    {
        $kelasKuliahId = $kelasDosen->kelas_kuliah_id;

        try {
            DB::transaction(function () use ($kelasDosen): void {
                $record = KelasDosen::query()
                    ->lockForUpdate()
                    ->findOrFail($kelasDosen->id);

                $hasEverSynced = $record->feeder_id !== null
                    || $record->last_synced_at !== null
                    || in_array($record->status_sinkronisasi, [
                        KelasDosen::STATUS_SYNCED,
                        KelasDosen::STATUS_UPDATED_LOCAL,
                        KelasDosen::STATUS_DELETED_LOCAL,
                        KelasDosen::STATUS_FAILED,
                    ], true);

                if (! $hasEverSynced) {
                    $record->delete();

                    return;
                }

                $record->update([
                    'is_deleted_server' => true,
                    'status_sinkronisasi' => KelasDosen::STATUS_DELETED_LOCAL,
                    'sync_action' => KelasDosen::SYNC_ACTION_DELETE,
                    'error_message' => null,
                ]);
            });
        } catch (Throwable $exception) {
            return redirect()
                ->back()
                ->with('error', 'Gagal menghapus dosen pengajar: ' . $exception->getMessage());
        }

        return redirect()
            ->route('admin.kelas-kuliah.show', $kelasKuliahId)
            ->with('success', 'Dosen pengajar berhasil dihapus.');
    }
}

<?php

namespace App\Http\Controllers;

use App\Http\Requests\KelasDosen\StoreDosenPengajarRequest;
use App\Models\DosenPengajarKelasKuliah;
use App\Models\DosenPenugasan;
use App\Models\KelasKuliah;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;
use Throwable;

class DosenPengajarKelasController extends Controller
{
    /**
     * Store a newly created Dosen Pengajar.
     */
    public function store(StoreDosenPengajarRequest $request): RedirectResponse
    {
        $data = $request->validated();
        $createdRecord = null;

        try {
            DB::transaction(function () use ($data, &$createdRecord): void {
                $kelasKuliah = KelasKuliah::findOrFail($data['kelas_kuliah_id']);

                // Cek duplikasi
                $isDuplicate = DosenPengajarKelasKuliah::query()
                    ->where('id_kelas_kuliah', $kelasKuliah->id_kelas_kuliah)
                    ->where('id_dosen', $data['dosen_id'])
                    ->lockForUpdate()
                    ->exists();

                if ($isDuplicate) {
                    throw ValidationException::withMessages([
                        'dosen_id' => 'Dosen sudah terdaftar pada kelas kuliah ini.',
                    ]);
                }

                // Cari id_registrasi_dosen (UUID) dari penugasan (jika ada)
                $penugasan = DosenPenugasan::where('id_dosen', $data['dosen_id'])
                    ->whereHas('semester', function ($q) use ($kelasKuliah) {
                        $q->where('id_semester', $kelasKuliah->id_semester);
                    })
                    ->first();

                $createdRecord = DosenPengajarKelasKuliah::create([
                    'id_kelas_kuliah' => $kelasKuliah->id_kelas_kuliah,
                    'id_dosen' => $data['dosen_id'],
                    'id_registrasi_dosen' => $penugasan->external_id ?? null,
                    'bobot_sks' => $data['bobot_sks'],
                    'sks_substansi' => $data['bobot_sks'], // Sinkronkan sks_substansi
                    'rencana_minggu_pertemuan' => $data['jumlah_rencana_pertemuan'],
                    'realisasi_minggu_pertemuan' => $data['jumlah_realisasi_pertemuan'] ?? null,
                    'jenis_evaluasi' => $data['jenis_evaluasi'],
                    'id_dosen_alias_lokal' => $data['id_dosen_alias_lokal'] ?? null,
                    'dosen_alias' => $data['dosen_alias'] ?? null,
                    'status_sinkronisasi' => DosenPengajarKelasKuliah::STATUS_CREATED_LOCAL,
                    'sync_action' => 'insert',
                    'sumber_data' => 'lokal',
                    'is_deleted_server' => false,
                    'is_deleted_local' => false,
                    'is_local_change' => true,
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
            ->route('admin.kelas-kuliah.show', $data['kelas_kuliah_id'])
            ->with('success', 'Dosen pengajar berhasil ditambahkan.');
    }

    /**
     * Remove the specified Dosen Pengajar.
     */
    public function destroy(DosenPengajarKelasKuliah $dosenPengajar): RedirectResponse
    {
        // Cari ID lokal kelas_kuliah untuk redirect
        $kelasKuliah = KelasKuliah::where('id_kelas_kuliah', $dosenPengajar->id_kelas_kuliah)->first();
        $kelasId = $kelasKuliah ? $kelasKuliah->id : 0;

        try {
            DB::transaction(function () use ($dosenPengajar): void {
                $record = DosenPengajarKelasKuliah::query()
                    ->lockForUpdate()
                    ->findOrFail($dosenPengajar->id);

                $hasEverSynced = $record->id_aktivitas_mengajar !== null
                    || $record->last_synced_at !== null
                    || in_array($record->status_sinkronisasi, [
                        DosenPengajarKelasKuliah::STATUS_SYNCED,
                        DosenPengajarKelasKuliah::STATUS_UPDATED_LOCAL,
                        DosenPengajarKelasKuliah::STATUS_DELETED_LOCAL,
                    ], true);

                if (!$hasEverSynced) {
                    $record->delete();
                    return;
                }

                $record->update([
                    'is_deleted_local' => true,
                    'is_local_change' => true,
                    'status_sinkronisasi' => DosenPengajarKelasKuliah::STATUS_DELETED_LOCAL,
                    'sync_action' => 'delete',
                ]);
            });
        } catch (Throwable $exception) {
            return redirect()
                ->back()
                ->with('error', 'Gagal menghapus dosen pengajar: ' . $exception->getMessage());
        }

        return redirect()
            ->route('admin.kelas-kuliah.show', $kelasId)
            ->with('success', 'Dosen pengajar berhasil dihapus.');
    }
}

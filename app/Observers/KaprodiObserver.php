<?php

namespace App\Observers;

use App\Models\Kaprodi;

class KaprodiObserver
{
    /**
     * Handle the Kaprodi "created" event.
     */
    public function created(Kaprodi $kaprodi): void
    {
        $user = $kaprodi->dosen->akun;
        if ($user && !$user->hasRole('Kaprodi')) {
            $user->assignRole('Kaprodi');
            \Illuminate\Support\Facades\Log::info("SYNC_ROLE: User {$user->username} diberikan role 'Kaprodi' secara otomatis.");
        }
    }

    /**
     * Handle the Kaprodi "updated" event.
     */
    public function updated(Kaprodi $kaprodi): void
    {
        if ($kaprodi->isDirty('dosen_id')) {
            $oldDosenId = $kaprodi->getOriginal('dosen_id');

            // Handle Old Dosen (Revoke if no other positions)
            $oldDosen = \App\Models\Dosen::find($oldDosenId);
            if ($oldDosen && $oldDosen->akun) {
                $stillKaprodi = Kaprodi::where('dosen_id', $oldDosenId)->exists();
                if (!$stillKaprodi) {
                    $oldDosen->akun->removeRole('Kaprodi');
                    \Illuminate\Support\Facades\Log::info("SYNC_ROLE: Role 'Kaprodi' dicabut dari User {$oldDosen->akun->username} (Pergantian Jabatan).");
                }
            }

            // Handle New Dosen (Assign)
            $user = $kaprodi->dosen->akun;
            if ($user && !$user->hasRole('Kaprodi')) {
                $user->assignRole('Kaprodi');
                \Illuminate\Support\Facades\Log::info("SYNC_ROLE: User {$user->username} diberikan role 'Kaprodi' secara otomatis.");
            }
        }
    }

    /**
     * Handle the Kaprodi "deleted" event.
     */
    public function deleted(Kaprodi $kaprodi): void
    {
        $dosen = $kaprodi->dosen;
        if ($dosen && $dosen->akun) {
            $stillKaprodi = Kaprodi::where('dosen_id', $dosen->id)->exists();
            if (!$stillKaprodi) {
                $dosen->akun->removeRole('Kaprodi');
                \Illuminate\Support\Facades\Log::info("SYNC_ROLE: Role 'Kaprodi' dicabut dari User {$dosen->akun->username} (Jabatan dihapus).");
            }
        }
    }
}

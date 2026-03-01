<?php

namespace App\Observers;

use App\Models\Bpmi;

class BpmiObserver
{
    /**
     * Handle the Bpmi "created" event.
     */
    public function created(Bpmi $bpmi): void
    {
        // Panggil helper pada model Dosen untuk memastikan / membuat User
        $user = $bpmi->dosen->generateUserIfNotExists();

        if ($user && !$user->hasRole('bpmi')) {
            $user->assignRole('bpmi');
            \Illuminate\Support\Facades\Log::info("SYNC_ROLE: User {$user->username} diberikan role 'bpmi' karena jabatan.");
        }
    }

    /**
     * Handle the Bpmi "updated" event.
     */
    public function updated(Bpmi $bpmi): void
    {
        if ($bpmi->isDirty('id_dosen')) {
            $oldDosenId = $bpmi->getOriginal('id_dosen');

            // Handle Old Dosen (Revoke if no other BPMI positions)
            $oldDosen = \App\Models\Dosen::find($oldDosenId);
            if ($oldDosen && $oldDosen->akun) {
                $stillBpmi = Bpmi::where('id_dosen', $oldDosenId)->exists();
                if (!$stillBpmi) {
                    $oldDosen->akun->removeRole('bpmi');
                    \Illuminate\Support\Facades\Log::info("SYNC_ROLE: Role 'bpmi' dicabut dari User {$oldDosen->akun->username} (Pergantian Jabatan).");
                }
            }

            // Handle New Dosen (Assign & Auto Generate User)
            $user = $bpmi->dosen->generateUserIfNotExists();

            if ($user && !$user->hasRole('bpmi')) {
                $user->assignRole('bpmi');
                \Illuminate\Support\Facades\Log::info("SYNC_ROLE: User {$user->username} diberikan role 'bpmi' akibat pergantian jabatan.");
            }
        }
    }

    /**
     * Handle the Bpmi "deleted" event.
     */
    public function deleted(Bpmi $bpmi): void
    {
        $dosen = $bpmi->dosen;
        if ($dosen && $dosen->akun) {
            $stillBpmi = Bpmi::where('id_dosen', $dosen->id)->exists();
            if (!$stillBpmi) {
                $dosen->akun->removeRole('bpmi');
                \Illuminate\Support\Facades\Log::info("SYNC_ROLE: Role 'bpmi' dicabut dari User {$dosen->akun->username} (Jabatan dihapus).");
            }
        }
    }
}

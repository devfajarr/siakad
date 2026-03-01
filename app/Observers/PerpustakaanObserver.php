<?php

namespace App\Observers;

use App\Models\Perpustakaan;
use Spatie\Permission\Models\Role;
use Illuminate\Support\Facades\Log;

class PerpustakaanObserver
{
    private function handleRole(Perpustakaan $model, $isAssign = true)
    {
        $user = null;
        if ($model->id_dosen) {
            $user = $model->dosen->generateUserIfNotExists();
        } elseif ($model->id_pegawai) {
            $user = $model->pegawai->user ?? null;
        }

        if ($user) {
            Role::firstOrCreate(['name' => 'Perpustakaan']);
            if ($isAssign && $model->is_active) {
                if (!$user->hasRole('Perpustakaan')) {
                    $user->assignRole('Perpustakaan');
                    Log::info("SYNC_ROLE: User {$user->username} diberikan role 'Perpustakaan'.");
                }
            } else {
                $user->removeRole('Perpustakaan');
                Log::info("SYNC_ROLE: Role 'Perpustakaan' dicabut dari User {$user->username}.");
            }
        }
    }

    public function created(Perpustakaan $perpustakaan): void
    {
        $this->handleRole($perpustakaan, true);
    }

    public function updated(Perpustakaan $perpustakaan): void
    {
        if ($perpustakaan->isDirty('id_dosen') || $perpustakaan->isDirty('id_pegawai') || $perpustakaan->isDirty('is_active')) {
            $oldModel = new Perpustakaan($perpustakaan->getOriginal());
            $this->handleRole($oldModel, false);
            $this->handleRole($perpustakaan, true);
        }
    }

    public function deleted(Perpustakaan $perpustakaan): void
    {
        $this->handleRole($perpustakaan, false);
    }
}

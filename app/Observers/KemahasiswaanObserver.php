<?php

namespace App\Observers;

use App\Models\Kemahasiswaan;
use Spatie\Permission\Models\Role;
use Illuminate\Support\Facades\Log;

class KemahasiswaanObserver
{
    private function handleRole(Kemahasiswaan $model, $isAssign = true)
    {
        $user = null;
        if ($model->id_dosen) {
            $user = $model->dosen->generateUserIfNotExists();
        } elseif ($model->id_pegawai) {
            $user = $model->pegawai->user ?? null;
        }

        if ($user) {
            Role::firstOrCreate(['name' => 'Kemahasiswaan']);
            if ($isAssign && $model->is_active) {
                if (!$user->hasRole('Kemahasiswaan')) {
                    $user->assignRole('Kemahasiswaan');
                    Log::info("SYNC_ROLE: User {$user->username} diberikan role 'Kemahasiswaan'.");
                }
            } else {
                $user->removeRole('Kemahasiswaan');
                Log::info("SYNC_ROLE: Role 'Kemahasiswaan' dicabut dari User {$user->username}.");
            }
        }
    }

    public function created(Kemahasiswaan $kemahasiswaan): void
    {
        $this->handleRole($kemahasiswaan, true);
    }

    public function updated(Kemahasiswaan $kemahasiswaan): void
    {
        if ($kemahasiswaan->isDirty('id_dosen') || $kemahasiswaan->isDirty('id_pegawai') || $kemahasiswaan->isDirty('is_active')) {
            $oldModel = new Kemahasiswaan($kemahasiswaan->getOriginal());
            $this->handleRole($oldModel, false);
            $this->handleRole($kemahasiswaan, true);
        }
    }

    public function deleted(Kemahasiswaan $kemahasiswaan): void
    {
        $this->handleRole($kemahasiswaan, false);
    }
}

<?php

namespace App\Observers;

use App\Models\Sarpras;
use Spatie\Permission\Models\Role;
use Illuminate\Support\Facades\Log;

class SarprasObserver
{
    private function handleRole(Sarpras $model, $isAssign = true)
    {
        $user = null;
        if ($model->id_dosen) {
            $user = $model->dosen->generateUserIfNotExists();
        } elseif ($model->id_pegawai) {
            $user = $model->pegawai->user ?? null;
        }

        if ($user) {
            Role::firstOrCreate(['name' => 'Sarpras']);
            if ($isAssign && $model->is_active) {
                if (!$user->hasRole('Sarpras')) {
                    $user->assignRole('Sarpras');
                    Log::info("SYNC_ROLE: User {$user->username} diberikan role 'Sarpras'.");
                }
            } else {
                $user->removeRole('Sarpras');
                Log::info("SYNC_ROLE: Role 'Sarpras' dicabut dari User {$user->username}.");
            }
        }
    }

    public function created(Sarpras $sarpras): void
    {
        $this->handleRole($sarpras, true);
    }

    public function updated(Sarpras $sarpras): void
    {
        if ($sarpras->isDirty('id_dosen') || $sarpras->isDirty('id_pegawai') || $sarpras->isDirty('is_active')) {
            // Revoke from original if changed
            $oldModel = new Sarpras($sarpras->getOriginal());
            $this->handleRole($oldModel, false);

            // Assign to new
            $this->handleRole($sarpras, true);
        }
    }

    public function deleted(Sarpras $sarpras): void
    {
        $this->handleRole($sarpras, false);
    }
}

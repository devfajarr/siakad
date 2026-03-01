<?php

namespace App\Observers;

use App\Models\Personalia;
use Spatie\Permission\Models\Role;
use Illuminate\Support\Facades\Log;

class PersonaliaObserver
{
    private function handleRole(Personalia $model, $isAssign = true)
    {
        $user = null;
        if ($model->id_dosen) {
            $user = $model->dosen->generateUserIfNotExists();
        } elseif ($model->id_pegawai) {
            $user = $model->pegawai->user ?? null;
        }

        if ($user) {
            Role::firstOrCreate(['name' => 'Personalia']);
            if ($isAssign && $model->is_active) {
                if (!$user->hasRole('Personalia')) {
                    $user->assignRole('Personalia');
                    Log::info("SYNC_ROLE: User {$user->username} diberikan role 'Personalia'.");
                }
            } else {
                $user->removeRole('Personalia');
                Log::info("SYNC_ROLE: Role 'Personalia' dicabut dari User {$user->username}.");
            }
        }
    }

    public function created(Personalia $personalia): void
    {
        $this->handleRole($personalia, true);
    }

    public function updated(Personalia $personalia): void
    {
        if ($personalia->isDirty('id_dosen') || $personalia->isDirty('id_pegawai') || $personalia->isDirty('is_active')) {
            $oldModel = new Personalia($personalia->getOriginal());
            $this->handleRole($oldModel, false);
            $this->handleRole($personalia, true);
        }
    }

    public function deleted(Personalia $personalia): void
    {
        $this->handleRole($personalia, false);
    }
}

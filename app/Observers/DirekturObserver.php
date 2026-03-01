<?php

namespace App\Observers;

use App\Models\Direktur;
use Spatie\Permission\Models\Role;
use Illuminate\Support\Facades\Log;

class DirekturObserver
{
    private function handleRole(Direktur $model, $isAssign = true)
    {
        $user = null;
        if ($model->id_dosen) {
            $user = $model->dosen->generateUserIfNotExists();
        }

        if ($user) {
            Role::firstOrCreate(['name' => 'Direktur']);
            if ($isAssign && $model->is_active) {
                if (!$user->hasRole('Direktur')) {
                    $user->assignRole('Direktur');
                    Log::info("SYNC_ROLE: User {$user->username} diberikan role 'Direktur'.");
                }
            } else {
                $user->removeRole('Direktur');
                Log::info("SYNC_ROLE: Role 'Direktur' dicabut dari User {$user->username}.");
            }
        }
    }

    public function created(Direktur $direktur): void
    {
        $this->handleRole($direktur, true);
    }

    public function updated(Direktur $direktur): void
    {
        if ($direktur->isDirty('id_dosen') || $direktur->isDirty('is_active')) {
            $oldModel = new Direktur($direktur->getOriginal());
            $this->handleRole($oldModel, false);
            $this->handleRole($direktur, true);
        }
    }

    public function deleted(Direktur $direktur): void
    {
        $this->handleRole($direktur, false);
    }
}

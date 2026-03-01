<?php

namespace App\Observers;

use App\Models\WakilDirektur;
use Spatie\Permission\Models\Role;
use Illuminate\Support\Facades\Log;

class WakilDirekturObserver
{
    private function handleRole(WakilDirektur $model, $isAssign = true)
    {
        $user = null;
        if ($model->id_dosen) {
            $user = $model->dosen->generateUserIfNotExists();
        }

        if ($user) {
            $roleName = 'Wakil Direktur';
            Role::firstOrCreate(['name' => $roleName]);
            if ($isAssign && $model->is_active) {
                if (!$user->hasRole($roleName)) {
                    $user->assignRole($roleName);
                    Log::info("SYNC_ROLE: User {$user->username} diberikan role '{$roleName}'.");
                }
            } else {
                $user->removeRole($roleName);
                Log::info("SYNC_ROLE: Role '{$roleName}' dicabut dari User {$user->username}.");
            }
        }
    }

    public function created(WakilDirektur $wakilDirektur): void
    {
        $this->handleRole($wakilDirektur, true);
    }

    public function updated(WakilDirektur $wakilDirektur): void
    {
        if ($wakilDirektur->isDirty('id_dosen') || $wakilDirektur->isDirty('is_active')) {
            $oldModel = new WakilDirektur($wakilDirektur->getOriginal());
            $this->handleRole($oldModel, false);
            $this->handleRole($wakilDirektur, true);
        }
    }

    public function deleted(WakilDirektur $wakilDirektur): void
    {
        $this->handleRole($wakilDirektur, false);
    }
}

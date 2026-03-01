<?php

namespace App\Observers;

use App\Models\PembimbingAkademik;
use Spatie\Permission\Models\Role;
use Illuminate\Support\Facades\Log;

class PembimbingAkademikObserver
{
    private function handleRole(PembimbingAkademik $pembimbingAkademik)
    {
        try {
            if (!$pembimbingAkademik->dosen || !$pembimbingAkademik->dosen->user) {
                return;
            }

            $user = $pembimbingAkademik->dosen->user;

            $roleName = 'pembimbing_akademik';
            $role = Role::firstOrCreate(['name' => $roleName]);

            $isStillPa = PembimbingAkademik::where('id_dosen', $pembimbingAkademik->id_dosen)->exists();

            if ($isStillPa) {
                if (!$user->hasRole($roleName)) {
                    $user->assignRole($role);
                    Log::info("SYSTEM_LOG: Role {$roleName} diberikan kepada User ID {$user->id}");
                }
            } else {
                if ($user->hasRole($roleName)) {
                    $user->removeRole($role);
                    Log::info("SYSTEM_LOG: Role {$roleName} dicabut dari User ID {$user->id}");
                }
            }
        } catch (\Exception $e) {
            Log::error("SYSTEM_ERROR: Gagal sync role Pembimbing Akademik", [
                'pa_id' => $pembimbingAkademik->id,
                'message' => $e->getMessage()
            ]);
        }
    }

    public function created(PembimbingAkademik $pembimbingAkademik): void
    {
        $this->handleRole($pembimbingAkademik);
    }

    public function updated(PembimbingAkademik $pembimbingAkademik): void
    {
        $this->handleRole($pembimbingAkademik);
    }

    public function deleted(PembimbingAkademik $pembimbingAkademik): void
    {
        $this->handleRole($pembimbingAkademik);
    }
}

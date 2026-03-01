<?php

namespace App\Observers;

use App\Models\Pegawai;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Spatie\Permission\Models\Role;

class PegawaiObserver
{
    /**
     * Handle the Pegawai "created" event.
     */
    public function created(Pegawai $pegawai): void
    {
        // Hindari infinite loop jika ada mass insertion tanpa events
        // Jika data belum punya referensi user_id
        if (!$pegawai->user_id) {
            $user = User::create([
                'name' => $pegawai->nama_lengkap,
                'email' => $pegawai->email ?? strtolower(str_replace(' ', '', $pegawai->nip)) . '@apps.local',
                'username' => $pegawai->nip,
                'password' => Hash::make($pegawai->nip . '@Polsa') // Default password
            ]);

            // Assign Default Role Pegawai
            Role::firstOrCreate(['name' => 'Pegawai', 'guard_name' => 'web']);
            $user->assignRole('Pegawai');

            // Set foreign key dan save diam-diam (tanpa trigger event updated agar tidak berulang)
            $pegawai->user_id = $user->id;
            $pegawai->saveQuietly();
        }
    }

    /**
     * Handle the Pegawai "updated" event.
     */
    public function updated(Pegawai $pegawai): void
    {
        // Update user name/email jika data pegawai diubah
        if ($pegawai->user_id && ($pegawai->isDirty('nama_lengkap') || $pegawai->isDirty('email') || $pegawai->isDirty('nip'))) {
            $user = User::find($pegawai->user_id);
            if ($user) {
                if ($pegawai->isDirty('nama_lengkap'))
                    $user->name = $pegawai->nama_lengkap;
                if ($pegawai->isDirty('email') && $pegawai->email)
                    $user->email = $pegawai->email;
                if ($pegawai->isDirty('nip'))
                    $user->username = $pegawai->nip;
                $user->save();
            }
        }
    }

    /**
     * Handle the Pegawai "deleted" event.
     */
    public function deleted(Pegawai $pegawai): void
    {
        // Softdelete user juga
        if ($pegawai->user_id) {
            $user = User::find($pegawai->user_id);
            if ($user) {
                // Remove roles jika diperlukan atau delete saja
                $user->delete();
            }
        }
    }

    /**
     * Handle the Pegawai "restored" event.
     */
    public function restored(Pegawai $pegawai): void
    {
        if ($pegawai->user_id) {
            $user = User::withTrashed()->find($pegawai->user_id);
            if ($user) {
                $user->restore();
            }
        }
    }

    /**
     * Handle the Pegawai "force deleted" event.
     */
    public function forceDeleted(Pegawai $pegawai): void
    {
        //
    }
}

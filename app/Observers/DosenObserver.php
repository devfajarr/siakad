<?php

namespace App\Observers;

use App\Models\Dosen;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Spatie\Permission\Models\Role;

class DosenObserver
{
    /**
     * Handle the Dosen "created" event.
     */
    public function created(Dosen $dosen): void
    {
        // 1. Determine a unique username and email base.
        // NIDN is preferred, NIP is fallback, or random string if both absent.
        $loginId = $dosen->nidn ?? $dosen->nip ?? strtolower(Str::random(10));

        $email = $dosen->email ?? ($loginId . '@polsa.ac.id');

        // 2. Automate User creation
        $user = User::where('username', $loginId)
            ->orWhere('email', $email)
            ->first();

        if (!$user) {
            $user = User::create([
                'name' => $dosen->nama,
                'username' => $loginId,
                'email' => $email,
                'password' => Hash::make($loginId), // Default password is NIDN or NIP
            ]);
        }

        // 3. Bind the user to the Dosen profile quietly
        $dosen->updateQuietly(['user_id' => $user->id]);

        // 4. Attach default 'Dosen' role to the user
        $roleDosen = Role::firstOrCreate(['name' => 'Dosen']);
        $user->assignRole($roleDosen);
    }
}

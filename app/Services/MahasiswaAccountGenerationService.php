<?php

namespace App\Services;

use App\Models\Mahasiswa;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Spatie\Permission\Models\Role;

class MahasiswaAccountGenerationService
{
    /**
     * Generate a user account for a specific Mahasiswa.
     * 
     * @param Mahasiswa $mahasiswa
     * @return User|false
     * @throws \Exception
     */
    public function generate(Mahasiswa $mahasiswa)
    {
        // 1. Verifikasi (cek user_id)
        if ($mahasiswa->user_id) {
            return false; // Sudah punya akun
        }

        $username = $mahasiswa->nim;
        if (!$username) {
            throw new \Exception('Mahasiswa tidak memiliki NIM, gagal membuat username.');
        }

        // Pastikan username bersih dari spasi (penting untuk NIM dari database)
        $username = trim($username);

        DB::beginTransaction();
        try {
            // Prioritas email asli dari profil mahasiswa, fallback ke dummy email
            $email = !empty($mahasiswa->email)
                ? strtolower($mahasiswa->email)
                : strtolower($username) . '@mhs.polsa.ac.id';

            // 2. Buat User
            $user = User::firstOrCreate(
                ['email' => $email],
                [
                    'name' => $mahasiswa->nama_mahasiswa,
                    'username' => $username,
                    'password' => Hash::make($username),
                ]
            );

            // 3. Assign Role Mahasiswa
            $roleMahasiswa = Role::firstOrCreate(['name' => 'Mahasiswa']);
            if (!$user->hasRole('Mahasiswa')) {
                $user->assignRole($roleMahasiswa);
            }

            // 4. Update relasi Mahasiswa
            $mahasiswa->updateQuietly(['user_id' => $user->id]);

            DB::commit();

            return $user;

        } catch (\Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }
}

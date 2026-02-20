<?php

namespace App\Services;

use App\Models\User;
use App\Models\RiwayatPendidikan;
use Modules\Akademiks\app\Models\Dosen;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\DB;
use Spatie\Permission\Models\Role;

class UserSyncService
{
    /**
     * Sync Mahasiswa Users from RiwayatPendidikan
     * One User per NIM
     */
    public function syncMahasiswa()
    {
        $riwayats = RiwayatPendidikan::with('mahasiswa')
            ->whereNotNull('nim')
            ->get();

        foreach ($riwayats as $riwayat) {
            DB::transaction(function () use ($riwayat) {
                // Determine password (DDMMYYYY)
                $dob = $riwayat->mahasiswa->tanggal_lahir;
                $password = $dob ? $dob->format('dmY') : '12345678';

                $user = User::updateOrCreate(
                    ['username' => $riwayat->nim],
                    [
                        'name' => $riwayat->mahasiswa->nama_mahasiswa,
                        'email' => $riwayat->mahasiswa->email ?? $riwayat->nim . '@student.university.ac.id',
                        'password' => Hash::make($password),
                        'email_verified_at' => now(),
                    ]
                );

                // Polymorphic Link
                $user->profileable()->associate($riwayat);
                $user->save();

                // Assign Role
                $user->assignRole('Mahasiswa');
            });
        }
    }

    /**
     * Sync Dosen Users from Dosen Master
     * One User per NIDN
     */
    public function syncDosen()
    {
        $dosens = Dosen::whereNotNull('nidn')->get();

        foreach ($dosens as $dosen) {
            DB::transaction(function () use ($dosen) {
                $user = User::updateOrCreate(
                    ['username' => $dosen->nidn],
                    [
                        'name' => $dosen->nama_dosen,
                        'email' => $dosen->email ?? $dosen->nidn . '@lecturer.university.ac.id',
                        'password' => Hash::make($dosen->nidn), // Default password = NIDN
                        'email_verified_at' => now(),
                    ]
                );

                // Polymorphic Link
                $user->profileable()->associate($dosen);
                $user->save();

                // Assign Role
                $user->assignRole('Dosen');

                // Logic for Kaprodi or other structural roles can be added here
                // if ($dosen->jabatan_struktural === 'Kaprodi') {
                //     $user->assignRole('Kaprodi');
                // }
            });
        }
    }
}

<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\PermissionRegistrar;

class RolesAndPermissionsSeeder extends Seeder
{
    public function run()
    {
        // Reset cached roles and permissions
        app()[PermissionRegistrar::class]->forgetCachedPermissions();

        // Create Permissions
        $permissions = [
            'view_mahasiswa',
            'view_dosen',
            'view_nilai',
            'create_krs',
            'acc_krs_prodi',
            'acc_krs_wali',
            'view_khs_own',
        ];

        foreach ($permissions as $permission) {
            Permission::firstOrCreate(['name' => $permission]);
        }

        // Create Roles and assign created permissions

        // Mahasiswa
        $role = Role::firstOrCreate(['name' => 'Mahasiswa']);
        $role->givePermissionTo(['create_krs', 'view_khs_own']);

        // Dosen
        $role = Role::firstOrCreate(['name' => 'Dosen']);
        $role->givePermissionTo(['view_mahasiswa']);

        // Dosen Wali
        $role = Role::firstOrCreate(['name' => 'Dosen Wali']);
        $role->givePermissionTo(['acc_krs_wali', 'view_khs_own']);

        // Kaprodi
        $role = Role::firstOrCreate(['name' => 'Kaprodi']);
        $role->givePermissionTo(['acc_krs_prodi', 'view_nilai']);

        // Admin
        $role = Role::firstOrCreate(['name' => 'Admin']);
        $role->givePermissionTo(Permission::all());
    }
}

<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\User;
use App\Models\Mahasiswa;
use App\Models\RiwayatPendidikan;
use Modules\Akademiks\app\Models\Dosen;
use App\Services\UserSyncService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;

class UserSyncTest extends TestCase
{
    use RefreshDatabase;

    public function setUp(): void
    {
        parent::setUp();

        // Reset cached roles and permissions
        app()[PermissionRegistrar::class]->forgetCachedPermissions();

        $this->seed(\Database\Seeders\RolesAndPermissionsSeeder::class);
    }

    /** @test */
    public function it_can_sync_mahasiswa_users()
    {
        // Arrange
        $mahasiswa = Mahasiswa::create([
            'nama_mahasiswa' => 'Test Student',
            'tanggal_lahir' => '2000-01-01',
            // Add other required fields based on schema, assuming nullable for now or add defaults
            'tempat_lahir' => 'Test Place',
            'jenis_kelamin' => 'L',
            'id_agama' => 1,
            'kewarganegaraan' => 'ID',
            'nik' => '1234567890123456',
            'nisn' => '1234567890',
            'jalan' => 'Test Street',
            // ... other fields
        ]);

        $riwayat = RiwayatPendidikan::create([
            'id_mahasiswa' => $mahasiswa->id,
            'nim' => '12345678',
            'id_periode_masuk' => '20201',
            'id_prodi' => 'uuid-prodi', // Mock
            // ... other fields
        ]);

        // Act
        $service = new UserSyncService();
        $service->syncMahasiswa();

        // Assert
        $this->assertDatabaseHas('users', [
            'username' => '12345678',
            'email' => '12345678@student.university.ac.id',
        ]);

        $user = User::where('username', '12345678')->first();
        $this->assertTrue($user->hasRole('Mahasiswa'));
        $this->assertEquals(RiwayatPendidikan::class, $user->profileable_type);
        $this->assertEquals($riwayat->id, $user->profileable_id);
    }

    /** @test */
    public function it_can_sync_dosen_users()
    {
        // Arrange
        $dosen = Dosen::create([
            'nidn' => '99999999',
            'nama_dosen' => 'Test Lecturer',
            'email' => 'lecturer@university.ac.id',
            // ... other fields
        ]);

        // Act
        $service = new UserSyncService();
        $service->syncDosen();

        // Assert
        $this->assertDatabaseHas('users', [
            'username' => '99999999',
            'email' => 'lecturer@university.ac.id',
        ]);

        $user = User::where('username', '99999999')->first();
        $this->assertTrue($user->hasRole('Dosen'));
        $this->assertEquals(Dosen::class, $user->profileable_type);
        $this->assertEquals($dosen->id, $user->profileable_id);
    }
}

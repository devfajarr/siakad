<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\TestCase;
use App\Models\User;
use App\Models\Dosen;
use App\Models\Pegawai;
use App\Models\Sarpras;
use App\Models\Direktur;
use App\Models\PembimbingAkademik;
use Spatie\Permission\Models\Role;

class RoleAssignmentTest extends TestCase
{
    use DatabaseTransactions;

    protected function setUp(): void
    {
        parent::setUp();
        // Setup Roles
        Role::firstOrCreate(['name' => 'admin']);
        Role::firstOrCreate(['name' => 'Dosen']);
        Role::firstOrCreate(['name' => 'Pegawai']);
        Role::firstOrCreate(['name' => 'sarpras']);
        Role::firstOrCreate(['name' => 'direktur']);
        Role::firstOrCreate(['name' => 'pembimbing_akademik']);
    }

    public function test_dosen_assigned_sarpras_role_when_sarpras_created()
    {
        $user = User::factory()->create(['username' => 'dosen_a']);
        $user->assignRole('Dosen');

        $dosen = Dosen::create([
            'user_id' => $user->id,
            'nama' => 'Dosen A',
            'nidn' => '123456789'
        ]);

        $this->assertFalse($user->hasRole('sarpras'));

        $sarpras = Sarpras::create([
            'id_dosen' => $dosen->id,
            'is_active' => true,
        ]);

        $this->assertTrue($user->fresh()->hasRole('sarpras'));

        $sarpras->is_active = false;
        $sarpras->save();

        $this->assertFalse($user->fresh()->hasRole('sarpras'));

        $sarpras->is_active = true;
        $sarpras->save();
        $this->assertTrue($user->fresh()->hasRole('sarpras'));

        $sarpras->delete();
        $this->assertFalse($user->fresh()->hasRole('sarpras'));
    }

    public function test_pegawai_assigned_sarpras_role()
    {
        $user = User::factory()->create(['username' => 'pegawai_a']);
        $user->assignRole('Pegawai');

        $pegawai = Pegawai::create([
            'user_id' => $user->id,
            'nama_pegawai' => 'Pegawai A',
            'nip' => 'PEG-001',
        ]);

        $this->assertFalse($user->hasRole('sarpras'));

        $sarpras = Sarpras::create([
            'id_pegawai' => $pegawai->id,
            'is_active' => true,
        ]);

        $this->assertTrue($user->fresh()->hasRole('sarpras'));
    }

    public function test_dosen_assigned_direktur_role()
    {
        $user = User::factory()->create(['username' => 'dosen_b']);

        $dosen = Dosen::create([
            'user_id' => $user->id,
            'nama' => 'Dosen B',
            'nidn' => '987654321'
        ]);

        $direktur = Direktur::create([
            'id_dosen' => $dosen->id,
            'is_active' => true,
        ]);

        $this->assertTrue($user->fresh()->hasRole('direktur'));
    }

    public function test_dosen_assigned_pembimbing_akademik_role()
    {
        $user = User::factory()->create(['username' => 'dosen_pa']);

        $dosen = Dosen::create([
            'user_id' => $user->id,
            'nama' => 'Dosen PA',
            'nidn' => '111222333'
        ]);

        $pa = PembimbingAkademik::create([
            'id_dosen' => $dosen->id,
            'id_prodi' => \Illuminate\Support\Str::uuid(),
            'id_semester' => '20231', // Semester ID is usually string '20231' or uuid
        ]);

        $this->assertTrue($user->fresh()->hasRole('pembimbing_akademik'));

        $pa->delete();
        $this->assertFalse($user->fresh()->hasRole('pembimbing_akademik'));
    }

    public function test_user_can_act_as_hybrid_roles()
    {
        $user = User::factory()->create([
            'username' => 'dosen_hybrid',
        ]);
        $user->assignRole('Dosen');
        $user->assignRole('sarpras');

        $this->actingAs($user);

        // Just ensure roles are retrievable
        $this->assertTrue(auth()->user()->hasRole('Dosen'));
        $this->assertTrue(auth()->user()->hasRole('sarpras'));
    }
}

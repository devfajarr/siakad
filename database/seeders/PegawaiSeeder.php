<?php

namespace Database\Seeders;

use App\Models\Pegawai;
use Illuminate\Database\Seeder;
use Faker\Factory as Faker;

class PegawaiSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $faker = Faker::create('id_ID');

        $unitKerja = ['Biro Akademik', 'Biro Keuangan', 'UPT Komputer', 'Perpustakaan', 'Seksi Keamanan'];
        $jabatans = ['Staf Administrasi', 'Staf Keuangan', 'Teknisi IT', 'Pustakawan', 'Danru Satpam'];

        for ($i = 1; $i <= 10; $i++) {
            $nip = 'P' . date('Y') . str_pad($i, 3, '0', STR_PAD_LEFT);

            Pegawai::create([
                'nip' => $nip,
                'nama_lengkap' => $faker->name,
                'unit_kerja' => $faker->randomElement($unitKerja),
                'jabatan' => $faker->randomElement($jabatans),
                'no_hp' => '0812' . $faker->randomNumber(8, true),
                'email' => strtolower($nip) . '@polsa.ac.id',
                'is_active' => true,
            ]);
        }
    }
}

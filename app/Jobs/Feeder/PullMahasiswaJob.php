<?php

namespace App\Jobs\Feeder;

use App\Models\Mahasiswa;
use Carbon\Carbon;

class PullMahasiswaJob extends BaseSyncJob
{
    protected function getEntityName(): string
    {
        return 'Mahasiswa';
    }

    protected function syncRow(array $row): void
    {
        Mahasiswa::updateOrCreate(
            ['id_feeder' => $row['id_mahasiswa']], // id_feeder is our standardized UUID
            [
                'nama_mahasiswa' => $row['nama_mahasiswa'],
                'jenis_kelamin' => $row['jenis_kelamin'],
                'tempat_lahir' => $row['tempat_lahir'],
                'tanggal_lahir' => $row['tanggal_lahir'],
                'id_agama' => $row['id_agama'],
                'nik' => $row['nik'],
                'nisn' => $row['nisn'],
                'nama_ibu_kandung' => $row['nama_ibu_kandung'],
                'id_wilayah' => $row['id_wilayah'],
                'kelurahan' => $row['kelurahan'],
                'handphone' => $row['handphone'],
                'email' => $row['email'],
                'status_sinkronisasi' => 'synced',
                'last_synced_at' => Carbon::now(),
                'sumber_data' => 'server'
            ]
        );
    }
}

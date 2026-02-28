<?php

namespace App\Observers;

use App\Models\Mahasiswa;

class MahasiswaObserver
{
    /**
     * Handle the Mahasiswa "saving" event.
     * This catches both create and update events before hitting the database.
     */
    public function saving(Mahasiswa $mahasiswa): void
    {
        // Hanya proses jika tipe_kelas belum di-set secara manual
        if (empty($mahasiswa->tipe_kelas)) {
            // Coba ambil NIM dari riwayat pendidikan (bisa jadi yang baru di-attach, atau yang eksisting)
            // Atau dari attribute sementara 'nim' jika dipassing saat sync
            $nim = $mahasiswa->nim ?? $mahasiswa->getAttribute('nim_sementara');

            if ($nim && strlen($nim) >= 5) {
                $digitKeLima = substr($nim, 4, 1);
                $mahasiswa->tipe_kelas = ($digitKeLima === '1') ? 'Pagi' : 'Sore';
            }
        }
    }

    /**
     * Handle the Mahasiswa "updated" event.
     */
    public function updated(Mahasiswa $mahasiswa): void
    {
        //
    }

    /**
     * Handle the Mahasiswa "deleted" event.
     */
    public function deleted(Mahasiswa $mahasiswa): void
    {
        //
    }

    /**
     * Handle the Mahasiswa "restored" event.
     */
    public function restored(Mahasiswa $mahasiswa): void
    {
        //
    }

    /**
     * Handle the Mahasiswa "force deleted" event.
     */
    public function forceDeleted(Mahasiswa $mahasiswa): void
    {
        //
    }
}

<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\RiwayatPendidikan;
use App\Models\JenisPendaftaran; // Assuming model name
use Illuminate\Support\Facades\DB;

class ProbeRiwayatPadding extends Command
{
    protected $signature = 'probe:padding';
    protected $description = 'Probe padding in RiwayatPendidikan and Reference tables';

    public function handle()
    {
        $riwayat = RiwayatPendidikan::first();
        if (!$riwayat) {
            $this->error('No RiwayatPendidikan found.');
            return;
        }

        $this->info("Riwayat ID: {$riwayat->id}");
        $this->info("Jenis Daftar: '" . $riwayat->id_jenis_daftar . "' (Len: " . strlen($riwayat->id_jenis_daftar) . ")");
        $this->info("Jalur Daftar: '" . $riwayat->id_jalur_daftar . "' (Len: " . strlen($riwayat->id_jalur_daftar) . ")");

        // Check Reference
        // I need to guess the model name for JenisPendaftaran if it's not standard
        // But I can use DB table directly
        $jenis = DB::table('jenis_daftars')->where('id_jenis_daftar', trim($riwayat->id_jenis_daftar))->first();
        if ($jenis) {
            $this->info("Ref Jenis: '" . $jenis->id_jenis_daftar . "' (Len: " . strlen($jenis->id_jenis_daftar) . ")");
        } else {
            $this->warn("Ref Jenis not found for '" . trim($riwayat->id_jenis_daftar) . "'");
        }

        $jalur = DB::table('jalur_pendaftarans')->where('id_jalur_daftar', trim($riwayat->id_jalur_daftar))->first();
        if ($jalur) {
            $this->info("Ref Jalur: '" . $jalur->id_jalur_daftar . "' (Len: " . strlen($jalur->id_jalur_daftar) . ")");
        }
    }
}

<?php

namespace App\Console\Commands;

use App\Models\Dosen;
use App\Models\DosenPenugasan;
use App\Models\DosenRiwayatFungsional;
use App\Models\DosenRiwayatPangkat;
use App\Models\DosenRiwayatPendidikan;
use App\Models\DosenRiwayatPenelitian;
use App\Models\DosenRiwayatSertifikasi;
use App\Services\Feeder\DosenFeederService;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;

class SyncDosenFromPusat extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'sync:dosen-from-pusat';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Sinkronisasi data dosen dari Feeder Dikti';

    public function __construct(protected DosenFeederService $dosenFeederService)
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('Mulai sinkronisasi dosen dari Feeder...');

        try {
            // 1. Fetch List Dosen
            $this->info('Fetching list dosen...');
            $dosenList = $this->dosenFeederService->getListDosen();

            $total = count($dosenList);
            $this->info("Ditemukan {$total} data dosen.");

            $bar = $this->output->createProgressBar($total);
            $bar->start();

            $success = 0;
            $failed = 0;
            $errors = [];

            foreach ($dosenList as $dosenData) {
                try {
                    try {
                        // Logging detail untuk debugging
                        Log::debug('Calling DetailBiodataDosen', [
                            'requested_id_dosen' => $dosenData['id_dosen'],
                            'nama_dosen' => $dosenData['nama_dosen'] ?? 'N/A',
                        ]);

                        $detail = $this->dosenFeederService->getDetailBiodataDosen($dosenData['id_dosen']);
                        $detailData = isset($detail[0]) ? $detail[0] : (! empty($detail) ? $detail : []);

                        // Logging response untuk debugging
                        Log::debug('DetailBiodataDosen response', [
                            'requested_id_dosen' => $dosenData['id_dosen'],
                            'response_count' => count($detail),
                            'first_item_id' => $detailData['id_dosen'] ?? 'null',
                            'response_matches' => ($detailData['id_dosen'] ?? '') === $dosenData['id_dosen'],
                        ]);

                        // Only use detail data if it actually matches this dosen
                        if (! empty($detailData) && ($detailData['id_dosen'] ?? '') === $dosenData['id_dosen']) {
                            // Safe to merge — detail matches the correct dosen
                            $dosenData['tempat_lahir'] = $detailData['tempat_lahir'] ?? null;
                            Log::debug("Detail API success untuk {$dosenData['nama_dosen']}", [
                                'id_dosen' => $dosenData['id_dosen'],
                                'tempat_lahir' => $dosenData['tempat_lahir'] ?? 'null',
                            ]);
                        } else {
                            // Detail API returned wrong dosen — skip detail data
                            Log::info("Detail API mismatch untuk {$dosenData['nama_dosen']}: expected {$dosenData['id_dosen']}, got ".($detailData['id_dosen'] ?? 'null'));
                        }
                    } catch (\Exception $e) {
                        // Continue with partial data — don't abort this dosen
                        Log::warning("Detail biodata gagal untuk {$dosenData['nama_dosen']}: ".$e->getMessage(), [
                            'id_dosen' => $dosenData['id_dosen'] ?? 'N/A',
                            'error' => $e->getMessage(),
                            'trace' => $e->getTraceAsString(),
                        ]);
                    }

                    $this->syncDosen($dosenData);

                    $success++;
                } catch (\Exception $e) {
                    $failed++;
                    $nama = $dosenData['nama_dosen'] ?? 'Unknown';
                    $errors[] = "{$nama}: {$e->getMessage()}";
                    Log::warning("SyncDosen gagal [{$nama}]: ".$e->getMessage());
                    // Continue to next dosen — don't abort the loop
                }

                $bar->advance();
            }

            $bar->finish();
            $this->newLine(2);

            // Summary
            $this->info("Sinkronisasi selesai: {$success} berhasil, {$failed} gagal dari {$total} data.");

            if (! empty($errors)) {
                $this->newLine();
                $this->warn('Dosen yang gagal:');
                foreach (array_slice($errors, 0, 10) as $err) {
                    $this->line("  • {$err}");
                }
                if (count($errors) > 10) {
                    $this->line('  ... dan '.(count($errors) - 10).' lainnya. Lihat log untuk detail.');
                }
            }

        } catch (\Exception $e) {
            $this->error('Gagal sinkronisasi: '.$e->getMessage());
            Log::error('SyncDosenFromPusat Error: '.$e->getMessage());
        }
    }

    protected function syncDosen(array $data)
    {
        // Mapping Data
        $tglLahir = $data['tanggal_lahir'] ?? null;
        if ($tglLahir) {
            try {
                $tglLahir = \Carbon\Carbon::createFromFormat('d-m-Y', $tglLahir)->format('Y-m-d');
            } catch (\Exception $e) {
                // Already Y-m-d or other format, try to parse
                try {
                    $tglLahir = \Carbon\Carbon::parse($tglLahir)->format('Y-m-d');
                } catch (\Exception $e2) {
                    $tglLahir = null;
                }
            }
        }

        // Use external_id (id_dosen from Feeder) as the unique key
        $dosen = Dosen::updateOrCreate(
            ['external_id' => $data['id_dosen']],
            [
                'nidn' => $data['nidn'] ?? null,
                'nip' => $data['nip'] ?? null,
                'nama' => $data['nama_dosen'],
                'tempat_lahir' => $data['tempat_lahir'] ?? null,
                'tanggal_lahir' => $tglLahir,
                'jenis_kelamin' => $data['jenis_kelamin'] ?? null,
                'id_agama' => $data['id_agama'] ?? null,
                'id_status_aktif' => $data['id_status_aktif'] ?? null,
                'status_sinkronisasi' => 'pusat',
                'is_active' => true,
            ]
        );

        // Sync Related Data (errors here should not rollback dosen creation)
        try {
            $this->syncPenugasan($dosen);
        } catch (\Exception $e) {
            Log::warning("Penugasan sync gagal untuk {$dosen->nama}: ".$e->getMessage());
        }

        try {
            $this->syncRiwayat($dosen);
        } catch (\Exception $e) {
            Log::warning("Riwayat sync gagal untuk {$dosen->nama}: ".$e->getMessage());
        }
    }

    protected function syncPenugasan(Dosen $dosen)
    {
        // Gunakan GetListPenugasanDosen per dosen
        $penugasanList = $this->dosenFeederService->getListPenugasanDosen($dosen->external_id);

        foreach ($penugasanList as $penugasan) {
            DosenPenugasan::updateOrCreate(
                [
                    'external_id' => $penugasan['id_registrasi_dosen'],
                ],
                [
                    'id_dosen' => $dosen->id, // Gunakan id lokal dosen, bukan UUID dari API
                    'id_tahun_ajaran' => $penugasan['id_tahun_ajaran'],
                    'id_prodi' => $penugasan['id_prodi'], // UUID dari API
                    'jenis_penugasan' => null, // field di API mungkin beda, sesuaikan jika ada mapping
                    'unit_penugasan' => null, // field di API mungkin beda
                    'tanggal_mulai' => $penugasan['tanggal_surat_tugas'],
                    'tanggal_selesai' => null, // field di API mungkin beda
                    'sumber_data' => 'pusat',
                ]
            );
        }
    }

    protected function syncRiwayat(Dosen $dosen)
    {
        // 1. Fungsional
        $fungsionalList = $this->dosenFeederService->getRiwayatFungsionalDosen($dosen->external_id);
        if (! empty($fungsionalList)) {
            // dd($fungsionalList);
        }
        foreach ($fungsionalList as $item) {
            DosenRiwayatFungsional::updateOrCreate(
                [
                    'id_dosen' => $dosen->id,
                    'sk_nomor' => $item['sk_jabatan_fungsional'],
                ],
                [
                    'external_id' => $item['id_jabatan_fungsional'] ?? null,
                    'jabatan_fungsional' => $item['nama_jabatan_fungsional'],
                    'sk_tanggal' => null,
                    'tmt_jabatan' => $item['mulai_sk_jabatan'] ?? null,
                ]
            );
        }

        // 2. Pangkat
        $pangkatList = $this->dosenFeederService->getRiwayatPangkatDosen($dosen->external_id);
        if (! empty($pangkatList)) {
            // dd($pangkatList);
        }
        foreach ($pangkatList as $item) {
            // Date conversions
            $tglSk = $item['tanggal_sk_pangkat'] ?? null;
            $tmt = $item['mulai_sk_pangkat'] ?? null;

            try {
                if ($tglSk) {
                    $tglSk = \Carbon\Carbon::createFromFormat('d-m-Y', $tglSk)->format('Y-m-d');
                }
                if ($tmt) {
                    $tmt = \Carbon\Carbon::createFromFormat('d-m-Y', $tmt)->format('Y-m-d');
                }
            } catch (\Exception $e) {
            }

            DosenRiwayatPangkat::updateOrCreate(
                [
                    'id_dosen' => $dosen->id,
                    'sk_nomor' => $item['sk_pangkat'],
                ],
                [
                    'external_id' => $item['id_pangkat_golongan'] ?? null,
                    'pangkat_golongan' => $item['nama_pangkat_golongan'],
                    'sk_tanggal' => $tglSk,
                    'tmt_pangkat' => $tmt,
                ]
            );
        }

        // 3. Pendidikan
        $pendidikanList = $this->dosenFeederService->getRiwayatPendidikanDosen($dosen->external_id);
        if (! empty($pendidikanList)) {
            // dd($pendidikanList);
        }
        foreach ($pendidikanList as $item) {
            // Identifier: Dosen + Jenjang + PT + Tahun Lulus
            DosenRiwayatPendidikan::updateOrCreate(
                [
                    'id_dosen' => $dosen->id,
                    'jenjang_pendidikan' => $item['nama_jenjang_pendidikan'],
                    'perguruan_tinggi' => $item['nama_perguruan_tinggi'],
                    'tahun_lulus' => $item['tahun_lulus'],
                ],
                [
                    'external_id' => null, // No ID
                    'gelar_akademik' => $item['nama_gelar_akademik'] ?? null,
                    'program_studi' => $item['nama_bidang_studi'] ?? $item['nama_program_studi'] ?? null,
                    'sk_penyetaraan' => null,
                    'tanggal_ijazah' => null,
                    'nomor_ijazah' => null,
                ]
            );
        }

        // 4. Sertifikasi
        $sertifikasiList = $this->dosenFeederService->getRiwayatSertifikasiDosen($dosen->external_id);
        if (! empty($sertifikasiList)) {
            // dd($sertifikasiList);
        }
        foreach ($sertifikasiList as $item) {
            DosenRiwayatSertifikasi::updateOrCreate(
                [
                    'id_dosen' => $dosen->id,
                    'nomor_sertifikasi' => $item['nomor_peserta'] ?? $item['sk_sertifikasi'],
                ],
                [
                    'external_id' => null,
                    'jenis_sertifikasi' => $item['nama_jenis_sertifikasi'],
                    'tahun_sertifikasi' => $item['tahun_sertifikasi'],
                    'bidang_studi' => $item['nama_bidang_studi'],
                ]
            );
        }

        // 5. Penelitian
        $penelitianList = $this->dosenFeederService->getRiwayatPenelitianDosen($dosen->external_id);
        if (! empty($penelitianList)) {
            // dd($penelitianList);
        }
        foreach ($penelitianList as $item) {
            // Parse tahun
            $tahun = $item['tahun_kegiatan'];
            if (strpos($tahun, '/') !== false) {
                $tahun = explode('/', $tahun)[0];
            }
            $tahun = (int) $tahun; // ensure int

            DosenRiwayatPenelitian::updateOrCreate(
                ['external_id' => $item['id_penelitian']],
                [
                    'id_dosen' => $dosen->id,
                    'judul_penelitian' => $item['judul_penelitian'],
                    'kategori_kegiatan' => null,
                    'kelompok_bidang' => $item['nama_kelompok_bidang'] ?? null,
                    'lembaga_iptek' => $item['nama_lembaga_iptek'] ?? null,
                    'tahun_kegiatan' => $tahun,
                ]
            );
        }
    }
}

<?php

namespace App\Exports;

use App\Models\Dosen;
use App\Models\PresensiPertemuan;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class RekapHonorerMengajarExport implements FromCollection, WithHeadings, WithMapping, WithStyles, ShouldAutoSize
{
    protected $bulanTahun;

    public function __construct($bulanTahun)
    {
        $this->bulanTahun = $bulanTahun;
    }

    public function collection()
    {
        // 1. Re-use eager loading query from Controller
        return Dosen::with([
            'pengajaranKelas.kelasKuliah.mataKuliah',
            'pengajaranKelas.kelasKuliah.presensiPertemuans' => function ($q) {
                if ($this->bulanTahun) {
                    $explode = explode('-', $this->bulanTahun);
                    if (count($explode) == 2) {
                        $q->whereYear('tanggal', $explode[0])
                            ->whereMonth('tanggal', $explode[1]);
                    }
                }
            }
        ])
            ->whereHas('pengajaranKelas', function ($qHas) {
                $qHas->whereHas('kelasKuliah.presensiPertemuans', function ($qPresensi) {
                    if ($this->bulanTahun) {
                        $explode = explode('-', $this->bulanTahun);
                        if (count($explode) == 2) {
                            $qPresensi->whereYear('tanggal', $explode[0])
                                ->whereMonth('tanggal', $explode[1]);
                        }
                    }
                });
            })
            ->get();
    }

    public function map($dosen): array
    {
        $totalPertemuan = 0;
        $totalTerverifikasi = 0;
        $totalPending = 0;
        $totalSks = 0;
        $mataKuliahArr = [];

        foreach ($dosen->pengajaranKelas as $pk) {
            if ($pk->kelasKuliah) {
                $sksKelas = floatval($pk->kelasKuliah->mataKuliah->sks_mata_kuliah ?? 0);
                $mkNama = $pk->kelasKuliah->mataKuliah->nama_mk ?? '-';

                $addedSks = false;

                foreach ($pk->kelasKuliah->presensiPertemuans as $presensi) {
                    if ($presensi->id_dosen === $dosen->id) {
                        $totalPertemuan++;

                        if ($presensi->status_verifikasi === PresensiPertemuan::STATUS_TERVERIFIKASI) {
                            $totalTerverifikasi++;
                        } else {
                            $totalPending++;
                        }

                        if (!$addedSks) {
                            $totalSks += $sksKelas;
                            if (!in_array($mkNama, $mataKuliahArr)) {
                                $mataKuliahArr[] = $mkNama;
                            }
                            $addedSks = true;
                        }
                    }
                }
            }
        }

        // Hitung estimasi honor (Asumsi 100.000 / Pertemuan Valid)
        $estimasiHonor = $totalTerverifikasi * 100000;

        return [
            $dosen->nama_admin_display,
            $dosen->nidn ?? $dosen->nip ?? '-',
            implode(", ", $mataKuliahArr),
            $totalSks,
            $totalPertemuan,
            $totalTerverifikasi,
            $totalPending,
            $estimasiHonor,
        ];
    }

    public function headings(): array
    {
        return [
            'Nama Dosen Pengajar',
            'NIDN / NIP',
            'Mata Kuliah Diampu',
            'Total SKS',
            'Total Pertemuan',
            'Pertemuan Terverifikasi (Sah)',
            'Pertemuan Pending / Ditolak',
            'Estimasi Total Honor'
        ];
    }

    public function styles(Worksheet $sheet)
    {
        return [
            1 => ['font' => ['bold' => true, 'color' => ['argb' => 'FFFFFFFF']], 'fill' => ['fillType' => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID, 'startColor' => ['argb' => 'FF1A73E8']]],
        ];
    }
}

<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>KRS_{{ $mahasiswa->nim }}_{{ $semester->id_semester }}</title>
    <style>
        body {
            font-family: 'Arial', sans-serif;
            font-size: 12px;
            color: #333;
            margin: 0;
            padding: 20px;
        }

        .header {
            text-align: center;
            border-bottom: 2px solid #000;
            padding-bottom: 10px;
            margin-bottom: 20px;
        }

        .header h2 {
            margin: 5px 0;
            text-transform: uppercase;
        }

        .header p {
            margin: 2px 0;
        }

        .student-info {
            width: 100%;
            margin-bottom: 20px;
            border-collapse: collapse;
        }

        .student-info td {
            padding: 3px 0;
        }

        .student-info td:nth-child(2) {
            width: 20px;
            text-align: center;
        }

        .krs-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 20px;
        }

        .krs-table th,
        .krs-table td {
            border: 1px solid #000;
            padding: 8px;
            text-align: left;
        }

        .krs-table th {
            background-color: #f2f2f2;
            text-transform: uppercase;
        }

        .krs-table .center {
            text-align: center;
        }

        .footer {
            width: 100%;
            margin-top: 50px;
        }

        .footer td {
            width: 50%;
            text-align: center;
            vertical-align: top;
        }

        .signature-space {
            height: 80px;
        }

        @media print {
            .no-print {
                display: none;
            }

            body {
                padding: 0;
            }
        }
    </style>
</head>

<body onload="@if(request()->has('autoprint')) window.print() @endif">
    <div class="no-print"
        style="background: #fff3cd; padding: 10px; border: 1px solid #ffeeba; margin-bottom: 20px; border-radius: 4px;">
        <button onclick="window.print()" style="padding: 10px 20px; cursor: pointer;">Cetak Sekarang (Ctrl+P)</button>
        <p style="margin-top: 5px; font-size: 11px;">Gunakan pengaturan "Save as PDF" jika ingin menyimpan sebagai file.
        </p>
    </div>

    <div class="header">
        <h2 style="margin-bottom: 0;">Kartu Rencana Studi (KRS)</h2>
        <p style="font-size: 14px; font-weight: bold;">Semester {{ $semester->nama_semester }}</p>
        <p>Tahun Akademik {{ $semester->id_tahun_ajaran }}</p>
    </div>

    <table class="student-info">
        <tr>
            <td width="100">NIM</td>
            <td>:</td>
            <td style="font-weight: bold;">{{ $mahasiswa->nim }}</td>
            <td width="100">Program Studi</td>
            <td>:</td>
            <td>{{ $mahasiswa->riwayatAktif?->programStudi?->nama_program_studi }}</td>
        </tr>
        <tr>
            <td>Nama</td>
            <td>:</td>
            <td style="font-weight: bold; text-transform: uppercase;">{{ $mahasiswa->nama_mahasiswa }}</td>
            <td>Fakultas</td>
            <td>:</td>
            <td>{{ $mahasiswa->riwayatAktif?->programStudi?->nama_fakultas ?? '-' }}</td>
        </tr>
        <tr>
            <td>Dosen PA</td>
            <td>:</td>
            <td>{{ $mahasiswa->dosenPembimbing?->nama ?? '-' }}</td>
            <td>Status KRS</td>
            <td>:</td>
            <td><strong>{{ strtoupper($krsItems->first()->status_krs ?? 'DRAFT') }}</strong></td>
        </tr>
    </table>

    <table class="krs-table">
        <thead>
            <tr>
                <th width="30" class="center">No</th>
                <th width="100" class="center">Kode MK</th>
                <th>Nama Mata Kuliah</th>
                <th width="40" class="center">SKS</th>
                <th>Kelas</th>
                <th>Dosen Pengajar</th>
            </tr>
        </thead>
        <tbody>
            @forelse($krsItems as $index => $item)
                <tr>
                    <td class="center">{{ $index + 1 }}</td>
                    <td class="center">{{ $item->kelasKuliah->mataKuliah->kode_mk }}</td>
                    <td>{{ $item->kelasKuliah->mataKuliah->nama_mk }}</td>
                    <td class="center">{{ $item->kelasKuliah->sks_mk }}</td>
                    <td class="center">{{ $item->kelasKuliah->nama_kelas_kuliah }}</td>
                    <td>{{ $item->kelasKuliah->dosenPengajar->map(fn($d) => $d->nama_tampilan)->implode(', ') ?: '-' }}</td>
                </tr>
            @empty
                <tr>
                    <td colspan="6" style="text-align: center; padding: 20px;">Data KRS tidak ditemukan.</td>
                </tr>
            @endforelse
        </tbody>
        <tfoot>
            <tr style="font-weight: bold;">
                <td colspan="3" style="text-align: right;">TOTAL SKS :</td>
                <td class="center">{{ $krsItems->sum(fn($i) => $i->kelasKuliah->sks_mk) }}</td>
                <td colspan="2"></td>
            </tr>
        </tfoot>
    </table>

    <table class="footer">
        <tr>
            <td>
                Menyetujui,<br>
                Dosen Pembimbing Akademik
                <div class="signature-space"></div>
                <strong>({{ $mahasiswa->dosenPembimbing?->nama ?? '____________________' }})</strong><br>
                NIDN/NIDK: {{ $mahasiswa->dosenPembimbing?->nidn ?? '__________' }}
            </td>
            <td>
                {{ config('app.location', 'Lokal') }}, {{ now()->translatedFormat('d F Y') }}<br>
                Mahasiswa yang bersangkutan
                <div class="signature-space"></div>
                <strong>({{ strtoupper($mahasiswa->nama_mahasiswa) }})</strong><br>
                NIM: {{ $mahasiswa->nim }}
            </td>
        </tr>
    </table>

    <div style="margin-top: 30px; font-size: 10px; color: #777; font-style: italic;">
        Dicetak secara sistem pada: {{ now()->format('d/m/Y H:i:s') }} | Status:
        {{ $krsItems->first()->status_krs ?? '-' }}
        @if($krsItems->first() && $krsItems->first()->last_acc_at)
            | Disetujui pada: {{ $krsItems->first()->last_acc_at->format('d/m/Y H:i') }}
        @endif
    </div>
</body>

</html>
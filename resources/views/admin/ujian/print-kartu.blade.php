<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Kartu Ujian - {{ $mahasiswa->nama_mahasiswa ?? '' }}</title>
    <style>
        @page {
            size: A4;
            margin: 15mm;
        }

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Times New Roman', serif;
            font-size: 12pt;
            color: #333;
        }

        .kartu-container {
            max-width: 700px;
            margin: 0 auto;
        }

        .header {
            text-align: center;
            border-bottom: 3px double #333;
            padding-bottom: 12px;
            margin-bottom: 20px;
        }

        .header h2 {
            font-size: 16pt;
            text-transform: uppercase;
            letter-spacing: 2px;
            margin-bottom: 2px;
        }

        .header h3 {
            font-size: 14pt;
            margin-bottom: 4px;
        }

        .header p {
            font-size: 10pt;
            color: #666;
        }

        .info-section {
            display: flex;
            justify-content: space-between;
            margin-bottom: 20px;
        }

        .info-left {
            flex: 1;
        }

        .info-right {
            text-align: center;
            min-width: 100px;
        }

        .avatar-placeholder {
            width: 90px;
            height: 110px;
            border: 2px solid #333;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 28pt;
            font-weight: bold;
            color: #666;
            background-color: #f0f0f0;
            margin: 0 auto;
        }

        .info-left table {
            font-size: 11pt;
        }

        .info-left table td {
            padding: 2px 8px 2px 0;
            vertical-align: top;
        }

        .info-left table td:first-child {
            font-weight: bold;
            white-space: nowrap;
        }

        .ujian-table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 15px;
        }

        .ujian-table th,
        .ujian-table td {
            border: 1px solid #333;
            padding: 6px 10px;
            font-size: 10pt;
        }

        .ujian-table th {
            background-color: #f0f0f0;
            text-align: center;
            font-weight: bold;
        }

        .ujian-table td.text-center {
            text-align: center;
        }

        .footer {
            margin-top: 30px;
            display: flex;
            justify-content: flex-end;
        }

        .signature {
            text-align: center;
            min-width: 200px;
        }

        .signature .line {
            border-bottom: 1px solid #333;
            width: 180px;
            margin: 60px auto 5px;
        }

        .signature p {
            font-size: 10pt;
        }

        .badge-tipe {
            display: inline-block;
            padding: 2px 8px;
            font-size: 9pt;
            font-weight: bold;
            border: 1px solid;
        }

        .badge-uts {
            background-color: #d1ecf1;
            border-color: #0dcaf0;
            color: #055160;
        }

        .badge-uas {
            background-color: #cfe2ff;
            border-color: #0d6efd;
            color: #052c65;
        }

        .catatan {
            margin-top: 20px;
            font-size: 9pt;
            color: #666;
            border-top: 1px solid #ccc;
            padding-top: 8px;
        }

        @media print {
            body {
                print-color-adjust: exact;
                -webkit-print-color-adjust: exact;
            }

            .no-print {
                display: none !important;
            }
        }
    </style>
</head>

<body>
    <div class="no-print" style="text-align: center; padding: 10px; background: #f0f0f0; margin-bottom: 20px;">
        <button onclick="window.print()"
            style="padding: 8px 24px; font-size: 14px; cursor: pointer; background: #0d6efd; color: white; border: none; border-radius: 4px;">
            Cetak Kartu Ujian
        </button>
        <button onclick="window.close()"
            style="padding: 8px 24px; font-size: 14px; cursor: pointer; background: #6c757d; color: white; border: none; border-radius: 4px; margin-left: 8px;">
            Tutup
        </button>
    </div>

    <div class="kartu-container">
        <!-- Header Institusi -->
        <div class="header">
            <h2>Kartu Tanda Peserta Ujian</h2>
            <h3>{{ $peserta->jadwalUjian->tipe_ujian }} - Semester
                {{ $peserta->jadwalUjian->semester->nama_semester ?? '' }}</h3>
            <p>Politeknik Sains dan Teknologi</p>
        </div>

        <!-- Info Mahasiswa -->
        <div class="info-section">
            <div class="info-left">
                <table>
                    <tr>
                        <td>NIM</td>
                        <td>: {{ $riwayat->nim ?? '-' }}</td>
                    </tr>
                    <tr>
                        <td>Nama</td>
                        <td>: {{ $mahasiswa->nama_mahasiswa ?? '-' }}</td>
                    </tr>
                    <tr>
                        <td>Program Studi</td>
                        <td>: {{ $riwayat->programStudi->nama_program_studi ?? '-' }}</td>
                    </tr>
                    <tr>
                        <td>Tipe Ujian</td>
                        <td>:
                            <span class="badge-tipe badge-{{ strtolower($peserta->jadwalUjian->tipe_ujian) }}">
                                {{ $peserta->jadwalUjian->tipe_ujian }}
                            </span>
                        </td>
                    </tr>
                </table>
            </div>
            <div class="info-right">
                <div class="avatar-placeholder">
                    {{ strtoupper(substr($mahasiswa->nama_mahasiswa ?? 'M', 0, 1)) }}
                </div>
                <p style="font-size: 9pt; margin-top: 4px; color: #999;">Pas Foto 3x4</p>
            </div>
        </div>

        <!-- Tabel Daftar Ujian -->
        <h4 style="font-size: 12pt; margin-bottom: 5px;">Daftar Mata Kuliah Yang Diujikan:</h4>
        <table class="ujian-table">
            <thead>
                <tr>
                    <th width="5%">No</th>
                    <th>Kode MK</th>
                    <th>Mata Kuliah</th>
                    <th>Tanggal</th>
                    <th>Jam</th>
                </tr>
            </thead>
            <tbody>
                @foreach($semuaUjian as $index => $pu)
                    <tr>
                        <td class="text-center">{{ $index + 1 }}</td>
                        <td class="text-center">{{ $pu->jadwalUjian->kelasKuliah->mataKuliah->kode_mk ?? '-' }}</td>
                        <td>{{ $pu->jadwalUjian->kelasKuliah->mataKuliah->nama_mk ?? '-' }}</td>
                        <td class="text-center">{{ $pu->jadwalUjian->tanggal_ujian->format('d/m/Y') }}</td>
                        <td class="text-center">
                            {{ \Carbon\Carbon::parse($pu->jadwalUjian->jam_mulai)->format('H:i') }} -
                            {{ \Carbon\Carbon::parse($pu->jadwalUjian->jam_selesai)->format('H:i') }}
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>

        <!-- Signature -->
        <div class="footer">
            <div class="signature">
                <p>Makassar, {{ now()->format('d F Y') }}</p>
                <p>Kepala Bagian Akademik</p>
                <div class="line"></div>
                <p><strong>( ......................... )</strong></p>
            </div>
        </div>

        <!-- Catatan -->
        <div class="catatan">
            <strong>Catatan:</strong>
            <ol style="padding-left: 14px; margin-top: 4px;">
                <li>Kartu ini wajib dibawa saat mengikuti ujian.</li>
                <li>Mahasiswa yang tidak membawa kartu ujian tidak diperkenankan mengikuti ujian.</li>
                <li>Kartu ini berlaku untuk satu semester.</li>
            </ol>
        </div>
    </div>
</body>

</html>
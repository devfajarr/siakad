<?php

namespace App\Services;

use App\Models\KomponenBiaya;
use App\Models\Mahasiswa;
use App\Models\Pembayaran;
use App\Models\Tagihan;
use App\Models\TagihanItem;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class TagihanService
{
    /**
     * Generate nomor tagihan otomatis: INV/YYYY/NNNNN
     */
    public function generateNomorTagihan(): string
    {
        $tahun = date('Y');
        $prefix = "INV/{$tahun}/";
        $last = Tagihan::where('nomor_tagihan', 'like', $prefix . '%')
            ->orderByDesc('nomor_tagihan')
            ->lockForUpdate()
            ->first();

        $nextNumber = 1;
        if ($last) {
            $lastNumber = (int) substr($last->nomor_tagihan, strlen($prefix));
            $nextNumber = $lastNumber + 1;
        }

        return $prefix . str_pad($nextNumber, 5, '0', STR_PAD_LEFT);
    }

    /**
     * Generate nomor kuitansi otomatis: KWT/YYYY/NNNNN
     */
    public function generateNomorKuitansi(): string
    {
        $tahun = date('Y');
        $prefix = "KWT/{$tahun}/";
        $last = Pembayaran::where('nomor_kuitansi', 'like', $prefix . '%')
            ->orderByDesc('nomor_kuitansi')
            ->lockForUpdate()
            ->first();

        $nextNumber = 1;
        if ($last) {
            $lastNumber = (int) substr($last->nomor_kuitansi, strlen($prefix));
            $nextNumber = $lastNumber + 1;
        }

        return $prefix . str_pad($nextNumber, 5, '0', STR_PAD_LEFT);
    }

    /**
     * Terbitkan tagihan untuk 1 mahasiswa pada 1 semester.
     */
    public function terbitkanTagihan(Mahasiswa $mahasiswa, string $idSemester, ?string $idProdi = null): Tagihan
    {
        return DB::transaction(function () use ($mahasiswa, $idSemester, $idProdi) {
            // Ambil tahun angkatan dari riwayat pendidikan mahasiswa (4 digit pertama id_periode_masuk)
            $riwayat = $mahasiswa->riwayatPendidikans()->latest('id')->first();
            $tahunAngkatan = $riwayat ? substr($riwayat->id_periode_masuk, 0, 4) : null;

            // Ambil komponen biaya aktif yang berlaku untuk prodi + tahun angkatan mahasiswa
            $komponens = KomponenBiaya::active()
                ->where('kategori', KomponenBiaya::KATEGORI_PER_SEMESTER)
                ->forTarget($idProdi, $tahunAngkatan)
                ->get();

            if ($komponens->isEmpty()) {
                throw new \Exception("Tidak ada komponen biaya aktif untuk prodi ini.");
            }

            $totalTagihan = $komponens->sum('nominal_standar');

            $tagihan = Tagihan::create([
                'nomor_tagihan' => $this->generateNomorTagihan(),
                'id_mahasiswa' => $mahasiswa->id,
                'id_semester' => $idSemester,
                'total_tagihan' => $totalTagihan,
                'total_potongan' => 0,
                'total_dibayar' => 0,
                'status' => Tagihan::STATUS_BELUM_BAYAR,
            ]);

            // Buat item tagihan dari setiap komponen
            foreach ($komponens as $komponen) {
                TagihanItem::create([
                    'tagihan_id' => $tagihan->id,
                    'komponen_biaya_id' => $komponen->id,
                    'nominal' => $komponen->nominal_standar,
                    'potongan' => 0,
                ]);
            }

            Log::info("CRUD_CREATE: Tagihan diterbitkan", [
                'id' => $tagihan->id,
                'nomor' => $tagihan->nomor_tagihan,
                'mahasiswa_id' => $mahasiswa->id,
                'semester' => $idSemester,
                'total' => $totalTagihan,
                'jumlah_item' => $komponens->count(),
            ]);

            return $tagihan;
        });
    }

    /**
     * Terbitkan tagihan bulk untuk semua mahasiswa aktif di 1 semester.
     * @return int jumlah tagihan yang berhasil diterbitkan
     */
    public function terbitkanTagihanBulk(string $idSemester, ?string $idProdi = null): int
    {
        $mahasiswas = Mahasiswa::whereHas('riwayatPendidikans', function ($q) use ($idProdi) {
            if ($idProdi) {
                $q->where('id_prodi', $idProdi);
            }
        })
            ->whereDoesntHave('tagihans', function ($q) use ($idSemester) {
                $q->where('id_semester', $idSemester);
            })
            ->where('is_deleted_server', false)
            ->where('is_deleted_local', false)
            ->get();

        $count = 0;
        foreach ($mahasiswas as $mhs) {
            try {
                $this->terbitkanTagihan($mhs, $idSemester, $idProdi);
                $count++;
            } catch (\Exception $e) {
                Log::error("SYSTEM_ERROR: Gagal terbitkan tagihan bulk", [
                    'mahasiswa_id' => $mhs->id,
                    'message' => $e->getMessage(),
                ]);
            }
        }

        Log::info("SYNC_PUSH: Tagihan bulk diterbitkan", [
            'semester' => $idSemester,
            'prodi' => $idProdi,
            'count' => $count,
        ]);

        return $count;
    }

    /**
     * Verifikasi pembayaran (approve/reject).
     */
    public function verifikasiPembayaran(Pembayaran $pembayaran, bool $disetujui, ?string $catatan, User $admin): void
    {
        DB::transaction(function () use ($pembayaran, $disetujui, $catatan, $admin) {
            $pembayaran->update([
                'status_verifikasi' => $disetujui ? Pembayaran::STATUS_DISETUJUI : Pembayaran::STATUS_DITOLAK,
                'catatan_admin' => $catatan,
                'verified_by' => $admin->id,
                'verified_at' => now(),
                'nomor_kuitansi' => $disetujui ? $this->generateNomorKuitansi() : null,
            ]);

            // Recalculate tagihan
            $pembayaran->tagihan->recalculate();

            if (!$disetujui && $pembayaran->tagihan->mahasiswa && $pembayaran->tagihan->mahasiswa->user) {
                // Beri tahu mahasiswa pembayaran ditolak via database notification
                $userMahasiswa = $pembayaran->tagihan->mahasiswa->user;
                $userMahasiswa->notify(new \App\Notifications\PembayaranDitolakNotification($pembayaran));
            } elseif ($disetujui && $pembayaran->tagihan->mahasiswa && $pembayaran->tagihan->mahasiswa->user) {
                $userMahasiswa = $pembayaran->tagihan->mahasiswa->user;
                $userMahasiswa->notify(new \App\Notifications\PembayaranDisetujuiNotification($pembayaran));
            }

            $action = $disetujui ? 'DISETUJUI' : 'DITOLAK';
            Log::info("CRUD_UPDATE: Pembayaran {$action}", [
                'pembayaran_id' => $pembayaran->id,
                'tagihan_id' => $pembayaran->tagihan_id,
                'jumlah' => $pembayaran->jumlah_bayar,
                'admin' => $admin->name,
                'catatan' => $catatan,
            ]);
        });
    }

    /**
     * Cek apakah mahasiswa eligible untuk KRS (tagihan wajib KRS lunas).
     * Jika tidak ada tagihan, dianggap eligible (tagihan belum diterbitkan).
     */
    public function isKrsEligible(int $mahasiswaId, string $idSemester): bool
    {
        $tagihan = Tagihan::where('id_mahasiswa', $mahasiswaId)
            ->where('id_semester', $idSemester)
            ->first();

        // Tidak ada tagihan = eligible (admin belum terbitkan)
        if (!$tagihan) {
            return true;
        }

        // Cek komponen wajib KRS
        $totalWajib = $tagihan->items()
            ->whereHas('komponenBiaya', fn($q) => $q->where('is_wajib_krs', true))
            ->sum(DB::raw('nominal - potongan'));

        if ($totalWajib <= 0) {
            return true;
        }

        $totalDibayar = $tagihan->total_dibayar;

        return $totalDibayar >= $totalWajib;
    }

    /**
     * Cek apakah mahasiswa eligible untuk ujian (tagihan wajib ujian lunas).
     * Jika tidak ada tagihan, dianggap eligible.
     */
    public function isUjianEligible(int $mahasiswaId, string $idSemester): bool
    {
        $tagihan = Tagihan::where('id_mahasiswa', $mahasiswaId)
            ->where('id_semester', $idSemester)
            ->first();

        if (!$tagihan) {
            return true;
        }

        $totalWajib = $tagihan->items()
            ->whereHas('komponenBiaya', fn($q) => $q->where('is_wajib_ujian', true))
            ->sum(DB::raw('nominal - potongan'));

        if ($totalWajib <= 0) {
            return true;
        }

        return $tagihan->total_dibayar >= $totalWajib;
    }
}

<?php

namespace App\Http\Controllers\Mahasiswa;

use App\Http\Controllers\Controller;
use App\Models\Pembayaran;
use App\Models\Tagihan;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class KeuanganMahasiswaController extends Controller
{
    public function index(Request $request)
    {
        $mahasiswa = auth()->user()->mahasiswa;

        $tagihans = Tagihan::with(['semester', 'pembayarans'])
            ->where('id_mahasiswa', $mahasiswa->id)
            ->latest()
            ->get();

        return view('mahasiswa.keuangan.index', compact('tagihans', 'mahasiswa'));
    }

    public function show(Tagihan $tagihan)
    {
        $mahasiswa = auth()->user()->mahasiswa;

        // Authorization: mahasiswa hanya bisa lihat tagihan miliknya
        if ($tagihan->id_mahasiswa !== $mahasiswa->id) {
            abort(403, 'Anda tidak memiliki akses ke tagihan ini.');
        }

        $tagihan->load(['semester', 'items.komponenBiaya', 'pembayarans']);

        // Cek apakah ada pembayaran pending (blokir upload baru)
        $hasPending = $tagihan->pembayarans()->pending()->exists();

        return view('mahasiswa.keuangan.show', compact('tagihan', 'mahasiswa', 'hasPending'));
    }

    public function uploadBukti(Request $request, Tagihan $tagihan)
    {
        $mahasiswa = auth()->user()->mahasiswa;

        if ($tagihan->id_mahasiswa !== $mahasiswa->id) {
            abort(403);
        }

        if ($tagihan->status === Tagihan::STATUS_LUNAS) {
            return back()->with('error', 'Tagihan ini sudah lunas.');
        }

        // Cek duplikasi: tidak boleh upload jika ada pending
        if ($tagihan->pembayarans()->pending()->exists()) {
            return back()->with('error', 'Anda masih memiliki bukti bayar yang menunggu verifikasi.');
        }

        $request->validate([
            'jumlah_bayar' => 'required|numeric|min:1000',
            'tanggal_bayar' => 'required|date|before_or_equal:today',
            'bukti_bayar' => 'required|file|mimes:jpg,jpeg,png,pdf|max:2048',
        ], [
            'bukti_bayar.max' => 'Ukuran file maksimal 2MB.',
            'bukti_bayar.mimes' => 'Format file harus JPG, PNG, atau PDF.',
            'jumlah_bayar.min' => 'Jumlah bayar minimal Rp 1.000.',
        ]);

        try {
            // Simpan file ke private storage
            $file = $request->file('bukti_bayar');
            $path = $file->store(
                'private/bukti-bayar/' . date('Y') . '/' . date('m'),
                'local'
            );

            $pembayaran = Pembayaran::create([
                'tagihan_id' => $tagihan->id,
                'jumlah_bayar' => $request->jumlah_bayar,
                'tanggal_bayar' => $request->tanggal_bayar,
                'bukti_bayar' => $path,
                'status_verifikasi' => Pembayaran::STATUS_PENDING,
            ]);

            // Broadcast notifikasi ke seluruh Admin & Keuangan
            $notifiableUsers = \App\Models\User::role(['admin', 'Keuangan'])->get();
            \Illuminate\Support\Facades\Notification::send($notifiableUsers, new \App\Notifications\UploadPembayaranNotification($pembayaran));

            Log::info("CRUD_CREATE: Mahasiswa upload bukti bayar", [
                'mahasiswa_id' => $mahasiswa->id,
                'tagihan_id' => $tagihan->id,
                'jumlah' => $request->jumlah_bayar,
                'file' => $path,
            ]);

            return back()->with('success', 'Bukti pembayaran berhasil diunggah. Menunggu verifikasi admin.');
        } catch (\Exception $e) {
            Log::error("SYSTEM_ERROR: Gagal upload bukti bayar", ['message' => $e->getMessage(), 'trace' => $e->getTraceAsString()]);
            return back()->with('error', 'Terjadi kesalahan saat mengunggah bukti bayar.');
        }
    }

    public function downloadKuitansi(Pembayaran $pembayaran)
    {
        $mahasiswa = auth()->user()->mahasiswa;

        if ($pembayaran->tagihan->id_mahasiswa !== $mahasiswa->id) {
            abort(403);
        }

        if ($pembayaran->status_verifikasi !== Pembayaran::STATUS_DISETUJUI) {
            abort(404, 'Kuitansi belum tersedia.');
        }

        // Untuk saat ini redirect ke halaman cetak (bisa dikembangkan ke PDF nanti)
        return view('mahasiswa.keuangan.kuitansi', compact('pembayaran', 'mahasiswa'));
    }
}

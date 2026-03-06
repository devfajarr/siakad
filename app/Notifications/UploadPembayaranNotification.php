<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Notification;
use App\Models\Pembayaran;

class UploadPembayaranNotification extends Notification
{
    use Queueable;

    protected $pembayaran;

    public function __construct(Pembayaran $pembayaran)
    {
        $this->pembayaran = $pembayaran;
    }

    public function via($notifiable)
    {
        return ['database'];
    }

    public function toDatabase($notifiable)
    {
        $namaMahasiswa = $this->pembayaran->tagihan->mahasiswa->nama_mahasiswa ?? 'Seorang mahasiswa';

        return [
            'type' => 'upload_pembayaran',
            'title' => 'Validasi Pembayaran Baru',
            'message' => "{$namaMahasiswa} telah mengunggah bukti pembayaran sebesar Rp " . number_format($this->pembayaran->jumlah_bayar, 0, ',', '.'),
            'url' => route('admin.keuangan.pembayaran.index'),
            'pembayaran_id' => $this->pembayaran->id,
        ];
    }
}

<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Notification;
use App\Models\Pembayaran;

class PembayaranDisetujuiNotification extends Notification
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
        return [
            'type' => 'pembayaran_disetujui',
            'title' => 'Pembayaran Disetujui',
            'message' => "Pembayaran tagihan Anda sebesar Rp " . number_format($this->pembayaran->jumlah_bayar, 0, ',', '.') . " telah diverifikasi dan disetujui.",
            'url' => route('mahasiswa.keuangan.index'),
            'pembayaran_id' => $this->pembayaran->id,
        ];
    }
}

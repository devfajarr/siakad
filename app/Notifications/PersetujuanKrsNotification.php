<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class PersetujuanKrsNotification extends Notification
{
    use Queueable;

    protected $semesterId;
    protected $dosenNama;

    public function __construct($semesterId, $dosenNama)
    {
        $this->semesterId = $semesterId;
        $this->dosenNama = $dosenNama;
    }

    public function via($notifiable)
    {
        return ['database'];
    }

    public function toDatabase($notifiable)
    {
        return [
            'type' => 'persetujuan_krs',
            'title' => 'KRS Disetujui',
            'message' => "KRS Anda telah disetujui (ACC) oleh Dosen PA ({$this->dosenNama}).",
            'url' => route('mahasiswa.krs.index', ['id_semester' => $this->semesterId]),
            'semester_id' => $this->semesterId,
        ];
    }
}

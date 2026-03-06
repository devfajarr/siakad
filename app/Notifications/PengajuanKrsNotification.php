<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use App\Models\Mahasiswa;

class PengajuanKrsNotification extends Notification
{
    use Queueable;

    protected $mahasiswa;
    protected $semesterId;

    public function __construct(Mahasiswa $mahasiswa, $semesterId)
    {
        $this->mahasiswa = $mahasiswa;
        $this->semesterId = $semesterId;
    }

    public function via($notifiable)
    {
        return ['database'];
    }

    public function toDatabase($notifiable)
    {
        return [
            'type' => 'pengajuan_krs',
            'title' => 'Pengajuan KRS Baru',
            'message' => "Mahasiswa {$this->mahasiswa->nama_mahasiswa} ({$this->mahasiswa->nim}) telah mengajukan KRS.",
            'url' => route('dosen.perwalian.show', $this->mahasiswa->id),
            'mahasiswa_id' => $this->mahasiswa->id,
            'semester_id' => $this->semesterId,
        ];
    }
}

<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;
use Illuminate\Notifications\Messages\MailMessage;

class ProposalAssignedNotification extends Notification
{
    use Queueable;

    protected $proposal;

    public function __construct($proposal)
    {
        $this->proposal = $proposal;
    }

    // channel notifikasi
    public function via($notifiable)
    {
        return ['database']; // bisa tambah 'mail' kalau mau
    }

    // isi notifikasi database
   public function toDatabase($notifiable)
{
    return [
        'title'       => 'Penugasan Review Proposal',
        'message'     => 'Anda ditugaskan untuk mereview proposal: ' . $this->proposal->judul,
        'proposal_id' => $this->proposal->id,
        'judul'       => $this->proposal->judul,
        'pengusul'    => $this->proposal->nama_ketua,
        'url'         => route('reviewer.isi-review', $this->proposal->id),
    ];
}

    // OPTIONAL email
    /*
    public function toMail($notifiable)
    {
        return (new MailMessage)
            ->subject('Penugasan Review Proposal')
            ->line('Anda ditugaskan untuk mereview proposal:')
            ->line($this->proposal->judul)
            ->action('Beri Review', route('reviewer.isi-review', $this->proposal->id));
    }
    */
}

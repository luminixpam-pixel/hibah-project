<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;
use Illuminate\Notifications\Messages\MailMessage;

class ProposalAssigned extends Notification
{
    use Queueable;

    protected $proposal;

    public function __construct($proposal)
    {
        $this->proposal = $proposal;
    }

    // Channel notifikasi, bisa 'mail' atau 'database'
    public function via($notifiable)
    {
        return ['database', 'mail'];
    }

    // Notifikasi via email
    public function toMail($notifiable)
    {
        return (new MailMessage)
                    ->subject('Proposal Baru Ditugaskan')
                    ->line('Anda ditugaskan untuk mereview proposal: ' . $this->proposal->judul)
                    ->action('Lihat Proposal', url('/review/' . $this->proposal->id))
                    ->line('Terima kasih telah membantu proses review!');
    }

    // Notifikasi via database
    public function toDatabase($notifiable)
    {
        return [
            'proposal_id' => $this->proposal->id,
            'judul' => $this->proposal->judul,
            'message' => 'Anda ditugaskan untuk mereview proposal baru.'
        ];
    }
}

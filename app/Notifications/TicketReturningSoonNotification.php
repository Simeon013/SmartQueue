<?php

namespace App\Notifications;

use App\Models\Ticket;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class TicketReturningSoonNotification extends Notification implements ShouldQueue
{
    use Queueable;

    protected $ticket;

    /**
     * Create a new notification instance.
     *
     * @param Ticket $ticket
     * @return void
     */
    public function __construct(Ticket $ticket)
    {
        $this->ticket = $ticket;
    }

    /**
     * Get the notification's delivery channels.
     *
     * @param  mixed  $notifiable
     * @return array
     */
    public function via($notifiable)
    {
        // Pour l'instant, on utilise uniquement la notification en base de données
        // Vous pouvez ajouter d'autres canaux comme 'mail', 'sms', etc.
        return ['database'];
    }

    /**
     * Get the mail representation of the notification.
     *
     * @param  mixed  $notifiable
     * @return \Illuminate\Notifications\Messages\MailMessage
     */
    public function toMail($notifiable)
    {
        $url = route('public.ticket.status', [
            'queue_code' => $this->ticket->queue->code,
            'ticket_code' => $this->ticket->code_ticket
        ]);

        return (new MailMessage)
                    ->subject('Votre tour approche !')
                    ->line('Votre position dans la file d\'attente est maintenant proche.')
                    ->action('Voir mon ticket', $url)
                    ->line('Merci d\'utiliser notre service !');
    }

    /**
     * Get the array representation of the notification.
     *
     * @param  mixed  $notifiable
     * @return array
     */
    public function toArray($notifiable)
    {
        return [
            'ticket_id' => $this->ticket->id,
            'ticket_code' => $this->ticket->code_ticket,
            'queue_name' => $this->ticket->queue->name,
            'position' => $this->ticket->getPositionAttribute(),
            'message' => 'Votre position dans la file d\'attente est maintenant proche. Veuillez vous présenter bientôt.'
        ];
    }
}

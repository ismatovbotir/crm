<?php

namespace App\Notifications;

use App\Models\Support\Ticket;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class NewTicketNotification extends Notification
{
    use Queueable;

    public function __construct(public Ticket $ticket) {}

    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        $customer = $this->ticket->customer?->name ?? 'Клиент';
        $priority = match ($this->ticket->priority) {
            'critical' => 'Критичный',
            'high'     => 'Высокий',
            'medium'   => 'Средний',
            default    => 'Низкий',
        };

        return (new MailMessage)
            ->subject("[{$priority}] Новый тикет от {$customer}")
            ->greeting('Новое обращение в поддержку')
            ->line("**Клиент**: {$customer}")
            ->line("**Тема**: {$this->ticket->subject}")
            ->line("**Приоритет**: {$priority}")
            ->action('Открыть тикет', url("/admin/tickets/{$this->ticket->id}"))
            ->salutation('RSG CRM');
    }
}

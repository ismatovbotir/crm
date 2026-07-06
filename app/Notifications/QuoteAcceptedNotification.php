<?php

namespace App\Notifications;

use App\Models\Quote\Quote;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class QuoteAcceptedNotification extends Notification
{
    use Queueable;

    public function __construct(public Quote $quote) {}

    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        $customer = $this->quote->customer?->name ?? 'Клиент';

        return (new MailMessage)
            ->subject("КП {$this->quote->number} принято!")
            ->greeting("Отличная новость, {$notifiable->name}!")
            ->line("Клиент **{$customer}** принял КП **{$this->quote->number}** на сумму **{$this->quote->total} {$this->quote->currency}**.")
            ->line('Можно выставлять инвойс.')
            ->action('Открыть КП', url("/admin/quotes/{$this->quote->id}"))
            ->salutation('RSG CRM');
    }
}

<?php

namespace App\Notifications;

use App\Models\Quote\Quote;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class QuoteViewedNotification extends Notification
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
            ->subject("КП {$this->quote->number} просмотрено клиентом")
            ->greeting("Привет, {$notifiable->name}!")
            ->line("Клиент **{$customer}** открыл ваше КП **{$this->quote->number}**.")
            ->line('Это хороший момент для связи — клиент сейчас знакомится с предложением.')
            ->action('Открыть КП', url("/admin/quotes/{$this->quote->id}"))
            ->salutation('RSG CRM');
    }
}

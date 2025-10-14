<?php

namespace App\Notifications;

use Illuminate\Auth\Notifications\ResetPassword as BaseReset;
use Illuminate\Notifications\Messages\MailMessage;

class ResetPasswordNotification extends BaseReset
{
    public function toMail($notifiable)
    {
        $url = $this->resetUrl($notifiable);

        return (new MailMessage)
            ->subject('Reset hasła — StudentNoteDashboard')
            ->greeting('Cześć!')
            ->line('Otrzymaliśmy prośbę o reset hasła do Twojego konta.')
            ->action('Ustaw nowe hasło', $url)
            ->line('Jeśli to nie Ty inicjowałeś reset, zignoruj tę wiadomość — Twoje hasło pozostanie bez zmian.')
            ->salutation('Pozdrawiamy, Zespół StudentNoteDashboard');
    }
}


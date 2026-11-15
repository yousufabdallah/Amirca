<?php

namespace App\Notifications;

use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class ResetPasswordNotification extends Notification
{
    public function __construct(public string $token) {}

    public function via($notifiable): array
    {
        return ['mail'];
    }

    public function toMail($notifiable): MailMessage
    {
        $url = route('password.reset', ['token' => $this->token, 'email' => $notifiable->email]);

        return (new MailMessage)
            ->subject('استعادة كلمة المرور - '.setting('office_name', config('app.name')))
            ->greeting('مرحبًا '.$notifiable->name)
            ->line('تلقينا طلبًا لاستعادة كلمة المرور لحسابك.')
            ->action('تعيين كلمة مرور جديدة', $url)
            ->line('ينتهي هذا الرابط خلال 60 دقيقة. إذا لم تطلب ذلك فتجاهل هذه الرسالة.')
            ->salutation('مع التحية، '.setting('office_name', config('app.name')));
    }
}

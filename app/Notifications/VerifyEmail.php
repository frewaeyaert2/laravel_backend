<?php

namespace App\Notifications;

use Illuminate\Auth\Notifications\VerifyEmail as BaseVerifyEmail;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\URL;

class VerifyEmail extends BaseVerifyEmail
{
    protected function verificationUrl($notifiable)
{
    return URL::temporarySignedRoute(
        'verification.verify',
        Carbon::now()->addMinutes(60),
        [
            'id' => $notifiable->getKey(),
            'hash' => sha1($notifiable->getEmailForVerification()),
        ]
    );
}


    public function toMail($notifiable)
{
    $verificationUrl = $this->verificationUrl($notifiable);

    return (new MailMessage)
        ->subject('Verify Your Email Address')
        ->markdown('emails.verify-email', ['url' => $verificationUrl]);
}
}

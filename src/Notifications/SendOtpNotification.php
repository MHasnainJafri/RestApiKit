<?php

namespace Mhasnainjafri\RestApiKit\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class SendOtpNotification extends Notification
{
    use Queueable;

    public $otp;

    public function __construct($otp)
    {
        $this->otp = $otp;
    }

    public function via($notifiable)
    {
        return ['mail'];
    }

    public function toMail($notifiable)
    {
        return (new MailMessage)
            ->subject('Your OTP Code')
            ->line('Your OTP code for verification is:')
            ->line($this->otp)
            ->line('Thank you for using our application!');
    }

    public function toArray($notifiable)
    {
        return [
            'otp' => $this->otp,
        ];
    }
}

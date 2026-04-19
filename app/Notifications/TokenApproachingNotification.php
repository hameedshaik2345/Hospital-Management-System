<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;
use Illuminate\Notifications\Messages\MailMessage;

class TokenApproachingNotification extends Notification
{
    use Queueable;

    public $currentToken;
    public $patientToken;
    public $doctorName;
    public $tokensLeft;

    public function __construct($currentToken, $patientToken, $doctorName)
    {
        $this->currentToken = $currentToken;
        $this->patientToken = $patientToken;
        $this->doctorName = $doctorName;
        $this->tokensLeft = $patientToken - $currentToken;
    }

    public function via(object $notifiable): array
    {
        return ['mail']; // We handle SMS separately via SmsService
    }

    public function toMail(object $notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject("⏰ Your Turn is Approaching - MedFlow")
            ->greeting("Hello {$notifiable->name}!")
            ->line("This is a reminder from **MedFlow** about your appointment today.")
            ->line("---")
            ->line("🏥 **Doctor:** {$this->doctorName}")
            ->line("📋 **Currently Serving Token:** #{$this->currentToken}")
            ->line("🎟️ **Your Token Number:** #{$this->patientToken}")
            ->line("⏳ **Tokens Remaining Before Your Turn:** {$this->tokensLeft}")
            ->line("---")
            ->line("Please head to the clinic now to avoid missing your turn.")
            ->action('View Your Appointment', url('/patient/dashboard'))
            ->line("Thank you for choosing MedFlow!")
            ->salutation("Best regards,\nMedFlow Healthcare System");
    }
}

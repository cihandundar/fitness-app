<?php

namespace App\Notifications;

use App\Models\Appointment;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class AppointmentReminderNotification extends Notification implements ShouldQueue
{
    use Queueable;

    /**
     * The number of times the job may be attempted.
     */
    public int $tries = 3;

    /**
     * Create a new notification instance.
     */
    public function __construct(
        public Appointment $appointment,
        public string $recipientType // 'user' or 'trainer'
    ) {
        $this->onQueue('notifications');
    }

    /**
     * Get the notification's delivery channels.
     *
     * @return array<int, string>
     */
    public function via(object $notifiable): array
    {
        return ['mail', 'database'];
    }

    /**
     * Get the mail representation of the notification.
     */
    public function toMail(object $notifiable): MailMessage
    {
        $appointmentTime = $this->appointment->start_time->format('d.m.Y H:i');
        $isTrainer = $this->recipientType === 'trainer';

        $greeting = $isTrainer
            ? "Merhaba {$notifiable->name},"
            : "Merhaba {$notifiable->name},";

        $message = $isTrainer
            ? "Randevunuz {$appointmentTime}'de başlayacak. Antrenoranınız: {$this->appointment->user->name}"
            : "Randevunuz {$appointmentTime}'de başlayacak. Eğitmeniniz: {$this->appointment->trainer->name}";

        return (new MailMessage)
            ->subject('📅 Randevu Hatırlatması')
            ->greeting($greeting)
            ->line($message)
            ->line('Randevu için hazırlanın!')
            ->action('Randevuyu Görüntüle', url('/appointments/' . $this->appointment->id))
            ->line('Teşekkürler!');
    }

    /**
     * Get the array representation of the notification.
     *
     * @return array<string, mixed>
     */
    public function toArray(object $notifiable): array
    {
        return [
            'appointment_id' => $this->appointment->id,
            'recipient_type' => $this->recipientType,
            'start_time' => $this->appointment->start_time->toIso8601String(),
            'trainer_name' => $this->appointment->trainer->name,
            'user_name' => $this->appointment->user->name,
            'message' => 'Randevunuz ' . $this->appointment->start_time->format('d.m.Y H:i') . '\'de başlayacak.',
        ];
    }
}

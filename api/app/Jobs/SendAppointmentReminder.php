<?php

namespace App\Jobs;

use App\Models\Appointment;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Notification;
use App\Notifications\AppointmentReminderNotification;
use Illuminate\Support\Facades\Log;
use Exception;

class SendAppointmentReminder implements ShouldQueue
{
    use Queueable;

    /**
     * The number of times the job may be attempted.
     */
    public int $tries = 3;

    /**
     * Create a new job instance.
     */
    public function __construct(
        public Appointment $appointment,
        public string $recipientType // 'user' or 'trainer'
    ) {
        $this->onQueue('notifications');
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        try {
            $recipient = $this->recipientType === 'trainer'
                ? $this->appointment->trainer
                : $this->appointment->user;

            if (!$recipient) {
                Log::warning('Appointment reminder: missing recipient', [
                    'appointment_id' => $this->appointment->id,
                    'type' => $this->recipientType
                ]);
                return;
            }

            // Database notification
            $recipient->notify(new AppointmentReminderNotification(
                $this->appointment,
                $this->recipientType
            ));

            // Email gönderimi (opsiyonel - kullanıcı email tercihine göre)
            if ($recipient->email_notifications ?? true) {
                Mail::raw(
                    view('emails.appointment-reminder', [
                        'recipient' => $recipient,
                        'appointment' => $this->appointment,
                        'type' => $this->recipientType
                    ])->render(),
                    function ($message) use ($recipient) {
                        $message->to($recipient->email)
                            ->subject('📅 Randevu Hatırlatması');
                    }
                );
            }

            Log::info('Appointment reminder sent', [
                'appointment_id' => $this->appointment->id,
                'recipient_type' => $this->recipientType,
                'recipient_id' => $recipient->id
            ]);
        } catch (Exception $e) {
            Log::error('Failed to send appointment reminder', [
                'appointment_id' => $this->appointment->id,
                'error' => $e->getMessage()
            ]);
            throw $e;
        }
    }

    /**
     * Handle a job failure.
     */
    public function failed(Exception $exception): void
    {
        Log::error('Appointment reminder job failed permanently', [
            'appointment_id' => $this->appointment->id,
            'error' => $exception->getMessage()
        ]);
    }
}

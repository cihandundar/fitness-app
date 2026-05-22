<?php

namespace App\Jobs;

use App\Models\User;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Log;
use Exception;

class SendWelcomeEmail implements ShouldQueue
{
    use Queueable;

    /**
     * The number of times the job may be attempted.
     */
    public int $tries = 3;

    /**
     * The maximum number of seconds the job can run.
     */
    public int $timeout = 30;

    /**
     * Create a new job instance.
     */
    public function __construct(
        public User $user,
        public ?string $temporaryPassword = null
    ) {
        $this->onQueue('emails');
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        try {
            // Email gönderimi
            // Not: Gerçek email template'i oluşturulmalı
            Mail::raw(
                view('emails.welcome', [
                    'user' => $this->user,
                    'temporaryPassword' => $this->temporaryPassword
                ])->render(),
                function ($message) {
                    $message->to($this->user->email)
                        ->subject('FitLife App\'e Hoş Geldiniz! 🏋️');
                }
            );

            Log::info('Welcome email sent', ['user_id' => $this->user->id]);
        } catch (Exception $e) {
            Log::error('Failed to send welcome email', [
                'user_id' => $this->user->id,
                'error' => $e->getMessage()
            ]);
            throw $e; // Retry mekanizması için
        }
    }

    /**
     * Handle a job failure.
     */
    public function failed(Exception $exception): void
    {
        Log::error('Welcome email job failed permanently', [
            'user_id' => $this->user->id,
            'error' => $exception->getMessage()
        ]);
    }
}

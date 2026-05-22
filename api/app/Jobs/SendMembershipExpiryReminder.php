<?php

namespace App\Jobs;

use App\Models\UserMembership;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Log;
use Exception;

class SendMembershipExpiryReminder implements ShouldQueue
{
    use Queueable;

    /**
     * The number of times the job may be attempted.
     */
    public int $tries = 3;

    /**
     * The number of days before expiry to send reminder.
     */
    public int $daysBeforeExpiry;

    /**
     * Create a new job instance.
     */
    public function __construct(
        public UserMembership $membership,
        int $daysBeforeExpiry = 3
    ) {
        $this->daysBeforeExpiry = $daysBeforeExpiry;
        $this->onQueue('emails');
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        try {
            $user = $this->membership->user;
            $plan = $this->membership->plan;

            if (!$user || !$plan) {
                Log::warning('Membership expiry reminder: missing relationship', [
                    'membership_id' => $this->membership->id
                ]);
                return;
            }

            // Email gönderimi
            Mail::raw(
                view('emails.membership-expiry', [
                    'user' => $user,
                    'membership' => $this->membership,
                    'plan' => $plan,
                    'daysLeft' => $this->daysBeforeExpiry
                ])->render(),
                function ($message) use ($user) {
                    $message->to($user->email)
                        ->subject('⏰ Üyeliğiniz Yakında Sona Eriyor');
                }
            );

            Log::info('Membership expiry reminder sent', [
                'user_id' => $user->id,
                'membership_id' => $this->membership->id,
                'days_before' => $this->daysBeforeExpiry
            ]);
        } catch (Exception $e) {
            Log::error('Failed to send membership expiry reminder', [
                'membership_id' => $this->membership->id,
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
        Log::error('Membership expiry reminder job failed permanently', [
            'membership_id' => $this->membership->id,
            'error' => $exception->getMessage()
        ]);
    }
}

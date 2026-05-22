<?php

namespace App\Jobs;

use App\Models\Payment;
use App\Models\UserMembership;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Facades\Log;
use Exception;

class ProcessPaymentWebhook implements ShouldQueue
{
    use Queueable;

    /**
     * The number of times the job may be attempted.
     * Webhook retry önemli, 5 deneme
     */
    public int $tries = 5;

    /**
     * Retry delay (exponential backoff)
     */
    public int $backoff = 10; // 10sn, 20sn, 40sn, 80sn, 160sn

    /**
     * Create a new job instance.
     */
    public function __construct(
        public array $payload,
        public string $gateway // 'iyzico', 'stripe', 'paytr'
    ) {
        $this->onQueue('payments');
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        try {
            match ($this->gateway) {
                'iyzico' => $this->processIyzicoWebhook(),
                'stripe' => $this->processStripeWebhook(),
                'paytr' => $this->processPaytrWebhook(),
                default => throw new \Exception("Unknown payment gateway: {$this->gateway}")
            };

            Log::info('Payment webhook processed', [
                'gateway' => $this->gateway,
                'payload' => $this->payload
            ]);

        } catch (Exception $e) {
            Log::error('Failed to process payment webhook', [
                'gateway' => $this->gateway,
                'error' => $e->getMessage(),
                'payload' => $this->payload
            ]);
            throw $e;
        }
    }

    /**
     * Iyzico webhook işle
     */
    protected function processIyzicoWebhook(): void
    {
        // Iyzico signature verification
        // $signature = $this->payload['signature'] ?? '';
        // if (!$this->verifyIyzicoSignature($signature, $this->payload)) {
        //     throw new Exception('Invalid iyzico signature');
        // }

        $paymentStatus = $this->payload['paymentStatus'] ?? null;

        if ($paymentStatus === 'SUCCESS') {
            $this->activateMembership();
        } elseif ($paymentStatus === 'FAILURE') {
            $this->markPaymentAsFailed();
        }
    }

    /**
     * Stripe webhook işle
     */
    protected function processStripeWebhook(): void
    {
        $eventType = $this->payload['type'] ?? '';

        match ($eventType) {
            'payment_intent.succeeded' => $this->activateMembership(),
            'payment_intent.payment_failed' => $this->markPaymentAsFailed(),
            default => Log::info('Unhandled Stripe event', ['event_type' => $eventType])
        };
    }

    /**
     * PayTR webhook işle
     */
    protected function processPaytrWebhook(): void
    {
        $status = $this->payload['status'] ?? null;

        if ($status === 'success') {
            $this->activateMembership();
        } elseif ($status === 'failed') {
            $this->markPaymentAsFailed();
        }
    }

    /**
     * Üyeliği aktifleştir
     */
    protected function activateMembership(): void
    {
        $paymentId = $this->payload['payment_id'] ?? null;
        $membershipId = $this->payload['membership_id'] ?? null;

        if (!$paymentId || !$membershipId) {
            throw new Exception('Missing payment_id or membership_id in webhook payload');
        }

        // Payment'ı güncelle
        $payment = Payment::find($paymentId);
        if (!$payment) {
            throw new Exception("Payment not found: {$paymentId}");
        }

        $payment->update([
            'status' => 'completed',
            'metadata' => array_merge($payment->metadata ?? [], [
                'webhook_processed_at' => now()->toIso8601String(),
                'gateway' => $this->gateway
            ])
        ]);

        // Üyeliği aktifleştir
        $membership = UserMembership::find($membershipId);
        if ($membership) {
            $membership->update([
                'status' => 'active',
                'start_date' => $membership->start_date ?? now(),
            ]);

            // Welcome email gönder
            \App\Jobs\SendWelcomeEmail::dispatch(
                $membership->user
            )->delay(now()->addMinutes(1));
        }
    }

    /**
     * Payment'ı başarısız olarak işaretle
     */
    protected function markPaymentAsFailed(): void
    {
        $paymentId = $this->payload['payment_id'] ?? null;

        if (!$paymentId) {
            throw new Exception('Missing payment_id in webhook payload');
        }

        $payment = Payment::find($paymentId);
        if ($payment) {
            $payment->update([
                'status' => 'failed',
                'metadata' => array_merge($payment->metadata ?? [], [
                    'webhook_processed_at' => now()->toIso8601String(),
                    'gateway' => $this->gateway,
                    'failure_reason' => $this->payload['failure_reason'] ?? 'Unknown'
                ])
            ]);
        }
    }

    /**
     * Handle a job failure.
     */
    public function failed(Exception $exception): void
    {
        Log::error('Payment webhook processing failed permanently', [
            'gateway' => $this->gateway,
            'payload' => $this->payload,
            'error' => $exception->getMessage()
        ]);

        // Notification gönder - manuel müdahale gerekebilir
    }
}

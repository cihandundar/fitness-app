<?php

namespace App\Services;

use App\Models\Payment;
use App\Models\UserMembership;
use App\Models\MembershipPlan;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class PaymentService
{
    /**
     * Üyelik için ödeme başlatır.
     *
     * @param int $userId
     * @param int $membershipPlanId
     * @param string $paymentMethod
     * @return array
     */
    public function initializeMembershipPayment(int $userId, int $membershipPlanId, string $paymentMethod = 'iyzico'): array
    {
        // Mevcut aktif veya bekleyen üyelik kontrolü
        $existingMembership = UserMembership::where('user_id', $userId)
            ->whereIn('status', [UserMembership::STATUS_ACTIVE, UserMembership::STATUS_PENDING])
            ->first();

        if ($existingMembership) {
            throw new \Exception(
                $existingMembership->status === UserMembership::STATUS_PENDING
                    ? 'Zaten onay bekleyen bir üyeliğiniz bulunuyor.'
                    : 'Zaten aktif bir üyeliğiniz bulunuyor. Yeni üyelik alabilmeniz için mevcut üyeliğinizin sona ermesini bekleyin.'
            );
        }

        $plan = MembershipPlan::findOrFail($membershipPlanId);

        // Mock - Gerçek entegrasyonda burada iyzico API çağrısı olacak
        $paymentSession = [
            'payment_page_url' => null, // Mock: redirect URL yok
            'payment_token' => 'mock_token_' . uniqid(),
            'conversation_id' => 'conv_' . uniqid(),
        ];

        // Session bilgilerini sakla (gerçek entegrasyonda cache'e alınacak)
        session([
            'payment_pending' => [
                'user_id' => $userId,
                'plan_id' => $membershipPlanId,
                'amount' => $plan->price,
                'payment_method' => $paymentMethod,
                'conversation_id' => $paymentSession['conversation_id'],
            ]
        ]);

        Log::info('Payment initialized', [
            'user_id' => $userId,
            'plan_id' => $membershipPlanId,
            'amount' => $plan->price,
            'method' => $paymentMethod,
        ]);

        return $paymentSession;
    }

    /**
     * Ödeme callback'ini işler.
     *
     * @param array $callbackData
     * @return Payment
     */
    public function handlePaymentCallback(array $callbackData): Payment
    {
        return DB::transaction(function () use ($callbackData) {
            $pendingData = session('payment_pending');

            if (!$pendingData) {
                throw new \Exception('Bekleyen ödeme bulunamadı.');
            }

            $userId = $pendingData['user_id'];
            $planId = $pendingData['plan_id'];
            $amount = $pendingData['amount'];
            $conversationId = $pendingData['conversation_id'];

            $plan = MembershipPlan::findOrFail($planId);

            // Mock: Başarılı ödeme simülasyonu
            $isSuccessful = $callbackData['status'] ?? 'success';

            if ($isSuccessful === 'success') {
                // Üyeliği oluştur
                $userMembership = $this->createUserMembership($userId, $plan);

                // Ödeme kaydı oluştur
                $payment = Payment::create([
                    'user_id' => $userId,
                    'user_membership_id' => $userMembership->id,
                    'amount' => $amount,
                    'payment_method' => $pendingData['payment_method'],
                    'status' => 'completed',
                    'transaction_id' => 'mock_txn_' . uniqid(),
                    'payment_details' => [
                        'conversation_id' => $conversationId,
                        'installment' => 1,
                        'paid_price' => $amount,
                    ],
                ]);

                // Session temizle
                session()->forget('payment_pending');

                Log::info('Payment completed successfully', [
                    'payment_id' => $payment->id,
                    'user_id' => $userId,
                    'amount' => $amount,
                ]);

                return $payment->load('userMembership');
            } else {
                // Başarısız ödeme
                $payment = Payment::create([
                    'user_id' => $userId,
                    'user_membership_id' => null,
                    'amount' => $amount,
                    'payment_method' => $pendingData['payment_method'],
                    'status' => 'failed',
                    'transaction_id' => null,
                    'payment_details' => [
                        'error_message' => $callbackData['error_message'] ?? 'Ödeme başarısız',
                        'conversation_id' => $conversationId,
                    ],
                ]);

                session()->forget('payment_pending');

                Log::warning('Payment failed', [
                    'payment_id' => $payment->id,
                    'user_id' => $userId,
                    'amount' => $amount,
                ]);

                return $payment;
            }
        });
    }

    /**
     * Kullanıcı üyeliğini oluşturur.
     *
     * @param int $userId
     * @param MembershipPlan $plan
     * @return UserMembership
     */
    protected function createUserMembership(int $userId, MembershipPlan $plan): UserMembership
    {
        // Kullanıcının mevcut aktif/pending üyeliklerini expired yap
        UserMembership::where('user_id', $userId)
            ->whereIn('status', [UserMembership::STATUS_ACTIVE, UserMembership::STATUS_PENDING])
            ->update(['status' => UserMembership::STATUS_EXPIRED]);

        $durationDays = $plan->duration_days ?? 30; // Default 30 gün

        return UserMembership::create([
            'user_id' => $userId,
            'membership_plan_id' => $plan->id,
            'start_date' => null, // Onaylandığında belirlenecek
            'end_date' => null,   // Onaylandığında belirlenecek
            'remaining_sessions' => $plan->session_count ?? null,
            'remaining_days' => $durationDays,
            'total_days' => $durationDays,
            'status' => UserMembership::STATUS_PENDING, // Admin onayı beklenecek
            'last_check_in' => null,
        ]);
    }

    /**
     * Ödeme durumunu sorgular (iyzico için).
     *
     * @param string $paymentId
     * @return array
     */
    public function queryPaymentStatus(string $paymentId): array
    {
        // Mock implementation
        return [
            'status' => 'success',
            'payment_id' => $paymentId,
        ];
    }

    /**
     * İade işlemi.
     *
     * @param Payment $payment
     * @param float|null $amount
     * @return bool
     */
    public function refundPayment(Payment $payment, ?float $amount = null): bool
    {
        // Mock implementation
        $payment->update([
            'status' => 'refunded',
        ]);

        Log::info('Payment refunded', [
            'payment_id' => $payment->id,
            'amount' => $amount ?? $payment->amount,
        ]);

        return true;
    }
}

<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\PaymentResource;
use App\Models\Payment;
use App\Services\PaymentService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class PaymentController extends Controller
{
    protected PaymentService $paymentService;

    public function __construct(PaymentService $paymentService)
    {
        $this->paymentService = $paymentService;
    }
    /**
     * Tüm ödemeleri listeler (Admin için).
     */
    public function index()
    {
        $payments = Payment::with('user:id,name,email')
            ->orderBy('created_at', 'desc')
            ->get();

        return response()->json([
            'status' => 'success',
            'data' => PaymentResource::collection($payments)
        ]);
    }

    /**
     * Tek bir ödeme detayını gösterir.
     */
    public function show(Payment $payment)
    {
        $payment->load('user', 'userMembership');
        return response()->json([
            'status' => 'success',
            'data' => new PaymentResource($payment)
        ]);
    }

    /**
     * Ödeme durumunu günceller (Manuel müdahale için).
     */
    public function update(Request $request, Payment $payment)
    {
        $request->validate([
            'status' => 'required|in:pending,completed,failed,refunded',
        ]);

        $payment->update([
            'status' => $request->status
        ]);

        return response()->json([
            'status' => 'success',
            'message' => 'Ödeme durumu güncellendi.',
            'data' => new PaymentResource($payment->load('userMembership'))
        ]);
    }

    /**
     * Üyelik ödemesini başlatır.
     */
    public function initializeMembership(Request $request)
    {
        $request->validate([
            'membership_plan_id' => 'required|exists:membership_plans,id',
            'payment_method' => 'required|in:iyzico,stripe,cash',
        ]);

        $userId = Auth::id();
        $planId = $request->membership_plan_id;
        $paymentMethod = $request->payment_method;

        try {
            $result = $this->paymentService->initializeMembershipPayment(
                $userId,
                $planId,
                $paymentMethod
            );

            return response()->json([
                'status' => 'success',
                'message' => 'Ödeme başlatıldı.',
                'data' => [
                    'payment_token' => $result['payment_token'],
                    'conversation_id' => $result['conversation_id'],
                    // Mock modunda payment_page_url yok, gerçek entegrasyonda olacak
                    'payment_page_url' => $result['payment_page_url'] ?? null,
                    'is_mock' => true,
                ]
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Ödeme başlatılamadı: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Ödeme callback'ini işler.
     */
    public function callback(Request $request)
    {
        // Mock modunda basit callback
        $callbackData = [
            'status' => $request->status ?? 'success',
            'error_message' => $request->error_message ?? null,
        ];

        try {
            $payment = $this->paymentService->handlePaymentCallback($callbackData);

            return response()->json([
                'status' => 'success',
                'message' => $payment->status === 'completed'
                    ? 'Ödeme başarılı! Üyeliğiniz aktif.'
                    : 'Ödeme başarısız oldu.',
                'data' => new PaymentResource($payment->load('userMembership'))
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Ödeme işlenirken hata oluştu: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Mock test ödemesi yapar (Sadece development için).
     */
    public function mockPayment(Request $request)
    {
        if (config('app.env') !== 'local' && config('app.env') !== 'development') {
            return response()->json([
                'status' => 'error',
                'message' => 'Bu endpoint sadece development ortamında çalışır.'
            ], 403);
        }

        $request->validate([
            'membership_plan_id' => 'required|exists:membership_plans,id',
            'simulate' => 'in:success,failed',
        ]);

        $userId = Auth::id();
        $planId = $request->membership_plan_id;
        $simulate = $request->simulate ?? 'success';

        // Zaten bekleyen bir ödeme işlemi varsa engelle
        if (session('payment_pending')) {
            return response()->json([
                'status' => 'error',
                'message' => 'Zaten işlenen bir ödeme isteğiniz bulunuyor. Lütfen bekleyin.'
            ], 429);
        }

        // Session data oluştur
        $plan = \App\Models\MembershipPlan::findOrFail($planId);
        session([
            'payment_pending' => [
                'user_id' => $userId,
                'plan_id' => $planId,
                'amount' => $plan->price,
                'payment_method' => 'mock',
                'conversation_id' => 'mock_conv_' . uniqid(),
            ]
        ]);

        $callbackData = [
            'status' => $simulate,
            'error_message' => $simulate === 'failed' ? 'Mock hata simülasyonu' : null,
        ];

        try {
            $payment = $this->paymentService->handlePaymentCallback($callbackData);

            return response()->json([
                'status' => 'success',
                'message' => $payment->status === 'completed'
                    ? 'Mock ödeme başarılı! Üyeliğiniz aktif.'
                    : 'Mock ödeme başarısız simüle edildi.',
                'data' => new PaymentResource($payment->load('userMembership'))
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Kullanıcının kendi ödemelerini listeler.
     */
    public function myPayments()
    {
        $payments = Payment::where('user_id', Auth::id())
            ->with('userMembership')
            ->orderBy('created_at', 'desc')
            ->get();

        return response()->json([
            'status' => 'success',
            'data' => PaymentResource::collection($payments)
        ]);
    }
}

<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\PaymentResource;
use App\Models\Payment;
use Illuminate\Http\Request;

class PaymentController extends Controller
{
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
}

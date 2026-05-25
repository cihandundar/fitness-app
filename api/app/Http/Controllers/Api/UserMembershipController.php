<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\UserMembership;
use Illuminate\Http\Request;

class UserMembershipController extends Controller
{
    public function index()
    {
        $memberships = UserMembership::with(['user', 'plan'])
            ->orderBy('created_at', 'desc')
            ->get();

        return response()->json([
            'success' => true,
            'data' => $memberships
        ]);
    }

    public function store(Request $request)
    {
        $request->validate([
            'membership_plan_id' => 'required|exists:membership_plans,id',
        ]);

        // Aktif veya pending üyelik kontrolü
        $existingMembership = UserMembership::where('user_id', auth()->id())
            ->whereIn('status', ['active', 'pending'])
            ->first();

        if ($existingMembership) {
            return response()->json([
                'success' => false,
                'message' => $existingMembership->status === 'pending'
                    ? 'Onay bekleyen bir üyeliğiniz bulunuyor.'
                    : 'Zaten aktif bir üyeliğiniz bulunuyor.'
            ], 400);
        }

        $plan = \App\Models\MembershipPlan::find($request->membership_plan_id);

        $membership = UserMembership::create([
            'user_id' => auth()->id(),
            'membership_plan_id' => $plan->id,
            'start_date' => null, // Onayda set edilecek
            'end_date' => null,
            'remaining_sessions' => $plan->session_count,
            'remaining_days' => $plan->duration_days ?? 0,
            'total_days' => $plan->duration_days ?? 0,
            'status' => 'pending', // Onay bekliyor
            'last_check_in' => now(),
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Üyelik başvurunuz alındı.',
            'data' => $membership->load('plan')
        ], 201);
    }

    public function update(Request $request, UserMembership $userMembership)
    {
        $request->validate([
            'status' => 'required|in:active,expired,cancelled',
        ]);

        $userMembership->update(['status' => $request->status]);

        return response()->json([
            'success' => true,
            'message' => 'Durum güncellendi.',
            'data' => $userMembership
        ]);
    }

    public function show(UserMembership $userMembership)
    {
        return response()->json([
            'success' => true,
            'data' => $userMembership->load(['user', 'plan', 'approvedBy'])
        ]);
    }

    public function destroy(UserMembership $userMembership)
    {
        $userMembership->delete();

        return response()->json([
            'success' => true,
            'message' => 'Üyelik silindi.'
        ]);
    }

    /**
     * Onay bekleyen üyelikleri listeler (Admin için).
     */
    public function pending()
    {
        $memberships = UserMembership::with(['user', 'plan'])
            ->pending()
            ->orderBy('created_at', 'desc')
            ->get();

        return response()->json([
            'success' => true,
            'data' => $memberships
        ]);
    }

    /**
     * Üyeliği onaylar (Admin için).
     */
    public function approve(Request $request, UserMembership $userMembership)
    {
        if ($userMembership->status !== UserMembership::STATUS_PENDING) {
            return response()->json([
                'success' => false,
                'message' => 'Sadece onay bekleyen üyelikler onaylanabilir.'
            ], 400);
        }

        // Onaylandığında başlangıç ve bitiş tarihlerini güncelle
        $startDate = now();
        $plan = $userMembership->plan;
        $durationDays = $userMembership->total_days ?? ($plan->duration_days ?? 30);
        $endDate = $startDate->copy()->addDays($durationDays);

        $userMembership->update([
            'status' => UserMembership::STATUS_ACTIVE,
            'start_date' => $startDate,
            'end_date' => $endDate,
            'approved_by' => auth()->id(),
            'approved_at' => now(),
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Üyelik onaylandı ve aktif edildi.',
            'data' => $userMembership->load('user', 'plan', 'approvedBy')
        ]);
    }

    /**
     * Üyeliği reddeder (Admin için).
     */
    public function reject(Request $request, UserMembership $userMembership)
    {
        $request->validate([
            'reason' => 'required|string|max:500',
        ]);

        if ($userMembership->status !== UserMembership::STATUS_PENDING) {
            return response()->json([
                'success' => false,
                'message' => 'Sadece onay bekleyen üyelikler reddedilebilir.'
            ], 400);
        }

        $userMembership->update([
            'status' => UserMembership::STATUS_REJECTED,
            'approved_by' => auth()->id(),
            'approved_at' => now(),
            'rejection_reason' => $request->reason,
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Üyelik reddedildi.',
            'data' => $userMembership->load('user', 'plan', 'approvedBy')
        ]);
    }

    /**
     * Kullanıcının kendi üyeliğini gösterir.
     */
    public function myMembership()
    {
        $membership = UserMembership::with(['plan', 'approvedBy'])
            ->where('user_id', auth()->id())
            ->whereIn('status', [UserMembership::STATUS_ACTIVE, UserMembership::STATUS_PENDING])
            ->orderBy('created_at', 'desc')
            ->first();

        return response()->json([
            'success' => true,
            'data' => $membership
        ]);
    }

    /**
     * Kullanıcı kendi üyeliğini iptal eder.
     */
    public function cancelMyMembership()
    {
        $membership = UserMembership::where('user_id', auth()->id())
            ->whereIn('status', [UserMembership::STATUS_ACTIVE, UserMembership::STATUS_PENDING])
            ->first();

        if (!$membership) {
            return response()->json([
                'success' => false,
                'message' => 'İptal edilebilir aktif veya bekleyen üyelik bulunamadı.'
            ], 404);
        }

        $membership->update([
            'status' => UserMembership::STATUS_EXPIRED,
            'rejection_reason' => 'Kullanıcı tarafından iptal edildi'
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Üyeliğiniz iptal edildi.',
            'data' => $membership->load('user', 'plan')
        ]);
    }
}

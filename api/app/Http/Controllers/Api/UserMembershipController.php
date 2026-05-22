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
            'user_id' => 'required|exists:users,id',
            'plan_id' => 'required|exists:membership_plans,id',
        ]);

        $plan = \App\Models\MembershipPlan::find($request->plan_id);

        $startDate = now();
        $endDate = $plan->duration_days ? $startDate->copy()->addDays($plan->duration_days) : null;

        $membership = UserMembership::create([
            'user_id' => $request->user_id,
            'membership_plan_id' => $plan->id,
            'start_date' => $startDate,
            'end_date' => $endDate,
            'remaining_sessions' => $plan->session_count,
            'remaining_days' => $plan->duration_days ?? 0,
            'total_days' => $plan->duration_days ?? 0,
            'status' => 'active',
            'last_check_in' => now(),
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Üyelik başarıyla oluşturuldu.',
            'data' => $membership
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

    public function destroy(UserMembership $userMembership)
    {
        $userMembership->delete();

        return response()->json([
            'success' => true,
            'message' => 'Üyelik silindi.'
        ]);
    }
}

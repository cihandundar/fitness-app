<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\UserMembership;
use App\Models\MembershipPlan;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;

class AuthController extends Controller
{
    // kayıt ol
    public function register(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|unique:users',
            'password' => 'required|string|min:8|confirmed',
            'membership_plan_id' => 'nullable|exists:membership_plans,id',
        ]);

        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => Hash::make($request->password),
            'role' => 'user',
        ]);

        // Eğer üyelik planı seçilmişse
        if ($request->has('membership_plan_id') && $request->membership_plan_id) {
            $plan = MembershipPlan::find($request->membership_plan_id);

            if ($plan) {
                $startDate = now();
                $endDate = $plan->duration_days ? $startDate->copy()->addDays($plan->duration_days) : null;

                UserMembership::create([
                    'user_id' => $user->id,
                    'membership_plan_id' => $plan->id,
                    'start_date' => $startDate,
                    'end_date' => $endDate,
                    'remaining_sessions' => $plan->session_count,
                    'remaining_days' => $plan->duration_days ?? 0,
                    'total_days' => $plan->duration_days ?? 0,
                    'status' => 'active',
                    'last_check_in' => now(),
                ]);
            }
        }

        $token = $user->createToken('auth_token')->plainTextToken;

        return response()->json([
            'user' => $user,
            'token' => $token,
        ], 201);
    }

    // giriş yap
    public function login(Request $request)
    {
        $request->validate([
            'email' => 'required|email',
            'password' => 'required',
        ]);

        $user = User::where('email', $request->email)->first();

        if (!$user || !Hash::check($request->password, $user->password)) {
            throw ValidationException::withMessages([
                'email' => ['Bilgiler hatalı.'],
            ]);
        }

        $token = $user->createToken('auth_token')->plainTextToken;

        return response()->json([
            'user' => $user,
            'token' => $token,
        ]);
    }

    // çıkış yap
    public function logout(Request $request)
    {
        $request->user()->currentAccessToken()->delete();

        return response()->json([
            'message' => 'Çıkış yapıldı.',
        ]);
    }

    // giriş yapan kullanıcı bilgisi
    public function me(Request $request)
    {
        $user = $request->user();

        return response()->json($user);
    }
}

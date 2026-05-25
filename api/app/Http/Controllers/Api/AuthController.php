<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\RegisterCompleteRequest;
use App\Models\MembershipPlan;
use App\Models\User;
use App\Models\UserMembership;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;

class AuthController extends Controller
{
    // e-posta müsait mi (kayıt formu 1. adım)
    public function checkEmail(Request $request)
    {
        $request->validate([
            'email' => 'required|string|email',
        ]);

        $email = strtolower(trim($request->email));
        $exists = User::where('email', $email)->exists();

        return response()->json([
            'available' => ! $exists,
        ]);
    }

    // kayıt ol
    public function register(Request $request)
    {
        $email = strtolower(trim($request->input('email', '')));

        $request->merge([
            'email' => $email,
            'password_confirmation' => $request->password_confirmation ?? $request->password,
        ]);

        $existing = User::where('email', $email)->first();

        if ($existing) {
            if (! Hash::check($request->password, $existing->password)) {
                throw ValidationException::withMessages([
                    'email' => ['Bu e-posta adresi zaten kayıtlı.'],
                ]);
            }

            $token = $existing->createToken('auth_token')->plainTextToken;

            return response()->json([
                'user' => $existing,
                'token' => $token,
            ]);
        }

        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|unique:users',
            'password' => 'required|string|min:8',
            'password_confirmation' => 'required|string|same:password',
        ], [
            'name.required' => 'Ad soyad zorunludur.',
            'email.required' => 'E-posta zorunludur.',
            'email.email' => 'Geçerli bir e-posta adresi giriniz.',
            'email.unique' => 'Bu e-posta adresi zaten kayıtlı.',
            'password.required' => 'Şifre zorunludur.',
            'password.min' => 'Şifre en az 8 karakter olmalıdır.',
            'password_confirmation.same' => 'Şifreler eşleşmiyor.',
        ]);

        $user = User::create([
            'name' => $request->name,
            'email' => $email,
            'password' => $request->password,
            'role' => 'user',
        ]);

        $token = $user->createToken('auth_token')->plainTextToken;

        return response()->json([
            'user' => $user,
            'token' => $token,
        ], 201);
    }

    // çok adımlı kayıt formu — tek istekte hesap + profil + üyelik
    public function registerComplete(RegisterCompleteRequest $request)
    {
        $email = $request->email;

        return DB::transaction(function () use ($request, $email) {
            $user = User::where('email', $email)->first();

            if ($user) {
                if (! Hash::check($request->password, $user->password)) {
                    throw ValidationException::withMessages([
                        'email' => ['Bu e-posta adresi zaten kayıtlı.'],
                    ]);
                }
            } else {
                $user = User::create([
                    'name' => $request->name,
                    'email' => $email,
                    'password' => $request->password,
                    'role' => 'user',
                ]);
            }

            $profileData = array_filter([
                'name' => $request->name,
                'phone' => $request->phone,
                'birth_date' => $request->birth_date,
                'gender' => $request->gender,
                'height' => $request->height,
                'weight' => $request->weight,
                'fitness_goal' => $request->fitness_goal,
                'preferred_branches' => $request->preferred_branches ?? [],
            ], fn ($value) => $value !== null && $value !== '');

            $user->update($profileData);

            $existingMembership = UserMembership::where('user_id', $user->id)
                ->whereIn('status', ['active', 'pending'])
                ->first();

            if (! $existingMembership) {
                $plan = MembershipPlan::findOrFail($request->membership_plan_id);

                UserMembership::create([
                    'user_id' => $user->id,
                    'membership_plan_id' => $plan->id,
                    'start_date' => null,
                    'end_date' => null,
                    'remaining_sessions' => $plan->session_count,
                    'remaining_days' => $plan->duration_days ?? 0,
                    'total_days' => $plan->duration_days ?? 0,
                    'status' => 'pending',
                    'last_check_in' => now(),
                ]);
            }

            $token = $user->createToken('auth_token')->plainTextToken;

            return response()->json([
                'user' => $user->fresh(),
                'token' => $token,
                'message' => 'Kayıt başarılı.',
            ], 201);
        });
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

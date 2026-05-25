<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\ProfileRequest;
use App\Http\Resources\UserResource;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class ProfileController extends Controller
{
    public function show(Request $request): JsonResponse
    {
        return response()->json([
            'data' => new UserResource($request->user()),
        ]);
    }

    public function update(ProfileRequest $request): JsonResponse
    {
        $user = $request->user();

        $updateData = [
            'name' => $request->name ?? $user->name,
            'email' => $request->email ?? $user->email,
            'height' => $request->height ?? $user->height,
            'weight' => $request->weight ?? $user->weight,
            'birth_date' => $request->birth_date ?? $request->date_of_birth ?? $user->birth_date,
            'gender' => $request->gender ?? $user->gender,
            'phone' => $request->phone ?? $user->phone,
            'fitness_goal' => $request->fitness_goal ?? $user->fitness_goal,
            'avatar' => $request->avatar ?? $user->avatar,
            'preferred_branches' => $request->preferred_branches ?? $user->preferred_branches,
        ];

        // Sadece gönderilen alanları güncelle
        $user->update(array_filter($updateData, fn($value) => $value !== null));

        return response()->json([
            'data' => new UserResource($user->fresh()),
            'message' => 'Profil başarıyla güncellendi.',
        ]);
    }

    public function updatePassword(Request $request): JsonResponse
    {
        $request->validate([
            'current_password' => 'required',
            'password' => 'required|string|min:8|confirmed',
        ]);

        $user = $request->user();

        if (!Hash::check($request->current_password, $user->password)) {
            return response()->json([
                'message' => 'Mevcut şifre hatalı.',
            ], 422);
        }

        $user->update([
            'password' => Hash::make($request->password),
        ]);

        return response()->json([
            'message' => 'Şifre başarıyla güncellendi.',
        ]);
    }

    public function destroy(Request $request): JsonResponse
    {
        $user = $request->user();

        // Revoke all tokens
        $user->tokens()->delete();

        // Delete account
        $user->delete();

        return response()->json([
            'message' => 'Hesap başarıyla silindi.',
        ]);
    }
}

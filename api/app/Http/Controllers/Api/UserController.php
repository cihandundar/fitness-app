<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\UserResource;
use App\Models\User;
use App\Models\TrainerProfile;
use Illuminate\Http\Request;

class UserController extends Controller
{
    /**
     * Tüm kullanıcıları listeler (Admin için).
     */
    public function index(Request $request)
    {
        $query = User::with('trainerProfile');

        if ($request->has('role')) {
            $query->where('role', $request->role);
        }

        $users = $query->orderBy('created_at', 'desc')->get();

        return response()->json([
            'status' => 'success',
            'data' => UserResource::collection($users)
        ]);
    }

    /**
     * Kullanıcı detayını gösterir.
     */
    public function show(User $user)
    {
        return response()->json([
            'status' => 'success',
            'data' => new UserResource($user->load('trainerProfile'))
        ]);
    }

    /**
     * Kullanıcı rolünü ve bilgilerini günceller.
     */
    public function update(Request $request, User $user)
    {
        // Super Admin'in durumunu değiştirmeyi engelle
        if ($user->role === 'super_admin' && $request->has('is_active')) {
            return response()->json([
                'status' => 'error',
                'message' => 'Super Admin her zaman aktiftir.'
            ], 403);
        }

        $request->validate([
            'role' => 'sometimes|in:user,trainer,admin,super_admin',
            'is_active' => 'sometimes|boolean',
        ]);

        $data = [];

        // Rol güncelleme
        if ($request->has('role')) {
            $data['role'] = $request->role;
        }

        // Durum güncelleme (Super Admin değilse)
        if ($request->has('is_active') && $user->role !== 'super_admin') {
            $data['is_active'] = $request->boolean('is_active');
        }

        if (!empty($data)) {
            $user->update($data);
            // Değişiklikten sonra kullanıcıyı yeniden yükle
            $user->refresh();
        }

        // Eğer rol trainer yapıldıysa ve profili yoksa oluştur
        if (isset($data['role']) && $data['role'] === 'trainer' && !$user->trainerProfile) {
            TrainerProfile::create([
                'user_id' => $user->id,
                'experience_years' => 0
            ]);
        }

        return response()->json([
            'status' => 'success',
            'message' => 'Kullanıcı güncellendi.',
            'data' => new UserResource($user->load('trainerProfile'))
        ]);
    }

    /**
     * Kullanıcıyı siler.
     */
    public function destroy(User $user)
    {
        if ($user->role === 'super_admin') {
            return response()->json(['message' => 'Super Admin silinemez.'], 403);
        }

        $user->delete();

        return response()->noContent();
    }

    /**
     * Kullanıcının üyelik bilgisini getirir.
     */
    public function getUserMembership(User $user)
    {
        $membership = \App\Models\UserMembership::with('plan')
            ->where('user_id', $user->id)
            ->whereIn('status', [\App\Models\UserMembership::STATUS_ACTIVE, \App\Models\UserMembership::STATUS_PENDING])
            ->orderBy('created_at', 'desc')
            ->first();

        return response()->json([
            'success' => true,
            'data' => $membership
        ]);
    }

    /**
     * Kullanıcının atanan programlarını getirir.
     */
    public function getUserPrograms(User $user)
    {
        $programs = $user->programs()
            ->where('is_active', true)
            ->with('workouts')
            ->get();

        return response()->json([
            'success' => true,
            'data' => $programs
        ]);
    }

    /**
     * Kullanıcının ilerleme kayıtlarını getirir.
     */
    public function getUserProgress(User $user)
    {
        $progressLogs = \App\Models\ProgressLog::where('user_id', $user->id)
            ->orderBy('created_at', 'desc')
            ->take(20)
            ->get();

        return response()->json([
            'success' => true,
            'data' => $progressLogs
        ]);
    }
}

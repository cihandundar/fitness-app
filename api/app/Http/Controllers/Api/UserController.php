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
        $request->validate([
            'role' => 'sometimes|in:user,trainer,admin,super_admin',
            'is_active' => 'boolean',
        ]);

        $data = [];
        if ($request->has('role')) {
            $data['role'] = $request->role;
        }
        if ($request->has('is_active')) {
            $data['is_active'] = $request->is_active;
        }

        $user->update($data);

        // Eğer rol trainer yapıldıysa ve profili yoksa oluştur
        if ($request->role === 'trainer' && !$user->trainerProfile) {
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
}

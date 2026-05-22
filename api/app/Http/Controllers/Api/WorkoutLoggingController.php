<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\CompletedWorkout;
use App\Models\ExerciseLog;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class WorkoutLoggingController extends Controller
{
    /**
     * Antrenmanı başlatır.
     */
    public function startWorkout(Request $request): JsonResponse
    {
        $request->validate([
            'workout_id' => 'nullable|exists:workouts,id',
        ]);

        $session = CompletedWorkout::create([
            'user_id' => Auth::id(),
            'workout_id' => $request->workout_id,
            'started_at' => now(),
        ]);

        return response()->json([
            'status' => 'success',
            'data' => $session,
            'message' => 'Antrenman başlatıldı.'
        ]);
    }

    /**
     * Bir egzersiz setini kaydeder.
     */
    public function logSet(Request $request): JsonResponse
    {
        $request->validate([
            'completed_workout_id' => 'required|exists:completed_workouts,id',
            'exercise_id' => 'required|exists:exercises,id',
            'set_number' => 'required|integer',
            'weight' => 'nullable|numeric',
            'reps' => 'nullable|integer',
            'rest_time' => 'nullable|integer',
        ]);

        $log = ExerciseLog::create($request->all());

        return response()->json([
            'status' => 'success',
            'data' => $log,
            'message' => 'Set kaydedildi.'
        ]);
    }

    /**
     * Antrenmanı bitirir.
     */
    public function finishWorkout(Request $request, CompletedWorkout $session): JsonResponse
    {
        $session->update([
            'completed_at' => now(),
            'notes' => $request->notes
        ]);

        return response()->json([
            'status' => 'success',
            'data' => $session,
            'message' => 'Antrenman tamamlandı.'
        ]);
    }

    /**
     * Belirli bir egzersizin geçmişini getirir.
     */
    public function getExerciseHistory($exerciseId): JsonResponse
    {
        $history = ExerciseLog::where('exercise_id', $exerciseId)
            ->whereHas('completedWorkout', function($q) {
                $q->where('user_id', Auth::id());
            })
            ->with('completedWorkout')
            ->orderBy('created_at', 'desc')
            ->limit(10)
            ->get();

        return response()->json([
            'status' => 'success',
            'data' => $history
        ]);
    }

    /**
     * Kullanıcının son antrenmanlarını getirir.
     */
    public function getHistory(): JsonResponse
    {
        $history = CompletedWorkout::where('user_id', Auth::id())
            ->with(['workout', 'exerciseLogs.exercise'])
            ->orderBy('completed_at', 'desc')
            ->paginate(10);

        return response()->json([
            'status' => 'success',
            'data' => $history
        ]);
    }

    /**
     * Tüm kullanıcıların antrenman geçmişini getirir (Admin için).
     */
    public function getAllHistory(): JsonResponse
    {
        if (!Auth::user()->isAdmin()) {
            return response()->json(['message' => 'Yetkisiz erişim.'], 403);
        }

        $history = CompletedWorkout::with(['user', 'workout'])
            ->orderBy('created_at', 'desc')
            ->paginate(15);

        return response()->json([
            'status' => 'success',
            'data' => $history
        ]);
    }
}

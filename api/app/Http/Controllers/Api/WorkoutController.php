<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\WorkoutRequest;
use App\Http\Resources\WorkoutResource;
use App\Models\Workout;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class WorkoutController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $query = Workout::query();

        // Filter by program
        if ($request->has('program_id')) {
            $query->where('program_id', $request->program_id);
        }

        // Filter by day
        if ($request->has('day_number')) {
            $query->where('day_number', $request->day_number);
        }

        // Search by title
        if ($request->has('search')) {
            $query->where('title', 'like', '%' . $request->search . '%');
        }

        $query->withCount('exercises');

        $workouts = $query->orderBy('day_number')->orderBy('created_at')
            ->paginate($request->input('per_page', 15));

        return response()->json([
            'data' => WorkoutResource::collection($workouts),
            'meta' => [
                'total' => $workouts->total(),
                'per_page' => $workouts->perPage(),
                'current_page' => $workouts->currentPage(),
                'last_page' => $workouts->lastPage(),
            ],
        ]);
    }

    public function store(WorkoutRequest $request): JsonResponse
    {
        $workout = Workout::create([
            'program_id' => $request->program_id,
            'day_number' => $request->day_number,
            'title' => $request->title,
            'description' => $request->description,
            'duration_minutes' => $request->duration_minutes,
        ]);

        return response()->json([
            'data' => new WorkoutResource($workout),
            'message' => 'Antrenman başarıyla oluşturuldu.',
        ], 201);
    }

    public function show(Workout $workout): JsonResponse
    {
        $workout->load(['exercises', 'program']);

        return response()->json([
            'data' => new WorkoutResource($workout),
        ]);
    }

    public function update(WorkoutRequest $request, Workout $workout): JsonResponse
    {
        $workout->update($request->validated());

        return response()->json([
            'data' => new WorkoutResource($workout->fresh()),
            'message' => 'Antrenman başarıyla güncellendi.',
        ]);
    }

    public function destroy(Workout $workout): JsonResponse
    {
        $workout->delete();

        return response()->json([
            'message' => 'Antrenman başarıyla silindi.',
        ]);
    }
}

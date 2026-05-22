<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\ExerciseRequest;
use App\Http\Resources\ExerciseResource;
use App\Models\Exercise;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class ExerciseController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $query = Exercise::query();

        // Filter by muscle group
        if ($request->has('muscle_group')) {
            $query->where('muscle_group', $request->muscle_group);
        }

        // Filter by equipment
        if ($request->has('equipment')) {
            $query->where('equipment', $request->equipment);
        }

        // Filter by difficulty
        if ($request->has('difficulty')) {
            $query->where('difficulty', $request->difficulty);
        }

        // Search by name
        if ($request->has('search')) {
            $query->where('name', 'like', '%' . $request->search . '%');
        }

        $query->withCount('workouts')->with(['muscleGroup', 'equipmentType']);

        $exercises = $query->orderBy('name')->paginate($request->input('per_page', 20));

        return response()->json([
            'data' => ExerciseResource::collection($exercises),
            'meta' => [
                'total' => $exercises->total(),
                'per_page' => $exercises->perPage(),
                'current_page' => $exercises->currentPage(),
                'last_page' => $exercises->lastPage(),
            ],
        ]);
    }

    public function store(ExerciseRequest $request): JsonResponse
    {
        $data = [
            'name' => $request->name,
            'slug' => $request->slug ?? Str::slug($request->name),
            'description' => $request->description,
            'muscle_group_id' => $request->muscle_group_id,
            'equipment_type_id' => $request->equipment_type_id,
            'difficulty' => $request->difficulty ?? 'intermediate',
            'is_active' => $request->is_active ?? true,
        ];

        if ($request->hasFile('image')) {
            $data['image'] = $request->file('image')->store('exercises', 'public');
        }

        $exercise = Exercise::create($data);
        $exercise->load(['muscleGroup', 'equipmentType']);

        return response()->json([
            'data' => new ExerciseResource($exercise),
            'message' => 'Egzersiz başarıyla oluşturuldu.',
        ], 201);
    }

    public function show(Exercise $exercise): JsonResponse
    {
        $exercise->load(['muscleGroup', 'equipmentType']);
        return response()->json([
            'data' => new ExerciseResource($exercise),
        ]);
    }

    public function update(ExerciseRequest $request, Exercise $exercise): JsonResponse
    {
        $data = [
            'name' => $request->name,
            'slug' => $request->slug ?? Str::slug($request->name),
        ];

        // Sadece gelen alanları güncelle
        if ($request->has('description')) {
            $data['description'] = $request->description;
        }
        if ($request->has('muscle_group_id')) {
            $data['muscle_group_id'] = $request->muscle_group_id;
        }
        if ($request->has('equipment_type_id')) {
            $data['equipment_type_id'] = $request->equipment_type_id;
        }
        if ($request->has('difficulty')) {
            $data['difficulty'] = $request->difficulty;
        }
        if ($request->hasFile('image')) {
            $data['image'] = $request->file('image')->store('exercises', 'public');
        }
        if ($request->has('is_active')) {
            $data['is_active'] = $request->is_active;
        }

        $exercise->update($data);
        $exercise->load(['muscleGroup', 'equipmentType']);

        return response()->json([
            'data' => new ExerciseResource($exercise),
            'message' => 'Egzersiz başarıyla güncellendi.',
        ]);
    }

    public function destroy(Exercise $exercise): JsonResponse
    {
        $exercise->delete();

        return response()->json([
            'message' => 'Egzersiz başarıyla silindi.',
        ]);
    }
}

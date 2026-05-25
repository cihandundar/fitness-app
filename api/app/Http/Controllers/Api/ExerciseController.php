<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\ExerciseRequest;
use App\Http\Resources\ExerciseResource;
use App\Models\EquipmentType;
use App\Models\Exercise;
use App\Models\MuscleGroup;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class ExerciseController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $perPage = (int) $request->input('per_page', 20);
        $perPage = max(1, min($perPage, 100));

        $query = Exercise::query();

        if ($request->filled('muscle_group')) {
            $query->where('muscle_group', $request->muscle_group);
        }

        if ($request->filled('equipment')) {
            $query->where('equipment', $request->equipment);
        }

        if ($request->filled('difficulty')) {
            $query->where('difficulty', $request->difficulty);
        }

        if ($request->filled('search')) {
            $query->where('name', 'like', '%' . $request->search . '%');
        }

        $exercises = $query
            ->withCount('workouts')
            ->with(['muscleGroup', 'equipmentType'])
            ->orderBy('name')
            ->paginate($perPage);

        return ExerciseResource::collection($exercises)->response();
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

        if ($request->filled('instructions')) {
            $data['instructions'] = $request->instructions;
        }
        if ($request->filled('tips')) {
            $data['tips'] = $request->tips;
        }

        $data = $this->withLegacyGroupFields($data);

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
        if ($request->has('instructions')) {
            $data['instructions'] = $request->instructions;
        }
        if ($request->has('tips')) {
            $data['tips'] = $request->tips;
        }

        $data = $this->withLegacyGroupFields($data, $exercise);

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

    /**
     * Eski string kolonları (muscle_group, equipment) FK'lardan doldurur.
     * Veritabanında bu alanlar NOT NULL olduğu için create/update'te zorunlu.
     */
    private function withLegacyGroupFields(array $data, ?Exercise $existing = null): array
    {
        $muscleGroupId = $data['muscle_group_id'] ?? $existing?->muscle_group_id;
        if ($muscleGroupId) {
            $data['muscle_group'] = MuscleGroup::find($muscleGroupId)?->name
                ?? $existing?->muscle_group
                ?? 'general';
        } elseif (! isset($data['muscle_group'])) {
            $data['muscle_group'] = $existing?->muscle_group ?? 'general';
        }

        $equipmentTypeId = $data['equipment_type_id'] ?? $existing?->equipment_type_id;
        if ($equipmentTypeId) {
            $data['equipment'] = EquipmentType::find($equipmentTypeId)?->name
                ?? $existing?->equipment
                ?? 'none';
        } elseif (! isset($data['equipment'])) {
            $data['equipment'] = $existing?->equipment ?? 'none';
        }

        return $data;
    }
}

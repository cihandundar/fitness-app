<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\EquipmentType;
use App\Models\MuscleGroup;
use Illuminate\Http\Request;

class UserPreferenceController extends Controller
{
    public function getPreferences(Request $request)
    {
        $user = $request->user();

        return response()->json([
            'success' => true,
            'data' => [
                'target_groups' => $user->targetGroups()->orderBy('sort_order')->get(),
                'equipment' => $user->equipments()->orderBy('sort_order')->get(),
            ]
        ]);
    }

    public function updatePreferences(Request $request)
    {
        $request->validate([
            'target_groups' => 'nullable|array',
            'target_groups.*' => 'exists:muscle_groups,id',
            'equipment' => 'nullable|array',
            'equipment.*' => 'exists:equipment_types,id',
        ]);

        $user = $request->user();

        // Hedef bölgelerini güncelle
        if ($request->has('target_groups')) {
            $user->targetGroups()->sync($request->target_groups);
        }

        // Ekipmanları güncelle
        if ($request->has('equipment')) {
            $user->equipments()->sync($request->equipment);
        }

        return response()->json([
            'success' => true,
            'message' => 'Tercihler güncellendi',
            'data' => [
                'target_groups' => $user->targetGroups()->orderBy('sort_order')->get(),
                'equipment' => $user->equipments()->orderBy('sort_order')->get(),
            ]
        ]);
    }

    public function getAvailableOptions()
    {
        return response()->json([
            'success' => true,
            'data' => [
                'muscle_groups' => MuscleGroup::where('is_active', true)->orderBy('sort_order')->get(),
                'equipment_types' => EquipmentType::where('is_active', true)->orderBy('sort_order')->get(),
            ]
        ]);
    }
}

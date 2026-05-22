<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\MuscleGroupResource;
use App\Models\MuscleGroup;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class MuscleGroupController extends Controller
{
    public function index()
    {
        $groups = MuscleGroup::orderBy('sort_order')->orderBy('name')->get();
        return response()->json([
            'success' => true,
            'data' => MuscleGroupResource::collection($groups)
        ]);
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'icon' => 'nullable|string|max:50',
            'color' => 'nullable|string|max:50',
            'image' => 'nullable|image|max:2048',
            'sort_order' => 'nullable|integer',
        ]);

        $data = [
            'name' => $request->name,
            'slug' => Str::slug($request->name),
            'icon' => $request->icon,
            'color' => $request->color ?? 'violet',
            'sort_order' => $request->sort_order ?? 0,
            'is_active' => true,
        ];

        if ($request->hasFile('image')) {
            $data['image'] = $request->file('image')->store('muscle-groups', 'public');
        }

        $group = MuscleGroup::create($data);

        return response()->json([
            'success' => true,
            'message' => 'Kas grubu oluşturuldu',
            'data' => new MuscleGroupResource($group)
        ], 201);
    }

    public function update(Request $request, MuscleGroup $muscleGroup)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'icon' => 'nullable|string|max:50',
            'color' => 'nullable|string|max:50',
            'image' => 'nullable|image|max:2048',
            'sort_order' => 'nullable|integer',
            'is_active' => 'nullable|boolean',
        ]);

        $data = [
            'name' => $request->name,
            'slug' => Str::slug($request->name),
        ];

        if ($request->has('icon')) {
            $data['icon'] = $request->icon;
        }
        if ($request->has('color')) {
            $data['color'] = $request->color;
        }
        if ($request->has('sort_order')) {
            $data['sort_order'] = $request->sort_order;
        }
        if ($request->has('is_active')) {
            $data['is_active'] = $request->is_active;
        }
        if ($request->hasFile('image')) {
            $data['image'] = $request->file('image')->store('muscle-groups', 'public');
        }

        $muscleGroup->update($data);

        return response()->json([
            'success' => true,
            'message' => 'Kas grubu güncellendi',
            'data' => new MuscleGroupResource($muscleGroup->fresh())
        ]);
    }

    public function destroy(MuscleGroup $muscleGroup)
    {
        $muscleGroup->delete();
        return response()->json([
            'success' => true,
            'message' => 'Kas grubu silindi'
        ]);
    }

    public function reorder(Request $request)
    {
        $request->validate([
            'orders' => 'required|array',
            'orders.*.id' => 'required|exists:muscle_groups,id',
            'orders.*.sort_order' => 'required|integer',
        ]);

        foreach ($request->orders as $item) {
            MuscleGroup::where('id', $item['id'])->update(['sort_order' => $item['sort_order']]);
        }

        return response()->json([
            'success' => true,
            'message' => 'Sıralama güncellendi'
        ]);
    }
}

<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\EquipmentType;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class EquipmentTypeController extends Controller
{
    public function index()
    {
        $equipment = EquipmentType::orderBy('sort_order')->orderBy('name')->get();
        return response()->json([
            'success' => true,
            'data' => $equipment
        ]);
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'icon' => 'nullable|string|max:50',
            'image' => 'nullable|image|max:2048',
            'sort_order' => 'nullable|integer',
        ]);

        $data = [
            'name' => $request->name,
            'slug' => Str::slug($request->name),
            'icon' => $request->icon,
            'sort_order' => $request->sort_order ?? 0,
            'is_active' => true,
        ];

        if ($request->hasFile('image')) {
            $data['image'] = $request->file('image')->store('equipment', 'public');
        }

        $equipment = EquipmentType::create($data);

        return response()->json([
            'success' => true,
            'message' => 'Ekipman türü oluşturuldu',
            'data' => $equipment
        ], 201);
    }

    public function update(Request $request, EquipmentType $equipmentType)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'icon' => 'nullable|string|max:50',
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
        if ($request->has('sort_order')) {
            $data['sort_order'] = $request->sort_order;
        }
        if ($request->has('is_active')) {
            $data['is_active'] = $request->is_active;
        }
        if ($request->hasFile('image')) {
            $data['image'] = $request->file('image')->store('equipment', 'public');
        }

        $equipmentType->update($data);

        return response()->json([
            'success' => true,
            'message' => 'Ekipman türü güncellendi',
            'data' => $equipmentType->fresh()
        ]);
    }

    public function destroy(EquipmentType $equipmentType)
    {
        $equipmentType->delete();
        return response()->json([
            'success' => true,
            'message' => 'Ekipman türü silindi'
        ]);
    }

    public function reorder(Request $request)
    {
        $request->validate([
            'orders' => 'required|array',
            'orders.*.id' => 'required|exists:equipment_types,id',
            'orders.*.sort_order' => 'required|integer',
        ]);

        foreach ($request->orders as $item) {
            EquipmentType::where('id', $item['id'])->update(['sort_order' => $item['sort_order']]);
        }

        return response()->json([
            'success' => true,
            'message' => 'Sıralama güncellendi'
        ]);
    }
}

<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\BranchResource;
use App\Models\Branch;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class BranchController extends Controller
{
    public function index()
    {
        return response()->json([
            'status' => 'success',
            'data' => BranchResource::collection(Branch::orderBy('order')->get())
        ]);
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'icon' => 'nullable|string',
            'description' => 'nullable|string',
            'is_active' => 'boolean',
            'order' => 'integer',
        ]);

        $branch = Branch::create([
            'name' => $request->name,
            'slug' => Str::slug($request->name),
            'icon' => $request->icon,
            'description' => $request->description,
            'is_active' => $request->is_active ?? true,
            'order' => $request->order ?? 0,
        ]);

        return response()->json([
            'status' => 'success',
            'message' => 'Branş başarıyla oluşturuldu.',
            'data' => new BranchResource($branch)
        ], 201);
    }

    public function show(Branch $branch)
    {
        return response()->json([
            'status' => 'success',
            'data' => new BranchResource($branch)
        ]);
    }

    public function update(Request $request, Branch $branch)
    {
        $request->validate([
            'name' => 'sometimes|required|string|max:255',
        ]);

        $branch->update($request->all());

        if ($request->has('name')) {
            $branch->slug = Str::slug($request->name);
            $branch->save();
        }

        return response()->json([
            'status' => 'success',
            'message' => 'Branş başarıyla güncellendi.',
            'data' => new BranchResource($branch)
        ]);
    }

    public function updateOrder(Request $request)
    {
        $request->validate([
            'orders' => 'required|array',
            'orders.*.id' => 'required|exists:branches,id',
            'orders.*.order' => 'required|integer',
        ]);

        foreach ($request->orders as $item) {
            Branch::where('id', $item['id'])->update(['order' => $item['order']]);
        }

        return response()->json([
            'status' => 'success',
            'message' => 'Sıralama güncellendi.'
        ]);
    }

    public function uploadImage(Request $request, Branch $branch)
    {
        $request->validate([
            'image' => 'required|image|mimes:jpeg,png,jpg,gif,svg|max:2048',
        ]);

        if ($request->hasFile('image')) {
            $imageName = time().'.'.$request->image->extension();
            $request->image->move(public_path('uploads/branches'), $imageName);
            
            $branch->image = 'uploads/branches/'.$imageName;
            $branch->save();

            return response()->json([
                'status' => 'success',
                'message' => 'Görsel başarıyla yüklendi.',
                'data' => new BranchResource($branch)
            ]);
        }

        return response()->json([
            'status' => 'error',
            'message' => 'Görsel yüklenemedi.'
        ], 400);
    }

    public function destroy(Branch $branch)
    {
        $branch->delete();

        return response()->json([
            'status' => 'success',
            'message' => 'Branş başarıyla silindi.'
        ]);
    }
}

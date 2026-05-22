<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\MembershipPlanResource;
use App\Models\MembershipPlan;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class MembershipPlanController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        return response()->json([
            'status' => 'success',
            'data' => MembershipPlanResource::collection(MembershipPlan::all())
        ]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'features' => 'nullable|array',
            'price' => 'required|numeric|min:0',
            'duration_days' => 'nullable|integer|min:1',
            'session_count' => 'nullable|integer|min:0',
            'type' => 'required|in:gym,pt,hybrid',
            'is_featured' => 'boolean',
            'is_active' => 'boolean',
        ]);

        $plan = MembershipPlan::create([
            'name' => $request->name,
            'slug' => Str::slug($request->name) . '-' . Str::random(5),
            'description' => $request->description,
            'features' => $request->features,
            'price' => $request->price,
            'duration_days' => $request->duration_days,
            'session_count' => $request->session_count,
            'type' => $request->type,
            'is_featured' => $request->is_featured ?? false,
            'is_active' => $request->is_active ?? true,
        ]);

        return response()->json([
            'status' => 'success',
            'message' => 'Plan başarıyla oluşturuldu.',
            'data' => new MembershipPlanResource($plan)
        ], 201);
    }

    /**
     * Display the specified resource.
     */
    public function show(MembershipPlan $membershipPlan)
    {
        return response()->json([
            'status' => 'success',
            'data' => new MembershipPlanResource($membershipPlan)
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, MembershipPlan $membershipPlan)
    {
        $request->validate([
            'name' => 'sometimes|required|string|max:255',
            'price' => 'sometimes|required|numeric|min:0',
            'type' => 'sometimes|required|in:gym,pt,hybrid',
            'features' => 'nullable|array',
            'is_featured' => 'boolean',
        ]);

        $data = $request->except(['name']);

        if ($request->has('name')) {
            $data['name'] = $request->name;
            $data['slug'] = Str::slug($request->name) . '-' . Str::random(5);
        }

        $membershipPlan->update($data);

        return response()->json([
            'status' => 'success',
            'message' => 'Plan başarıyla güncellendi.',
            'data' => new MembershipPlanResource($membershipPlan->fresh())
        ]);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(MembershipPlan $membershipPlan)
    {
        $membershipPlan->delete();

        return response()->json([
            'status' => 'success',
            'message' => 'Plan başarıyla silindi.'
        ]);
    }
}

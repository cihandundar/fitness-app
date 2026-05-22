<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\ProgramRequest;
use App\Http\Resources\ProgramResource;
use App\Models\Program;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Auth;

class ProgramController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $query = Program::query();

        // Filter by level
        if ($request->has('level')) {
            $query->where('level', $request->level);
        }

        // Filter by featured
        if ($request->boolean('featured')) {
            $query->where('is_featured', true);
        }

        // Filter by active
        if ($request->has('active')) {
            $query->where('is_active', $request->boolean('active'));
        }

        // Search by title
        if ($request->has('search')) {
            $query->where('title', 'like', '%' . $request->search . '%');
        }

        // With relationships
        $query->withCount('workouts', 'users');

        // Role based filtering
        $user = Auth::user();
        if ($user && $user->role === 'user') {
            // Sadece kullanıcıya atanmış programları getir
            $query->whereHas('users', function($q) use ($user) {
                $q->where('user_id', $user->id);
            });
        }

        // Order
        $query->orderBy('is_featured', 'desc')
              ->orderBy('created_at', 'desc');

        $programs = $query->paginate($request->input('per_page', 15));

        return response()->json([
            'data' => ProgramResource::collection($programs),
            'meta' => [
                'total' => $programs->total(),
                'per_page' => $programs->perPage(),
                'current_page' => $programs->currentPage(),
                'last_page' => $programs->lastPage(),
            ],
        ]);
    }

    public function store(ProgramRequest $request): JsonResponse
    {
        $slug = $request->slug ?? \Illuminate\Support\Str::slug($request->title) . '-' . \Illuminate\Support\Str::random(5);

        $program = Program::create([
            'created_by' => Auth::id(),
            'title' => $request->title,
            'slug' => $slug,
            'description' => $request->description,
            'level' => $request->level,
            'duration_weeks' => $request->duration_weeks,
            'image' => $request->image,
            'is_active' => $request->boolean('is_active', true),
            'is_featured' => $request->boolean('is_featured', false),
            'settings' => $request->settings,
        ]);

        return response()->json([
            'data' => new ProgramResource($program),
            'message' => 'Program başarıyla oluşturuldu.',
        ], 201);
    }

    public function show(Program $program): JsonResponse
    {
        $program->load(['workouts', 'workouts.exercises']);

        return response()->json([
            'data' => new ProgramResource($program),
        ]);
    }

    public function uploadImage(Request $request, Program $program): JsonResponse
    {
        $request->validate([
            'image' => 'required|image|mimes:jpeg,png,jpg,gif,svg|max:2048',
        ]);

        if ($request->hasFile('image')) {
            $imageName = time().'.'.$request->image->extension();
            $request->image->move(public_path('uploads/programs'), $imageName);
            
            $program->image = 'uploads/programs/'.$imageName;
            $program->save();

            return response()->json([
                'status' => 'success',
                'message' => 'Görsel başarıyla yüklendi.',
                'data' => new ProgramResource($program)
            ]);
        }

        return response()->json([
            'status' => 'error',
            'message' => 'Görsel yüklenemedi.'
        ], 400);
    }

    public function update(ProgramRequest $request, Program $program): JsonResponse
    {
        $program->update($request->validated());

        return response()->json([
            'data' => new ProgramResource($program->fresh()),
            'message' => 'Program başarıyla güncellendi.',
        ]);
    }

    public function destroy(Program $program): Response
    {
        $program->delete();

        return response()->noContent();
    }
}

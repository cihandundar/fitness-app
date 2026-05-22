<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\ProgressLogRequest;
use App\Http\Resources\ProgressLogResource;
use App\Models\ProgressLog;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ProgressController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $query = Auth::user()->progressLogs();

        // Filter by date range
        if ($request->has('from')) {
            $query->where('logged_at', '>=', $request->from);
        }
        if ($request->has('to')) {
            $query->where('logged_at', '<=', $request->to);
        }

        $logs = $query->orderBy('logged_at', 'desc')
            ->paginate($request->input('per_page', 20));

        return response()->json([
            'data' => ProgressLogResource::collection($logs),
            'meta' => [
                'total' => $logs->total(),
                'per_page' => $logs->perPage(),
                'current_page' => $logs->currentPage(),
                'last_page' => $logs->lastPage(),
            ],
        ]);
    }

    public function store(ProgressLogRequest $request): JsonResponse
    {
        $log = Auth::user()->progressLogs()->create([
            'weight' => $request->weight,
            'body_fat' => $request->body_fat,
            'notes' => $request->notes,
            'logged_at' => $request->logged_at ?? now(),
        ]);

        return response()->json([
            'data' => new ProgressLogResource($log),
            'message' => 'İlerleme kaydı başarıyla oluşturuldu.',
        ], 201);
    }

    public function show(ProgressLog $progressLog): JsonResponse
    {
        // Check ownership
        if ($progressLog->user_id !== Auth::id()) {
            return response()->json([
                'message' => 'Bu kayda erişim izniniz yok.',
            ], 403);
        }

        return response()->json([
            'data' => new ProgressLogResource($progressLog),
        ]);
    }

    public function update(ProgressLogRequest $request, ProgressLog $progressLog): JsonResponse
    {
        // Check ownership
        if ($progressLog->user_id !== Auth::id()) {
            return response()->json([
                'message' => 'Bu kaydı güncelleme izniniz yok.',
            ], 403);
        }

        $progressLog->update([
            'weight' => $request->weight ?? $progressLog->weight,
            'body_fat' => $request->body_fat ?? $progressLog->body_fat,
            'notes' => $request->notes ?? $progressLog->notes,
            'logged_at' => $request->logged_at ?? $progressLog->logged_at,
        ]);

        return response()->json([
            'data' => new ProgressLogResource($progressLog->fresh()),
            'message' => 'İlerleme kaydı başarıyla güncellendi.',
        ]);
    }

    public function destroy(ProgressLog $progressLog): JsonResponse
    {
        // Check ownership
        if ($progressLog->user_id !== Auth::id()) {
            return response()->json([
                'message' => 'Bu kaydı silme izniniz yok.',
            ], 403);
        }

        $progressLog->delete();

        return response()->json([
            'message' => 'İlerleme kaydı başarıyla silindi.',
        ]);
    }

    // Get stats summary
    public function stats(): JsonResponse
    {
        $user = Auth::user();

        // Get latest log (most recent)
        $latestLog = $user->progressLogs()->orderBy('logged_at', 'desc')->first();
        // Get earliest log (oldest)
        $startingLog = $user->progressLogs()->orderBy('logged_at', 'asc')->first();

        $latestWeight = $latestLog?->weight;
        $startingWeight = $startingLog?->weight;

        return response()->json([
            'data' => [
                'total_logs' => $user->progressLogs()->count(),
                'latest_weight' => $latestWeight,
                'starting_weight' => $startingWeight,
                'weight_change' => $latestWeight && $startingWeight ? $latestWeight - $startingWeight : null,
                'current_height' => $user->height,
            ],
        ]);
    }
}

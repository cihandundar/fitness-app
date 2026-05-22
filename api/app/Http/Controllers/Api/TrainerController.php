<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\Program;
use App\Models\TrainerClient;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class TrainerController extends Controller
{
    /**
     * Eğitmenin kendi öğrencilerini (danışanlarını) listeler.
     */
    public function clients()
    {
        $trainer = Auth::user();

        if (!$trainer->isTrainer() && !$trainer->isAdmin()) {
            return response()->json(['message' => 'Bu işlem için yetkiniz yok.'], 403);
        }

        $clients = $trainer->clients()->get();

        return response()->json([
            'status' => 'success',
            'data' => $clients
        ]);
    }

    /**
     * Yeni bir öğrenci (danışan) ekler.
     */
    public function addClient(Request $request)
    {
        $request->validate([
            'email' => 'required|email|exists:users,email',
            'total_days' => 'required|integer|min:1',
        ]);

        $trainer = Auth::user();

        if (!$trainer->isTrainer() && !$trainer->isAdmin()) {
            return response()->json(['message' => 'Bu işlem için yetkiniz yok.'], 403);
        }

        $client = User::where('email', $request->email)->first();

        // Zaten öğrencisi mi kontrol et
        $existingClient = TrainerClient::where('trainer_id', $trainer->id)
            ->where('client_id', $client->id)
            ->where('status', 'active')
            ->first();

        if ($existingClient) {
            return response()->json(['message' => 'Bu kullanıcı zaten sizin öğrenciniz.'], 400);
        }

        // Öğrenciyi ekle
        TrainerClient::create([
            'trainer_id' => $trainer->id,
            'client_id' => $client->id,
            'status' => 'active',
            'started_at' => now(),
            'total_days' => $request->total_days,
            'remaining_days' => $request->total_days,
            'last_check_in' => now(),
        ]);

        return response()->json([
            'status' => 'success',
            'message' => 'Öğrenci başarıyla eklendi.'
        ]);
    }

    /**
     * Bir öğrenciye program atar.
     */
    public function assignProgram(Request $request)
    {
        $request->validate([
            'client_id' => 'required|exists:users,id',
            'program_id' => 'required|exists:programs,id',
        ]);

        $trainer = Auth::user();

        if (!$trainer->isTrainer() && !$trainer->isAdmin()) {
            return response()->json(['message' => 'Bu işlem için yetkiniz yok.'], 403);
        }

        // Öğrencinin bu eğitmenin öğrencisi olup olmadığını kontrol et
        $isMyClient = $trainer->clients()->where('client_id', $request->client_id)->exists();

        if (!$isMyClient && !$trainer->isAdmin()) {
            return response()->json(['message' => 'Bu kullanıcı sizin öğrenciniz değil.'], 403);
        }

        $client = User::find($request->client_id);

        // Programı ata
        $client->programs()->syncWithoutDetaching([
            $request->program_id => [
                'assigned_by' => $trainer->id,
                'is_active' => true,
                'started_at' => now(),
            ]
        ]);

        return response()->json([
            'status' => 'success',
            'message' => 'Program başarıyla atandı.'
        ]);
    }

    /**
     * Bir öğrencinin gelişimini (loglarını) görüntüler.
     */
    public function clientProgress($clientId)
    {
        $trainer = Auth::user();

        // Yetki kontrolü
        $isMyClient = $trainer->clients()->where('client_id', $clientId)->exists();
        if (!$isMyClient && !$trainer->isAdmin()) {
            return response()->json(['message' => 'Bu kullanıcının verilerini görme yetkiniz yok.'], 403);
        }

        $client = User::with(['progressLogs', 'completedWorkouts.workout', 'completedWorkouts.exerciseLogs.exercise'])
            ->findOrFail($clientId);

        return response()->json([
            'status' => 'success',
            'data' => [
                'client' => $client->only(['id', 'name', 'email']),
                'logs' => $client->progressLogs,
                'workouts' => $client->completedWorkouts
            ]
        ]);
    }
}

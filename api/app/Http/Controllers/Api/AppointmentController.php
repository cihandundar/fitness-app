<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\AppointmentResource;
use App\Models\Appointment;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AppointmentController extends Controller
{
    /**
     * Randevuları listeler.
     */
    public function index()
    {
        $user = Auth::user();

        if ($user->isTrainer()) {
            $appointments = Appointment::with('user:id,name,email')
                ->where('trainer_id', $user->id)
                ->orderBy('start_time', 'asc')
                ->get();
        } else {
            $appointments = Appointment::with('trainer:id,name')
                ->where('user_id', $user->id)
                ->orderBy('start_time', 'asc')
                ->get();
        }

        return response()->json([
            'status' => 'success',
            'data' => AppointmentResource::collection($appointments)
        ]);
    }

    /**
     * Randevu detayını gösterir.
     */
    public function show(Appointment $appointment)
    {
        // Yetki kontrolü - sadece ilgili kullanıcı, PT veya admin görebilir
        $user = Auth::user();
        if ($appointment->user_id !== $user->id && $appointment->trainer_id !== $user->id && !$user->isAdmin()) {
            return response()->json(['message' => 'Yetkisiz işlem.'], 403);
        }

        $appointment->load('user:id,name,email', 'trainer:id,name');

        return response()->json([
            'status' => 'success',
            'data' => new AppointmentResource($appointment)
        ]);
    }

    /**
     * Yeni randevu oluşturur (Üye tarafından veya PT tarafından).
     */
    public function store(Request $request)
    {
        $request->validate([
            'trainer_id' => 'required|exists:users,id',
            'start_time' => 'required|date|after:now',
            'end_time' => 'required|date|after:start_time',
            'notes' => 'nullable|string',
        ]);

        // Çakışma kontrolü
        $exists = Appointment::where('trainer_id', $request->trainer_id)
            ->where('status', '!=', 'cancelled')
            ->where(function ($query) use ($request) {
                $query->where(function ($q) use ($request) {
                    // Mevcut randevu yeni randevuyla çakışıyor
                    $q->where('start_time', '<', $request->end_time)
                      ->where('end_time', '>', $request->start_time);
                });
            })
            ->exists();

        if ($exists) {
            return response()->json(['message' => 'Bu saat diliminde eğitmenin başka bir randevusu bulunuyor.'], 422);
        }

        $appointment = Appointment::create([
            'trainer_id' => $request->trainer_id,
            'user_id' => $request->user_id ?? Auth::id(),
            'start_time' => $request->start_time,
            'end_time' => $request->end_time,
            'status' => 'pending',
            'notes' => $request->notes,
        ]);

        return response()->json([
            'status' => 'success',
            'message' => 'Randevu talebi oluşturuldu.',
            'data' => new AppointmentResource($appointment)
        ], 201);
    }

    /**
     * Randevu durumunu günceller (Onay/İptal).
     */
    public function update(Request $request, Appointment $appointment)
    {
        $request->validate([
            'status' => 'required|in:confirmed,cancelled,completed',
        ]);

        // Yetki kontrolü (Sadece ilgili PT veya Admin onaylayabilir)
        if ($appointment->trainer_id !== Auth::id() && !Auth::user()->isAdmin()) {
            return response()->json(['message' => 'Bu işlemi yapmaya yetkiniz yok.'], 403);
        }

        $appointment->update([
            'status' => $request->status
        ]);

        return response()->json([
            'status' => 'success',
            'message' => 'Randevu durumu güncellendi.',
            'data' => new AppointmentResource($appointment)
        ]);
    }

    /**
     * Randevuyu siler/iptal eder.
     */
    public function destroy(Appointment $appointment)
    {
        if ($appointment->user_id !== Auth::id() && $appointment->trainer_id !== Auth::id() && !Auth::user()->isAdmin()) {
            return response()->json(['message' => 'Yetkisiz işlem.'], 403);
        }

        $appointment->delete();

        return response()->json([
            'status' => 'success',
            'message' => 'Randevu iptal edildi.'
        ]);
    }
}

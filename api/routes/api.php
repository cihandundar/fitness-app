<?php

use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\ProgramController;
use App\Http\Controllers\Api\WorkoutController;
use App\Http\Controllers\Api\ExerciseController;
use App\Http\Controllers\Api\ProfileController;
use App\Http\Controllers\Api\ProgressController;
use App\Http\Controllers\Api\MembershipPlanController;
use App\Http\Controllers\Api\UserMembershipController;
use App\Http\Controllers\Api\AppointmentController;
use App\Http\Controllers\Api\PaymentController;
use App\Http\Controllers\Api\UserController;
use App\Http\Controllers\Api\TrainerController;
use App\Http\Controllers\Api\BranchController;
use App\Http\Controllers\Api\EquipmentTypeController;
use App\Http\Controllers\Api\MuscleGroupController;
use App\Http\Controllers\Api\UserPreferenceController;
use Illuminate\Support\Facades\Route;

// Guest routes - giriş gerektirmez, herkes erişebilir
Route::middleware('guest')->group(function () {
    Route::prefix('auth')->group(function () {
        Route::get('/check-email', [AuthController::class, 'checkEmail']);
        Route::post('/register', [AuthController::class, 'register']);
        Route::post('/register-complete', [AuthController::class, 'registerComplete']);
        Route::post('/login', [AuthController::class, 'login']);
    });
});

// Public routes - herkes erişebilir (giriş yapmış/yapmamış fark etmez)
Route::get('membership-plans', [MembershipPlanController::class, 'index']);
Route::get('membership-plans/{membershipPlan}', [MembershipPlanController::class, 'show']);
Route::get('branches', [BranchController::class, 'index']);
Route::get('branches/{branch}', [BranchController::class, 'show']);
Route::get('programs', [ProgramController::class, 'index']);
Route::get('programs/{program}', [ProgramController::class, 'show']);

// Protected routes - giriş gerektirir
Route::middleware('auth:sanctum')->group(function () {
    Route::post('/auth/logout', [AuthController::class, 'logout']);
    Route::get('/auth/me', [AuthController::class, 'me']);

    // Workouts API
    Route::apiResource('workouts', WorkoutController::class);

    // Exercises API (okuma — tüm giriş yapmış kullanıcılar)
    Route::get('exercises', [ExerciseController::class, 'index']);
    Route::get('exercises/{exercise}', [ExerciseController::class, 'show']);

    // Kas grupları & ekipman listesi (form/select için)
    Route::get('muscle-groups', [MuscleGroupController::class, 'index']);
    Route::get('equipment-types', [EquipmentTypeController::class, 'index']);

    // User Preferences API
    Route::prefix('user')->group(function () {
        Route::get('preferences', [UserPreferenceController::class, 'getPreferences']);
        Route::post('preferences', [UserPreferenceController::class, 'updatePreferences']);
        Route::get('available-options', [UserPreferenceController::class, 'getAvailableOptions']);
    });

    // Appointments API
    Route::apiResource('appointments', AppointmentController::class);

    // Workout Logging API
    Route::prefix('workout-tracking')->group(function () {
        Route::post('start', [App\Http\Controllers\Api\WorkoutLoggingController::class, 'startWorkout']);
        Route::post('log-set', [App\Http\Controllers\Api\WorkoutLoggingController::class, 'logSet']);
        Route::post('finish/{session}', [App\Http\Controllers\Api\WorkoutLoggingController::class, 'finishWorkout']);
        Route::get('history', [App\Http\Controllers\Api\WorkoutLoggingController::class, 'getHistory']);
        Route::get('exercise-history/{exerciseId}', [App\Http\Controllers\Api\WorkoutLoggingController::class, 'getExerciseHistory']);
    });

    // Admin Only - Write Operations
    Route::middleware('admin')->group(function () {
        Route::post('programs/{program}/upload-image', [ProgramController::class, 'uploadImage']);
        Route::apiResource('programs', ProgramController::class)->except(['index', 'show']);
        Route::apiResource('exercises', ExerciseController::class)->except(['index', 'show']);
        Route::apiResource('branches', BranchController::class)->except(['index', 'show']);
        Route::post('branches/update-order', [BranchController::class, 'updateOrder']);
        Route::post('branches/{branch}/upload-image', [BranchController::class, 'uploadImage']);
        Route::apiResource('membership-plans', MembershipPlanController::class)->except(['index']);
        Route::get('user-memberships/pending', [UserMembershipController::class, 'pending']);
        Route::post('user-memberships/{userMembership}/approve', [UserMembershipController::class, 'approve']);
        Route::post('user-memberships/{userMembership}/reject', [UserMembershipController::class, 'reject']);
        Route::apiResource('user-memberships', UserMembershipController::class);
        Route::get('payments', [PaymentController::class, 'index']);
        Route::get('payments/{payment}', [PaymentController::class, 'show']);
        Route::put('payments/{payment}', [PaymentController::class, 'update']);
        Route::apiResource('users', UserController::class);
        Route::get('users/{user}/membership', [UserController::class, 'getUserMembership']);
        Route::get('users/{user}/programs', [UserController::class, 'getUserPrograms']);
        Route::get('users/{user}/progress', [UserController::class, 'getUserProgress']);
        Route::get('all-workout-history', [App\Http\Controllers\Api\WorkoutLoggingController::class, 'getAllHistory']);

        // Equipment & Muscle Groups Management
        Route::apiResource('equipment-types', EquipmentTypeController::class)->except(['index']);
        Route::apiResource('muscle-groups', MuscleGroupController::class)->except(['index']);
        Route::post('equipment-types/reorder', [EquipmentTypeController::class, 'reorder']);
        Route::post('muscle-groups/reorder', [MuscleGroupController::class, 'reorder']);
    });

    // Trainer Only
    Route::middleware('trainer')->prefix('trainer')->group(function () {
        Route::get('/clients', [TrainerController::class, 'clients']);
        Route::post('/add-client', [TrainerController::class, 'addClient']);
        Route::post('/assign-program', [TrainerController::class, 'assignProgram']);
        Route::get('/client-progress/{clientId}', [TrainerController::class, 'clientProgress']);
    });

    // Profile API
    Route::prefix('profile')->group(function () {
        Route::get('/', [ProfileController::class, 'show']);
        Route::put('/', [ProfileController::class, 'update']);
        Route::put('/password', [ProfileController::class, 'updatePassword']);
        Route::delete('/', [ProfileController::class, 'destroy']);
    });

    // Payment API - Sadece görüntüleme (Online ödeme kaldırıldı)
    Route::get('payments/my', [PaymentController::class, 'myPayments']);

    // User Membership API - Kullanıcı işlemleri
    Route::get('my-membership', [UserMembershipController::class, 'myMembership']);
    Route::post('my-membership/cancel', [UserMembershipController::class, 'cancelMyMembership']);

    // Progress API
    Route::prefix('progress')->group(function () {
        Route::get('/', [ProgressController::class, 'index']);
        Route::post('/', [ProgressController::class, 'store']);
        Route::get('/stats', [ProgressController::class, 'stats']);
        Route::get('/{progressLog}', [ProgressController::class, 'show']);
        Route::put('/{progressLog}', [ProgressController::class, 'update']);
        Route::delete('/{progressLog}', [ProgressController::class, 'destroy']);
    });
});

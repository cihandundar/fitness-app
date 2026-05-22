<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

// Inspire command
Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

// ============================================
// 📅 SCHEDULED TASKS
// ============================================

// Her saat başı: Randevusu 2 saat sonra olanlara hatırlatma gönder
Schedule::command('appointments:send-reminders --hours=2')
    ->hourly()
    ->description('Randevu hatırlatmalarını gönderir')
    ->onSuccess(function () {
        \Log::info('Appointment reminders scheduled task completed');
    })
    ->onFailure(function () {
        \Log::error('Appointment reminders scheduled task failed');
    });

// Her gün gece yarısı: 3 gün içinde bitecek üyelikler için hatırlatma
Schedule::command('memberships:check-expiry --days=3')
    ->daily()
    ->at('00:00')
    ->description('Üyelik bitiş hatırlatmalarını gönderir')
    ->onSuccess(function () {
        \Log::info('Membership expiry check completed');
    })
    ->onFailure(function () {
        \Log::error('Membership expiry check failed');
    });

// Her hafta pazar günü: 365 gündür aktif olmayan kullanıcıları temizle
Schedule::command('users:cleanup-inactive --days=365 --soft')
    ->weekly()
    ->sundays()
    ->at('02:00')
    ->description('Pasif kullanıcıları temizler')
    ->onSuccess(function () {
        \Log::info('Inactive users cleanup completed');
    })
    ->onFailure(function () {
        \Log::error('Inactive users cleanup failed');
    });

// ============================================
// 🔧 MAINTENANCE TASKS
// ============================================

// Queue worker'ların çalıştığından emin olmak için health check
Schedule::call(function () {
    \Log::info('Queue health check', [
        'pending_jobs' => \Illuminate\Support\Facades\DB::table('jobs')->count(),
        'failed_jobs' => \Illuminate\Support\Facades\DB::table('failed_jobs')->count()
    ]);
})->everyFiveMinutes();

// Her gün: Başarısız job'ları temizle (30 günden eski)
Schedule::call(function () {
    \Illuminate\Support\Facades\DB::table('failed_jobs')
        ->where('failed_at', '<', now()->subDays(30))
        ->delete();
    \Log::info('Old failed jobs cleaned up');
})->daily();

// ============================================
// 📊 REPORTS & ANALYTICS
// ============================================

// Her gün: Günlük özet raporu
Schedule::call(function () {
    $stats = [
        'new_users' => \App\Models\User::whereDate('created_at', today())->count(),
        'new_memberships' => \App\Models\UserMembership::whereDate('created_at', today())->count(),
        'completed_workouts' => \App\Models\CompletedWorkout::whereDate('created_at', today())->count(),
        'payments' => \App\Models\Payment::whereDate('created_at', today())->sum('amount'),
    ];

    \Log::info('Daily stats summary', $stats);
})->dailyAt('23:59');

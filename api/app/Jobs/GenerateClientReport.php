<?php

namespace App\Jobs;

use App\Models\User;
use App\Models\ProgressLog;
use App\Models\CompletedWorkout;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Exception;
use PDF;

class GenerateClientReport implements ShouldQueue
{
    use Queueable;

    /**
     * The number of times the job may be attempted.
     */
    public int $tries = 2;

    /**
     * The maximum number of seconds the job can run.
     * Rapor generation uzun sürebilir
     */
    public int $timeout = 300;

    /**
     * Create a new job instance.
     */
    public function __construct(
        public User $client,
        public User $trainer,
        public string $reportType, // 'weekly', 'monthly', 'custom'
        public ?string $startDate = null,
        public ?string $endDate = null
    ) {
        $this->onQueue('reports');
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        try {
            // Tarih aralığını belirle
            $startDate = $this->startDate ?? now()->subMonth();
            $endDate = $this->endDate ?? now();

            // Verileri topla
            $progressLogs = ProgressLog::where('user_id', $this->client->id)
                ->whereBetween('logged_at', [$startDate, $endDate])
                ->orderBy('logged_at')
                ->get();

            $completedWorkouts = CompletedWorkout::where('user_id', $this->client->id)
                ->whereBetween('completed_at', [$startDate, $endDate])
                ->with('exerciseLogs')
                ->get();

            // İstatistikleri hesapla
            $stats = [
                'total_workouts' => $completedWorkouts->count(),
                'total_duration' => $completedWorkouts->sum('duration_minutes'),
                'weight_change' => $this->calculateWeightChange($progressLogs),
                'strength_gain' => $this->calculateStrengthGain($completedWorkouts),
                'consistency' => $this->calculateConsistency($completedWorkouts, $startDate, $endDate),
            ];

            // PDF oluştur
            $pdfData = [
                'client' => $this->client,
                'trainer' => $this->trainer,
                'reportType' => $this->reportType,
                'period' => [
                    'start' => $startDate,
                    'end' => $endDate,
                ],
                'stats' => $stats,
                'progressLogs' => $progressLogs,
                'completedWorkouts' => $completedWorkouts,
            ];

            $filename = "reports/client-{$this->client->id}-{$this->reportType}-{$endDate->format('Y-m-d')}.pdf";
            $pdf = PDF::loadView('reports.client', $pdfData);

            // Dosyayı kaydet
            Storage::disk('public')->put($filename, $pdf->output());

            Log::info('Client report generated', [
                'client_id' => $this->client->id,
                'trainer_id' => $this->trainer->id,
                'type' => $this->reportType,
                'filename' => $filename
            ]);

            // Notification ile trainer'a bilgilendir
            // $this->trainer->notify(new ReportReadyNotification($filename));

        } catch (Exception $e) {
            Log::error('Failed to generate client report', [
                'client_id' => $this->client->id,
                'trainer_id' => $this->trainer->id,
                'error' => $e->getMessage()
            ]);
            throw $e;
        }
    }

    /**
     * Kilo değişimini hesapla
     */
    protected function calculateWeightChange($progressLogs): array
    {
        if ($progressLogs->isEmpty()) {
            return ['start' => null, 'end' => null, 'change' => 0];
        }

        return [
            'start' => $progressLogs->first()->weight,
            'end' => $progressLogs->last()->weight,
            'change' => $progressLogs->last()->weight - $progressLogs->first()->weight,
        ];
    }

    /**
     * Kuvvet artışını hesapla
     */
    protected function calculateStrengthGain($completedWorkouts): array
    {
        // İlk ve son egzersizleri karşılaştır
        $firstWorkout = $completedWorkouts->first();
        $lastWorkout = $completedWorkouts->last();

        if (!$firstWorkout || !$lastWorkout) {
            return ['improvement' => 0];
        }

        // Basit karşılaştırma - detaylı analiz yapılabilir
        return [
            'first_total_weight' => $firstWorkout->exerciseLogs->sum('weight'),
            'last_total_weight' => $lastWorkout->exerciseLogs->sum('weight'),
            'improvement' => $lastWorkout->exerciseLogs->sum('weight') - $firstWorkout->exerciseLogs->sum('weight'),
        ];
    }

    /**
     * Tutarlılığı hesapla (haftalık antrenoran sayısı)
     */
    protected function calculateConsistency($completedWorkouts, $startDate, $endDate): float
    {
        $weeks = $startDate->diffInWeeks($endDate) ?: 1;
        return round($completedWorkouts->count() / $weeks, 1);
    }

    /**
     * Handle a job failure.
     */
    public function failed(Exception $exception): void
    {
        Log::error('Client report generation job failed permanently', [
            'client_id' => $this->client->id,
            'error' => $exception->getMessage()
        ]);
    }
}

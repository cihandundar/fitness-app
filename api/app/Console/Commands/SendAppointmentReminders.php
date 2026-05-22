<?php

namespace App\Console\Commands;

use App\Models\Appointment;
use App\Jobs\SendAppointmentReminder;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;

class SendAppointmentReminders extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'appointments:send-reminders
                            {--hours=2 : Kaç saat öncesinden hatırlatma atılacağı}
                            {--dry-run : Test modu, gerçek işlem yapmaz}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Yaklaşan randevular için hatırlatma gönderir';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $hoursBefore = (int) $this->option('hours');
        $isDryRun = $this->option('dry-run') !== false;

        $this->info("Randevu hatırlatmaları kontrol ediliyor... ({$hoursBefore} saat öncesi)");

        // Şu andan X saat sonraki zaman aralığındaki randevuları bul
        $startTime = now()->addHours($hoursBefore)->startOfHour();
        $endTime = now()->addHours($hoursBefore)->endOfHour();

        $upcomingAppointments = Appointment::where('status', 'confirmed')
            ->whereBetween('start_time', [$startTime, $endTime])
            ->with(['user', 'trainer'])
            ->get();

        $this->info("{$upcomingAppointments->count()} randevu {$hoursBefore} saat içinde başlayacak");

        if ($upcomingAppointments->isEmpty()) {
            $this->info('İşlenecek randevu bulunamadı.');
            return self::SUCCESS;
        }

        if ($isDryRun) {
            $this->warn('DRY RUN modu - Hiçbir işlem yapılmayacak:');
            foreach ($upcomingAppointments as $appointment) {
                $time = Carbon::parse($appointment->start_time)->format('H:i');
                $this->line("  {$time} - {$appointment->user->name} & {$appointment->trainer->name}");
            }
            return self::SUCCESS;
        }

        $bar = $this->output->createProgressBar($upcomingAppointments->count() * 2); // user + trainer
        $bar->start();

        foreach ($upcomingAppointments as $appointment) {
            try {
                // Kullanıcıya hatırlatma
                SendAppointmentReminder::dispatch($appointment, 'user');
                $bar->advance();

                // Eğitmene hatırlatma
                SendAppointmentReminder::dispatch($appointment, 'trainer');
                $bar->advance();

                $time = Carbon::parse($appointment->start_time)->format('H:i');
                $this->line("  ✓ {$time} - {$appointment->user->name} & {$appointment->trainer->name}");
            } catch (\Exception $e) {
                $this->error("  ✗ Randevu #{$appointment->id} - {$e->getMessage()}");
                Log::error('Appointment reminder dispatch failed', [
                    'appointment_id' => $appointment->id,
                    'error' => $e->getMessage()
                ]);
            }
        }

        $bar->finish();
        $this->newLine(2);
        $this->info('Tamamlandı! Hatırlatmalar kuyruğa eklendi.');

        Log::info('Appointment reminders check completed', [
            'processed_count' => $upcomingAppointments->count(),
            'hours_before' => $hoursBefore
        ]);

        return self::SUCCESS;
    }
}

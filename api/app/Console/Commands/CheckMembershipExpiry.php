<?php

namespace App\Console\Commands;

use App\Models\UserMembership;
use App\Jobs\SendMembershipExpiryReminder;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;

class CheckMembershipExpiry extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'memberships:check-expiry
                            {--days=3 : Kaç gün öncesinden hatırlatma atılacağı}
                            {--dry-run : Test modu, gerçek işlem yapmaz}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Üyelikleri kontrol eder, bitmek üzere olanlar için hatırlatma gönderir';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $daysBefore = (int) $this->option('days');
        $isDryRun = $this->option('dry-run') !== false;

        $this->info("Üyelik kontrolleri başlatılıyor... ({$daysBefore} gün öncesi)");

        // Bugünden X gün sonra bitecek aktif üyelikleri bul
        $expiryDate = now()->addDays($daysBefore)->startOfDay();

        $expiringMemberships = UserMembership::where('status', 'active')
            ->whereDate('end_date', $expiryDate)
            ->with(['user', 'plan'])
            ->get();

        $this->info("{$expiringMemberships->count()} üyelik {$daysBefore} gün içinde sona eriyor");

        if ($expiringMemberships->isEmpty()) {
            $this->info('İşlenecek üyelik bulunamadı.');
            return self::SUCCESS;
        }

        if ($isDryRun) {
            $this->warn('DRY RUN modu - Hiçbir işlem yapılmayacak:');
            foreach ($expiringMemberships as $membership) {
                $this->line("  - {$membership->user->name} ({$membership->user->email}) - {$membership->plan->name}");
            }
            return self::SUCCESS;
        }

        // Her bir üyelik için reminder job'ı dispatch et
        $bar = $this->output->createProgressBar($expiringMemberships->count());
        $bar->start();

        foreach ($expiringMemberships as $membership) {
            try {
                SendMembershipExpiryReminder::dispatch($membership, $daysBefore);
                $this->line("  ✓ {$membership->user->email}");
            } catch (\Exception $e) {
                $this->error("  ✗ {$membership->user->email} - {$e->getMessage()}");
                Log::error('Membership expiry reminder dispatch failed', [
                    'membership_id' => $membership->id,
                    'error' => $e->getMessage()
                ]);
            }

            $bar->advance();
        }

        $bar->finish();
        $this->newLine(2);
        $this->info('Tamamlandı! Hatırlatmalar kuyruğa eklendi.');

        Log::info('Membership expiry check completed', [
            'processed_count' => $expiringMemberships->count(),
            'days_before' => $daysBefore
        ]);

        return self::SUCCESS;
    }
}

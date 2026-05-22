<?php

namespace App\Console\Commands;

use App\Models\User;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;

class CleanupInactiveUsers extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'users:cleanup-inactive
                            {--days=365 : Kaç gündür aktif olmayan kullanıcılar temizlenecek}
                            {--soft : Soft delete kullan, kalıcı olarak silme}
                            {--dry-run : Test modu, gerçek işlem yapmaz}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Belirli bir süredir aktif olmayan kullanıcıları temizler';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $daysInactive = (int) $this->option('days');
        $softDelete = $this->option('soft') !== false;
        $isDryRun = $this->option('dry-run') !== false;

        $thresholdDate = now()->subDays($daysInactive);

        $this->info("{$daysInactive} gündür aktif olmayan kullanıcılar aranıyor...");
        $this->info("Son aktivite tarihi öncesi: " . $thresholdDate->format('Y-m-d H:i:s'));

        // Hiç login olmamış veya son login'i eski olan kullanıcıları bul
        $inactiveUsers = User::where(function ($query) use ($thresholdDate) {
                $query->where('last_login_at', '<', $thresholdDate)
                    ->orWhereNull('last_login_at');
            })
            ->where('created_at', '<', $thresholdDate) // Hesap da en az X gün önceden oluşturulmuş olmalı
            ->whereDoesntHave('payments') // Hiç ödeme yapmamış
            ->whereDoesntHave('memberships') // Hiç üyeliği olmamış
            ->get();

        $this->info("{$inactiveUsers->count()} pasif kullanıcı bulundu");

        if ($inactiveUsers->isEmpty()) {
            $this->info('Temizlenecek kullanıcı yok.');
            return self::SUCCESS;
        }

        if ($isDryRun) {
            $this->warn('DRY RUN modu - Hiçbir işlem yapılmayacak:');
            $this->table(
                ['ID', 'İsim', 'Email', 'Oluşturulma', 'Son Login'],
                $inactiveUsers->take(10)->map(function ($user) {
                    return [
                        $user->id,
                        $user->name,
                        $user->email,
                        $user->created_at->format('Y-m-d'),
                        $user->last_login_at?->format('Y-m-d') ?? 'Hiç'
                    ];
                })
            );
            if ($inactiveUsers->count() > 10) {
                $this->line("... ve {$inactiveUsers->count() - 10} kullanıcı daha");
            }
            return self::SUCCESS;
        }

        if (!$this->confirm("{$inactiveUsers->count()} kullanıcıyı temizlemek istediğinize emin misiniz?")) {
            $this->info('İptal edildi.');
            return self::SUCCESS;
        }

        $bar = $this->output->createProgressBar($inactiveUsers->count());
        $bar->start();

        $deletedCount = 0;
        foreach ($inactiveUsers as $user) {
            try {
                if ($softDelete) {
                    $user->delete(); // Soft delete
                } else {
                    $user->forceDelete(); // Hard delete
                }
                $deletedCount++;
            } catch (\Exception $e) {
                $this->newLine();
                $this->error("Hata (User #{$user->id}): {$e->getMessage()}");
                Log::error('Inactive user cleanup failed', [
                    'user_id' => $user->id,
                    'error' => $e->getMessage()
                ]);
            }
            $bar->advance();
        }

        $bar->finish();
        $this->newLine(2);

        $action = $softDelete ? 'soft delete edildi' : 'kalıcı olarak silindi';
        $this->info("Tamamlandı! {$deletedCount} kullanıcı {$action}.");

        Log::info('Inactive users cleanup completed', [
            'deleted_count' => $deletedCount,
            'soft_delete' => $softDelete,
            'days_inactive' => $daysInactive
        ]);

        return self::SUCCESS;
    }
}

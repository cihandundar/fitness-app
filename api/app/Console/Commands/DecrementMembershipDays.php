<?php

namespace App\Console\Commands;

use App\Models\TrainerClient;
use App\Models\UserMembership;
use Illuminate\Console\Command;

class DecrementMembershipDays extends Command
{
    protected $signature = 'memberships:decrement-days';
    protected $description = 'Günlük olarak üyelik ve öğrenci paketlerinden gün düşürür.';

    public function handle()
    {
        // User üyeliklerinden gün düş
        $userMemberships = UserMembership::where('status', 'active')
            ->where('remaining_days', '>', 0)
            ->whereDate('last_check_in', '<', today())
            ->get();

        foreach ($userMemberships as $membership) {
            $membership->decrement('remaining_days');
            $membership->update(['last_check_in' => now()]);

            if ($membership->remaining_days <= 0) {
                $membership->update(['status' => 'expired']);
            }
        }

        $this->info("User üyeliklerinden gün düşüldü: {$userMemberships->count()} kayıt.");

        // Trainer öğrencilerinden gün düş
        $trainerClients = TrainerClient::where('status', 'active')
            ->where('remaining_days', '>', 0)
            ->whereDate('last_check_in', '<', today())
            ->get();

        foreach ($trainerClients as $client) {
            $client->decrement('remaining_days');
            $client->update(['last_check_in' => now()]);

            if ($client->remaining_days <= 0) {
                $client->update(['status' => 'completed']);
            }
        }

        $this->info("Trainer öğrencilerinden gün düşüldü: {$trainerClients->count()} kayıt.");

        return Command::SUCCESS;
    }
}

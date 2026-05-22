<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class HomepageContentSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Branches
        $branches = [
            ['name' => 'Fitness', 'icon' => 'fitness-icon', 'order' => 1],
            ['name' => 'Crossfit', 'icon' => 'crossfit-icon', 'order' => 2],
            ['name' => 'Kick Boks', 'icon' => 'kickboks-icon', 'order' => 3],
            ['name' => 'Boks', 'icon' => 'boks-icon', 'order' => 4],
            ['name' => 'Muay Thai', 'icon' => 'muaythai-icon', 'order' => 5],
            ['name' => 'Karate', 'icon' => 'karate-icon', 'order' => 6],
            ['name' => 'Tekvando', 'icon' => 'tekvando-icon', 'order' => 7],
            ['name' => 'Wing Chun', 'icon' => 'wingchun-icon', 'order' => 8],
        ];

        foreach ($branches as $branch) {
            \App\Models\Branch::updateOrCreate(
                ['slug' => \Illuminate\Support\Str::slug($branch['name'])],
                [
                    'name' => $branch['name'],
                    'icon' => $branch['icon'],
                    'order' => $branch['order'],
                    'is_active' => true,
                ]
            );
        }

        // Membership Plans
        $plans = [
            [
                'name' => 'Standart',
                'price' => 499,
                'type' => 'gym',
                'features' => ['Tüm branşlara erişim', 'Temel antrenman programları', 'Mobil uygulama desteği'],
                'is_featured' => false
            ],
            [
                'name' => 'Premium',
                'price' => 799,
                'type' => 'hybrid',
                'features' => ['Standart özelliklerin tamamı', 'Kişiye özel antrenman planı', 'Beslenme rehberi', 'Eğitmen desteği (Mesajlaşma)'],
                'is_featured' => true
            ],
            [
                'name' => 'Elite',
                'price' => 1299,
                'type' => 'pt',
                'features' => ['Premium özelliklerin tamamı', 'Haftalık birebir PT seansı (4 seans)', 'Haftalık gelişim analizi', 'Özel grup dersleri'],
                'is_featured' => false
            ],
        ];

        foreach ($plans as $plan) {
            \App\Models\MembershipPlan::updateOrCreate(
                ['slug' => \Illuminate\Support\Str::slug($plan['name'])],
                [
                    'name' => $plan['name'],
                    'price' => $plan['price'],
                    'type' => $plan['type'],
                    'features' => $plan['features'],
                    'is_featured' => $plan['is_featured'],
                    'is_active' => true,
                ]
            );
        }
    }
}

<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class TrainerSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        \App\Models\User::create([
            'name' => 'PT Can',
            'email' => 'pt@fitness.com',
            'password' => \Illuminate\Support\Facades\Hash::make('password'),
            'role' => 'trainer',
            'is_active' => true,
        ]);
    }
}

<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('exercise_logs', function (Blueprint $blueprint) {
            $blueprint->id();
            $blueprint->foreignId('completed_workout_id')->constrained('completed_workouts')->onDelete('cascade');
            $blueprint->foreignId('exercise_id')->constrained()->onDelete('cascade');
            $blueprint->integer('set_number');
            $blueprint->float('weight')->nullable();
            $blueprint->integer('reps')->nullable();
            $blueprint->integer('rest_time')->nullable(); // in seconds
            $blueprint->boolean('is_completed')->default(true);
            $blueprint->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('exercise_logs');
    }
};

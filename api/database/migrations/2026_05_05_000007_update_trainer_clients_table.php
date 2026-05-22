<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('trainer_clients', function (Blueprint $table) {
            $table->integer('total_days')->default(0)->after('notes');
            $table->integer('remaining_days')->default(0)->after('total_days');
            $table->timestamp('last_check_in')->nullable()->after('remaining_days');
        });
    }

    public function down(): void
    {
        Schema::table('trainer_clients', function (Blueprint $table) {
            $table->dropColumn(['total_days', 'remaining_days', 'last_check_in']);
        });
    }
};

<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('user_memberships', function (Blueprint $table) {
            $table->integer('remaining_days')->default(0)->after('status');
            $table->integer('total_days')->default(0)->after('remaining_days');
            $table->timestamp('last_check_in')->nullable()->after('total_days');
        });
    }

    public function down(): void
    {
        Schema::table('user_memberships', function (Blueprint $table) {
            $table->dropColumn(['remaining_days', 'total_days', 'last_check_in']);
        });
    }
};

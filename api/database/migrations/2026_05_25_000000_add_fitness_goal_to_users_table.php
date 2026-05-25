<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasColumn('users', 'fitness_goal')) {
            Schema::table('users', function (Blueprint $table) {
                $table->string('fitness_goal')->nullable()->after('weight');
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasColumn('users', 'fitness_goal')) {
            Schema::table('users', function (Blueprint $table) {
                $table->dropColumn('fitness_goal');
            });
        }
    }
};

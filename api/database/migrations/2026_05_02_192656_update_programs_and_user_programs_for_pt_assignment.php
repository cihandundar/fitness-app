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
        Schema::table('programs', function (Blueprint $table) {
            $table->boolean('is_custom')->default(false)->after('is_featured');
            $table->foreignId('target_user_id')->nullable()->after('is_custom')->constrained('users')->onDelete('cascade');
        });

        Schema::table('user_programs', function (Blueprint $table) {
            $table->foreignId('assigned_by')->nullable()->after('program_id')->constrained('users')->onDelete('set null');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('programs', function (Blueprint $table) {
            $table->dropColumn(['is_custom', 'target_user_id']);
        });

        Schema::table('user_programs', function (Blueprint $table) {
            $table->dropColumn('assigned_by');
        });
    }
};

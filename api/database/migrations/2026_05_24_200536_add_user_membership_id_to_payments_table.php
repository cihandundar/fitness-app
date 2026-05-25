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
        Schema::table('payments', function (Blueprint $table) {
            // Eğer sütun yoksa ekle
            if (!Schema::hasColumn('payments', 'user_membership_id')) {
                $table->unsignedBigInteger('user_membership_id')->nullable()->after('user_id');
                $table->foreign('user_membership_id')->references('id')->on('user_memberships')->onDelete('set null');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('payments', function (Blueprint $table) {
            if (Schema::hasColumn('payments', 'user_membership_id')) {
                $table->dropForeign(['user_membership_id']);
                $table->dropColumn('user_membership_id');
            }
        });
    }
};

<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('exercises', function (Blueprint $table) {
            // String'i foreign key'e çevir
            $table->foreignId('muscle_group_id')->nullable()->after('description')->constrained('muscle_groups');
            $table->foreignId('equipment_type_id')->nullable()->after('muscle_group_id')->constrained('equipment_types');

            // Eski column'ları drop et (veri varsa迁移 etmek lazım)
            // $table->dropColumn(['muscle_group', 'equipment']);
        });
    }

    public function down(): void
    {
        Schema::table('exercises', function (Blueprint $table) {
            $table->dropForeign(['muscle_group_id']);
            $table->dropForeign(['equipment_type_id']);
            $table->dropColumn(['muscle_group_id', 'equipment_type_id']);
        });
    }
};

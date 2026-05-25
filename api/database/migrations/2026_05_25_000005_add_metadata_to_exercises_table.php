<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('exercises', function (Blueprint $table) {
            if (! Schema::hasColumn('exercises', 'is_active')) {
                $table->boolean('is_active')->default(true)->after('difficulty');
            }
            if (! Schema::hasColumn('exercises', 'instructions')) {
                $table->text('instructions')->nullable()->after('description');
            }
            if (! Schema::hasColumn('exercises', 'tips')) {
                $table->text('tips')->nullable()->after('instructions');
            }
        });
    }

    public function down(): void
    {
        Schema::table('exercises', function (Blueprint $table) {
            $cols = array_filter([
                Schema::hasColumn('exercises', 'tips') ? 'tips' : null,
                Schema::hasColumn('exercises', 'instructions') ? 'instructions' : null,
                Schema::hasColumn('exercises', 'is_active') ? 'is_active' : null,
            ]);
            if ($cols) {
                $table->dropColumn($cols);
            }
        });
    }
};

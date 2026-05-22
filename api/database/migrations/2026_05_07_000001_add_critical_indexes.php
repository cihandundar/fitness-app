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
        // USERS TABLE - En kritik (auth sorguları)
        Schema::table('users', function (Blueprint $table) {
            $table->index('role', 'users_role_index')->after('email');
            $table->index(['role', 'is_active'], 'users_role_active_index')->after('email');
        });

        // USER MEMBERSHIPS TABLE - Üyelik sorguları için kritik
        Schema::table('user_memberships', function (Blueprint $table) {
            $table->index('user_id', 'user_memberships_user_id_index');
            $table->index('status', 'user_memberships_status_index');
            $table->index('end_date', 'user_memberships_end_date_index');
            $table->index(['user_id', 'status'], 'user_memberships_user_status_index');
        });

        // APPOINTMENTS TABLE - Randevu sorguları
        Schema::table('appointments', function (Blueprint $table) {
            $table->index('trainer_id', 'appointments_trainer_id_index');
            $table->index('user_id', 'appointments_user_id_index');
            $table->index('start_time', 'appointments_start_time_index');
            $table->index('status', 'appointments_status_index');
        });

        // PAYMENTS TABLE - Ödeme sorguları
        Schema::table('payments', function (Blueprint $table) {
            $table->index('user_id', 'payments_user_id_index');
            $table->index('status', 'payments_status_index');
            $table->index('created_at', 'payments_created_at_index');
            $table->index(['user_id', 'status'], 'payments_user_status_index');
        });

        // PROGRAMS TABLE - Program listeleme
        Schema::table('programs', function (Blueprint $table) {
            $table->index('is_active', 'programs_is_active_index');
            $table->index('level', 'programs_level_index');
        });

        // WORKOUTS TABLE - Antrenman program ilişkisi
        Schema::table('workouts', function (Blueprint $table) {
            $table->index('program_id', 'workouts_program_id_index');
            $table->index('day_number', 'workouts_day_number_index');
        });

        // EXERCISES TABLE - Egzersiz arama
        Schema::table('exercises', function (Blueprint $table) {
            $table->index('name', 'exercises_name_index');
            $table->index('difficulty', 'exercises_difficulty_index');
        });

        // COMPLETED WORKOUTS TABLE - Kullanıcı antrenman geçmişi
        Schema::table('completed_workouts', function (Blueprint $table) {
            $table->index('user_id', 'completed_workouts_user_id_index');
            $table->index('started_at', 'completed_workouts_started_at_index');
        });

        // BRANCHES TABLE - Branş sıralama
        Schema::table('branches', function (Blueprint $table) {
            $table->index('order', 'branches_order_index');
            $table->index('is_active', 'branches_is_active_index');
        });

        // EQUIPMENT TYPES TABLE - Ekipman sıralama
        Schema::table('equipment_types', function (Blueprint $table) {
            $table->index('sort_order', 'equipment_types_sort_order_index');
            $table->index('is_active', 'equipment_types_is_active_index');
        });

        // MUSCLE GROUPS TABLE - Kas grubu sıralama
        Schema::table('muscle_groups', function (Blueprint $table) {
            $table->index('sort_order', 'muscle_groups_sort_order_index');
            $table->index('is_active', 'muscle_groups_is_active_index');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropIndex('users_role_index');
            $table->dropIndex('users_role_active_index');
        });

        Schema::table('user_memberships', function (Blueprint $table) {
            $table->dropIndex('user_memberships_user_id_index');
            $table->dropIndex('user_memberships_status_index');
            $table->dropIndex('user_memberships_end_date_index');
            $table->dropIndex('user_memberships_user_status_index');
        });

        Schema::table('appointments', function (Blueprint $table) {
            $table->dropIndex('appointments_trainer_id_index');
            $table->dropIndex('appointments_user_id_index');
            $table->dropIndex('appointments_start_time_index');
            $table->dropIndex('appointments_status_index');
        });

        Schema::table('payments', function (Blueprint $table) {
            $table->dropIndex('payments_user_id_index');
            $table->dropIndex('payments_status_index');
            $table->dropIndex('payments_created_at_index');
            $table->dropIndex('payments_user_status_index');
        });

        Schema::table('programs', function (Blueprint $table) {
            $table->dropIndex('programs_is_active_index');
            $table->dropIndex('programs_level_index');
        });

        Schema::table('workouts', function (Blueprint $table) {
            $table->dropIndex('workouts_program_id_index');
            $table->dropIndex('workouts_day_number_index');
        });

        Schema::table('exercises', function (Blueprint $table) {
            $table->dropIndex('exercises_name_index');
            $table->dropIndex('exercises_difficulty_index');
        });

        Schema::table('completed_workouts', function (Blueprint $table) {
            $table->dropIndex('completed_workouts_user_id_index');
            $table->dropIndex('completed_workouts_started_at_index');
        });

        Schema::table('branches', function (Blueprint $table) {
            $table->dropIndex('branches_order_index');
            $table->dropIndex('branches_is_active_index');
        });

        Schema::table('equipment_types', function (Blueprint $table) {
            $table->dropIndex('equipment_types_sort_order_index');
            $table->dropIndex('equipment_types_is_active_index');
        });

        Schema::table('muscle_groups', function (Blueprint $table) {
            $table->dropIndex('muscle_groups_sort_order_index');
            $table->dropIndex('muscle_groups_is_active_index');
        });
    }
};

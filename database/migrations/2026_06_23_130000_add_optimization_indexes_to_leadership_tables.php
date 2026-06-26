<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('tasks', function (Blueprint $table) {
            $table->index('employee_id');
        });

        Schema::table('attendences', function (Blueprint $table) {
            $table->index('employee_id');
        });

        Schema::table('commit_logs', function (Blueprint $table) {
            $table->index('employee_id');
        });

        Schema::table('employee_meeting', function (Blueprint $table) {
            $table->index('employee_id');
        });

        Schema::table('employees', function (Blueprint $table) {
            $table->index('team_id');
        });

        Schema::table('projects', function (Blueprint $table) {
            $table->index('status');
            $table->index('end_date');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('tasks', function (Blueprint $table) {
            $table->dropIndex(['employee_id']);
        });

        Schema::table('attendences', function (Blueprint $table) {
            $table->dropIndex(['employee_id']);
        });

        Schema::table('commit_logs', function (Blueprint $table) {
            $table->dropIndex(['employee_id']);
        });

        Schema::table('employee_meeting', function (Blueprint $table) {
            $table->dropIndex(['employee_id']);
        });

        Schema::table('employees', function (Blueprint $table) {
            $table->dropIndex(['team_id']);
        });

        Schema::table('projects', function (Blueprint $table) {
            $table->dropIndex(['status']);
            $table->dropIndex(['end_date']);
        });
    }
};

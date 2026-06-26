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
            $table->index('assigned_date');
            $table->index('completed_date');
            $table->index('due_date');
            $table->index('status');
        });

        Schema::table('commit_logs', function (Blueprint $table) {
            $table->index('commit_date');
        });

        Schema::table('attendences', function (Blueprint $table) {
            $table->index('date');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('tasks', function (Blueprint $table) {
            $table->dropIndex(['assigned_date']);
            $table->dropIndex(['completed_date']);
            $table->dropIndex(['due_date']);
            $table->dropIndex(['status']);
        });

        Schema::table('commit_logs', function (Blueprint $table) {
            $table->dropIndex(['commit_date']);
        });

        Schema::table('attendences', function (Blueprint $table) {
            $table->dropIndex(['date']);
        });
    }
};

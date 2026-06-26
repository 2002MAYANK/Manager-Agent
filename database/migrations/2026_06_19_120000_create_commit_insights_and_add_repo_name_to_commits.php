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
        Schema::table('commit_logs', function (Blueprint $table) {
            $table->string('repository_name')->nullable()->after('employee_id');
        });

        Schema::create('commit_insights', function (Blueprint $table) {
            $table->id();
            $table->foreignId('commit_log_id')
                ->constrained('commit_logs')
                ->cascadeOnDelete();
            $table->string('feature_category');
            $table->string('business_impact');
            $table->string('technical_complexity');
            $table->string('risk_level');
            $table->text('summary');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('commit_insights');

        Schema::table('commit_logs', function (Blueprint $table) {
            $table->dropColumn('repository_name');
        });
    }
};

<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('meetings', function (Blueprint $table) {
            $table->dropColumn('meeting_audio');
            $table->integer('total_participants')->default(0);
            $table->integer('total_transcript_entries')->default(0);
            $table->string('most_active_speaker')->nullable();
            $table->string('least_active_speaker')->nullable();
            $table->string('meeting_duration')->nullable();
        });
    }

    public function down(): void
    {
        Schema::table('meetings', function (Blueprint $table) {
            $table->string('meeting_audio')->nullable();
            $table->dropColumn([
                'total_participants',
                'total_transcript_entries',
                'most_active_speaker',
                'least_active_speaker',
                'meeting_duration'
            ]);
        });
    }
};

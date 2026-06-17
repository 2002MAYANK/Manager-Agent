<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('meeting_transcripts', function (Blueprint $table) {
            $table->string('timestamp')->nullable()->after('spoken_text');
        });
    }

    public function down(): void
    {
        Schema::table('meeting_transcripts', function (Blueprint $table) {
            $table->dropColumn('timestamp');
        });
    }
};

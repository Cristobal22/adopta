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
        Schema::table('adoption_diaries', function (Blueprint $table) {
            $table->boolean('is_public')->default(false)->after('ai_abuse_flag');
            $table->enum('moderation_status', ['pending', 'approved', 'rejected'])->default('pending')->after('is_public');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('adoption_diaries', function (Blueprint $table) {
            $table->dropColumn(['is_public', 'moderation_status']);
        });
    }
};

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
            $table->boolean('photo_consent')->default(false)->after('moderation_status');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('adoption_diaries', function (Blueprint $table) {
            $table->dropColumn('photo_consent');
        });
    }
};

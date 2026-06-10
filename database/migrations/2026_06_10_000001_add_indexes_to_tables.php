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
        Schema::table('audit_logs', function (Blueprint $table) {
            $table->index(['action', 'created_at']);
            $table->index('model_type');
            $table->index('model_id');
        });

        Schema::table('adoptions', function (Blueprint $table) {
            $table->index('status');
        });

        Schema::table('sponsorships', function (Blueprint $table) {
            $table->index('status');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('audit_logs', function (Blueprint $table) {
            $table->dropIndex(['action', 'created_at']);
            $table->dropIndex(['model_type']);
            $table->dropIndex(['model_id']);
        });

        Schema::table('adoptions', function (Blueprint $table) {
            $table->dropIndex(['status']);
        });

        Schema::table('sponsorships', function (Blueprint $table) {
            $table->dropIndex(['status']);
        });
    }
};

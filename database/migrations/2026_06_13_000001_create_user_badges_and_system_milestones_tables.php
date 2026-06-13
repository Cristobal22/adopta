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
        Schema::create('user_badges', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->string('badge_key'); // e.g., 'pionero', 'tutor_comprometido', 'heroe_camino'
            $table->string('badge_name');
            $table->string('badge_icon');
            $table->timestamps();
        });

        Schema::create('system_milestones', function (Blueprint $table) {
            $table->id();
            $table->string('milestone_key')->unique(); // e.g., 'comunidad_fundadora', 'primeros_pasos', 'logistica_activa'
            $table->string('name');
            $table->integer('threshold');
            $table->integer('current_value')->default(0);
            $table->boolean('is_reached')->default(false);
            $table->timestamp('reached_at')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('system_milestones');
        Schema::dropIfExists('user_badges');
    }
};

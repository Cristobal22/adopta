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
        Schema::create('adoptions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('pet_id')->constrained('pets')->onDelete('cascade');
            $table->foreignId('adopter_id')->constrained('users')->onDelete('cascade');
            $table->foreignId('rescuer_id')->constrained('users')->onDelete('cascade');
            $table->enum('status', ['solicitado', 'en_proceso', 'aprobado', 'rechazado', 'finalizado'])->default('solicitado');
            $table->string('contract_path')->nullable();
            $table->enum('signature_type', ['digital_canvas', 'uploaded_pdf'])->nullable();
            $table->decimal('compatibility_score', 5, 2)->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('adoptions');
    }
};

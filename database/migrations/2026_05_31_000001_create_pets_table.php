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
        Schema::create('pets', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->enum('species', ['perro', 'gato', 'otro'])->default('perro');
            $table->string('breed')->nullable();
            $table->string('age_text')->nullable();
            $table->enum('status', ['rescatado', 'en_transito', 'adoptado', 'en_adopcion'])->default('rescatado');
            $table->enum('gender', ['macho', 'hembra'])->default('macho');
            $table->text('description')->nullable();
            $table->decimal('latitude', 10, 8)->nullable();
            $table->decimal('longitude', 11, 8)->nullable();
            $table->json('clinical_history')->nullable(); // Vacunas, castración, enfermedades, etc.
            $table->string('microchip_code')->nullable()->unique()->index();
            $table->json('characteristics')->nullable(); // Nivel energía, compatibilidad niños/perros, etc.
            $table->string('photo_path')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('pets');
    }
};

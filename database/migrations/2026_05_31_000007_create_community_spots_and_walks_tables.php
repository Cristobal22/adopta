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
        Schema::create('community_spots', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->enum('type', ['parque', 'restaurant', 'hotel', 'veterinaria'])->default('parque');
            $table->decimal('latitude', 10, 8);
            $table->decimal('longitude', 11, 8);
            $table->text('description')->nullable();
            $table->string('address')->nullable();
            $table->timestamps();
        });

        Schema::create('pack_walks', function (Blueprint $table) {
            $table->id();
            $table->string('title');
            $table->string('neighborhood');
            $table->dateTime('walk_date');
            $table->text('description')->nullable();
            $table->decimal('latitude', 10, 8);
            $table->decimal('longitude', 11, 8);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('community_spots');
        Schema::dropIfExists('pack_walks');
    }
};

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
        Schema::create('adoption_diaries', function (Blueprint $table) {
            $table->id();
            $table->foreignId('adoption_id')->constrained('adoptions')->onDelete('cascade');
            $table->date('report_date');
            $table->string('emoji_mood');
            $table->text('comment');
            $table->string('photo_path')->nullable();
            $table->decimal('ai_sentiment_score', 3, 2)->nullable(); // De -1.00 a +1.00
            $table->boolean('ai_abuse_flag')->default(false);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('adoption_diaries');
    }
};

<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable([
    'adoption_id',
    'report_date',
    'emoji_mood',
    'comment',
    'photo_path',
    'ai_sentiment_score',
    'ai_abuse_flag',
    'is_public',
    'moderation_status',
    'photo_consent',
])]
class AdoptionDiary extends Model
{
    use HasFactory;

    protected function casts(): array
    {
        return [
            'report_date' => 'date',
            'ai_sentiment_score' => 'decimal:2',
            'ai_abuse_flag' => 'boolean',
            'is_public' => 'boolean',
            'photo_consent' => 'boolean',
        ];
    }

    /**
     * Adopción a la que pertenece este diario de seguimiento.
     */
    public function adoption(): BelongsTo
    {
        return $this->belongsTo(Adoption::class);
    }
}

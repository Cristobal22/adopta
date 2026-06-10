<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

#[Fillable([
    'pet_id',
    'adopter_id',
    'rescuer_id',
    'status',
    'contract_path',
    'signature_type',
    'compatibility_score',
])]
class Adoption extends Model
{
    use HasFactory;

    protected function casts(): array
    {
        return [
            'compatibility_score' => 'decimal:2',
        ];
    }

    /**
     * Mascota que se está adoptando.
     */
    public function pet(): BelongsTo
    {
        return $this->belongsTo(Pet::class);
    }

    /**
     * Adoptante definitivo.
     */
    public function adopter(): BelongsTo
    {
        return $this->belongsTo(User::class, 'adopter_id');
    }

    /**
     * Rescatista o fundación que gestiona la adopción.
     */
    public function rescuer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'rescuer_id');
    }

    /**
     * Diarios de seguimiento de la adopción.
     */
    public function diaries(): HasMany
    {
        return $this->hasMany(AdoptionDiary::class);
    }
}

<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

#[Fillable([
    'name',
    'species',
    'breed',
    'age_text',
    'status',
    'gender',
    'description',
    'latitude',
    'longitude',
    'clinical_history',
    'microchip_code',
    'characteristics',
    'photo_path',
])]
class Pet extends Model
{
    use HasFactory;

    protected function casts(): array
    {
        return [
            'latitude' => 'decimal:8',
            'longitude' => 'decimal:8',
            'clinical_history' => 'array',
            'characteristics' => 'array',
        ];
    }

    /**
     * Adopciones asociadas a esta mascota.
     */
    public function adoptions(): HasMany
    {
        return $this->hasMany(Adoption::class);
    }

    /**
     * Apadrinamientos asociados a esta mascota.
     */
    public function sponsorships(): HasMany
    {
        return $this->hasMany(Sponsorship::class);
    }
}

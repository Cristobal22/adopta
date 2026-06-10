<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable([
    'pet_id',
    'driver_id',
    'requester_id',
    'origin',
    'destination',
    'status',
])]
class UberTrip extends Model
{
    use HasFactory;

    /**
     * Mascota que se traslada (puede ser nulo si es traslado de insumos/kits).
     */
    public function pet(): BelongsTo
    {
        return $this->belongsTo(Pet::class);
    }

    /**
     * Conductor voluntario del traslado.
     */
    public function driver(): BelongsTo
    {
        return $this->belongsTo(User::class, 'driver_id');
    }

    /**
     * Usuario que solicitó el viaje (rescatista, adoptante).
     */
    public function requester(): BelongsTo
    {
        return $this->belongsTo(User::class, 'requester_id');
    }
}

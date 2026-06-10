<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Operative extends Model
{
    use HasFactory;

    protected $fillable = [
        'municipality_id',
        'title',
        'description',
        'date',
        'commune',
        'capacity',
        'status',
    ];

    protected $casts = [
        'date' => 'datetime',
        'capacity' => 'integer',
    ];

    /**
     * Obtener la municipalidad que organizó el operativo.
     */
    public function municipality(): BelongsTo
    {
        return $this->belongsTo(User::class, 'municipality_id');
    }
}

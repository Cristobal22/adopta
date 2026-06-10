<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

#[Fillable([
    'title',
    'neighborhood',
    'walk_date',
    'description',
    'latitude',
    'longitude',
])]
class PackWalk extends Model
{
    use HasFactory;

    protected function casts(): array
    {
        return [
            'walk_date' => 'datetime',
            'latitude' => 'decimal:8',
            'longitude' => 'decimal:8',
        ];
    }
}

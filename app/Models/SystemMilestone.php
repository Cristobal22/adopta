<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SystemMilestone extends Model
{
    protected $fillable = [
        'milestone_key',
        'name',
        'threshold',
        'current_value',
        'is_reached',
        'reached_at',
    ];

    protected $casts = [
        'is_reached' => 'boolean',
        'reached_at' => 'datetime',
        'threshold' => 'integer',
        'current_value' => 'integer',
    ];
}

<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Hidden;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

#[Fillable([
    'name', 
    'email', 
    'password', 
    'role', 
    'phone', 
    'address', 
    'city', 
    'status', 
    'verification_data', 
    'points'
])]
#[Hidden(['password', 'remember_token'])]
class User extends Authenticatable
{
    use HasFactory, Notifiable;

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'verification_data' => 'array',
            'points' => 'integer',
        ];
    }

    /**
     * Adopciones en las que el usuario es el adoptante.
     */
    public function adoptionsAsAdopter(): HasMany
    {
        return $table = $this->hasMany(Adoption::class, 'adopter_id');
    }

    /**
     * Adopciones en las que el usuario es el rescatista / fundación.
     */
    public function adoptionsAsRescuer(): HasMany
    {
        return $this->hasMany(Adoption::class, 'rescuer_id');
    }

    /**
     * Viajes solidarios donde el usuario es conductor voluntario.
     */
    public function tripsAsDriver(): HasMany
    {
        return $this->hasMany(UberTrip::class, 'driver_id');
    }

    /**
     * Viajes solidarios solicitados por el usuario.
     */
    public function tripsAsRequester(): HasMany
    {
        return $this->hasMany(UberTrip::class, 'requester_id');
    }

    /**
     * Logs de auditoría generados por el usuario.
     */
    public function auditLogs(): HasMany
    {
        return $this->hasMany(AuditLog::class);
    }

    /**
     * Apadrinamientos financiados por este usuario.
     */
    public function sponsorships(): HasMany
    {
        return $this->hasMany(Sponsorship::class);
    }

    /**
     * Operativos veterinarios organizados por la municipalidad.
     */
    public function operatives(): HasMany
    {
        return $this->hasMany(Operative::class, 'municipality_id');
    }

    /**
     * Medallas / Logros obtenidos por el usuario.
     */
    public function badges(): HasMany
    {
        return $this->hasMany(UserBadge::class);
    }
}

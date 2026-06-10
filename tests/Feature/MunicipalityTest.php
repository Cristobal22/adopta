<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\Operative;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class MunicipalityTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Test que valida que los visitantes invitados y usuarios no autorizados no pueden ingresar al dashboard municipal.
     */
    public function test_unauthorized_users_are_blocked_from_municipality_dashboard(): void
    {
        // 1. Visitante invitado es redirigido a login
        $response = $this->get('/municipality/dashboard');
        $response->assertRedirect('/login');

        // 2. Adoptante recibe un 403 de acceso prohibido
        $adopter = User::factory()->create([
            'role' => 'adoptante',
            'city' => 'Santiago, Región Metropolitana de Santiago'
        ]);

        $response = $this->actingAs($adopter)->get('/municipality/dashboard');
        $response->assertStatus(403);
    }

    /**
     * Test que valida que el usuario con el rol municipalidad puede ingresar exitosamente a su dashboard.
     */
    public function test_municipality_user_can_access_their_dashboard(): void
    {
        $muni = User::factory()->create([
            'role' => 'municipalidad',
            'city' => 'Valparaíso, Región de Valparaíso'
        ]);

        $response = $this->actingAs($muni)->get('/municipality/dashboard');
        
        $response->assertStatus(200);
        $response->assertInertia(fn ($page) => $page
            ->component('Municipality/Dashboard')
            ->has('commune')
            ->where('commune', 'Valparaíso, Región de Valparaíso')
        );
    }

    /**
     * Test que valida la creación de operativos comunales.
     */
    public function test_municipality_can_create_veterinary_operatives(): void
    {
        $muni = User::factory()->create([
            'role' => 'municipalidad',
            'city' => 'Valparaíso, Región de Valparaíso'
        ]);

        $operativeData = [
            'title' => 'Operativo de Chipeo Masivo',
            'description' => 'Campaña gratuita de chipeo obligatorio.',
            'date' => now()->addDays(5)->toDateTimeString(),
            'capacity' => 100,
        ];

        $response = $this->actingAs($muni)->post('/municipality/operatives', $operativeData);

        $response->assertStatus(302);
        
        $this->assertDatabaseHas('operatives', [
            'title' => 'Operativo de Chipeo Masivo',
            'commune' => 'Valparaíso, Región de Valparaíso',
            'capacity' => 100,
        ]);
    }

    /**
     * Test que valida que las alertas críticas del diario son visibles para auditoría municipal.
     */
    public function test_municipality_can_access_flagged_abuse_diaries(): void
    {
        $muni = User::factory()->create([
            'role' => 'municipalidad',
            'city' => 'Valparaíso, Región de Valparaíso'
        ]);

        $response = $this->actingAs($muni)->get('/municipality/audit-abuse');

        $response->assertStatus(200);
        $response->assertInertia(fn ($page) => $page
            ->component('Municipality/AuditAbuse')
            ->has('alerts')
        );
    }
}

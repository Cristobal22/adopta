<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use App\Models\User;
use App\Models\Pet;
use App\Models\Adoption;
use App\Models\Donation;
use Inertia\Testing\AssertableInertia as Assert;

class FinancialAndVaultTest extends TestCase
{
    use RefreshDatabase;

    private User $adopter;
    private User $foundation;
    private Pet $pet;

    protected function setUp(): void
    {
        parent::setUp();

        $this->adopter = User::create([
            'name' => 'Adoptante Donante',
            'email' => 'donante@test.com',
            'password' => bcrypt('password'),
            'role' => 'adoptante',
            'points' => 10,
        ]);

        $this->foundation = User::create([
            'name' => 'Fundación Amiga',
            'email' => 'fundacion@test.com',
            'password' => bcrypt('password'),
            'role' => 'fundacion',
        ]);

        $this->pet = Pet::create([
            'name' => 'Pili',
            'species' => 'perro',
            'breed' => 'Mestiza',
            'age_text' => '1 año',
            'status' => 'adoptado',
            'gender' => 'hembra',
            'latitude' => -33.4489,
            'longitude' => -70.6693,
            'clinical_history' => [], // Sin esterilización
            'characteristics' => [
                'kids' => true,
                'dogs' => true,
                'cats' => true,
                'energy' => 3,
                'space' => 'pequeño',
                'alone' => true,
            ],
        ]);
    }

    /**
     * Prueba que los adoptantes no puedan acceder a la Bóveda de Contratos.
     */
    public function test_adopters_cannot_access_contracts_vault(): void
    {
        $response = $this->actingAs($this->adopter)->get(route('backoffice.vault'));
        $response->assertStatus(403);
    }

    /**
     * Prueba que las fundaciones sí puedan acceder a la Bóveda de Contratos.
     */
    public function test_foundations_can_access_vault_and_receive_clause_alerts(): void
    {
        // Crear una adopción finalizada
        $adoption = Adoption::create([
            'pet_id' => $this->pet->id,
            'adopter_id' => $this->adopter->id,
            'rescuer_id' => $this->foundation->id,
            'status' => 'finalizado',
            'compatibility_score' => 90,
        ]);

        // Forzar created_at en el pasado (ya que no es fillable)
        $adoption->created_at = now()->subMonths(7);
        $adoption->save();

        $response = $this->actingAs($this->foundation)->get(route('backoffice.vault'));

        $response->assertStatus(200);
        
        // Verificar usando la aserción de Inertia
        $response->assertInertia(fn (Assert $page) => $page
            ->component('Backoffice/Vault')
            ->has('clauseAlerts', 1)
            ->where('clauseAlerts.0.pet_name', 'Pili')
            ->where('clauseAlerts.0.adopter_name', 'Adoptante Donante')
        );
    }

    /**
     * Prueba la trazabilidad de donaciones públicas y el historial privado.
     */
    public function test_donations_traceability_screen(): void
    {
        Donation::create([
            'user_id' => $this->adopter->id,
            'amount' => 15000,
            'payment_id' => 'mp_123',
            'kit_id' => 'kit_bienvenida',
            'status' => 'aprobado',
        ]);

        $response = $this->actingAs($this->adopter)->get(route('donations.traceability'));

        $response->assertStatus(200);
        
        $response->assertInertia(fn (Assert $page) => $page
            ->component('Donations/Traceability')
            ->has('donations', 1)
            ->where('donations.0.amount', '15000.00')
            ->has('distribution')
        );
    }

    /**
     * Prueba iniciar una donación y recibir Huellas de recompensa.
     */
    public function test_can_donate_and_earn_points(): void
    {
        $response = $this->actingAs($this->adopter)->post(route('donations.checkout'), [
            'amount' => 10000,
            'kit_id' => null,
        ]);

        $response->assertStatus(302);

        $this->assertDatabaseHas('donations', [
            'user_id' => $this->adopter->id,
            'amount' => 10000,
            'status' => 'aprobado',
        ]);

        $this->assertEquals(110, $this->adopter->fresh()->points);
    }
}

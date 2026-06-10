<?php

namespace Tests\Unit;

use Tests\TestCase;
use App\Models\User;
use App\Models\Pet;
use App\Services\AdoptionService;

class MatchTest extends TestCase
{
    private AdoptionService $adoptionService;

    protected function setUp(): void
    {
        parent::setUp();
        $this->adoptionService = new AdoptionService();
    }

    /**
     * Prueba que un emparejamiento perfecto entregue 100% de compatibilidad.
     */
    public function test_perfect_match_returns_hundred_percent(): void
    {
        $user = new User();
        $user->verification_data = [
            'has_kids' => false,
            'has_dogs' => false,
            'has_cats' => false,
            'energy_preference' => 3,
            'house_type' => 'casa_patio_grande',
            'hours_alone' => 2,
        ];

        $pet = new Pet();
        $pet->characteristics = [
            'kids' => true,
            'dogs' => true,
            'cats' => true,
            'energy' => 3,
            'space' => 'pequeño',
            'alone' => true,
        ];

        $result = $this->adoptionService->calculateCompatibility($user, $pet);

        $this->assertTrue($result['is_compatible']);
        $this->assertEquals(100.00, $result['score']);
        $this->assertEmpty($result['reasons']);
    }

    /**
     * Prueba que el desajuste para niños genere advertencia sin bloquear compatibilidad.
     */
    public function test_kids_safety_lock_warns_compatibility(): void
    {
        $user = new User();
        $user->verification_data = [
            'has_kids' => true,
            'has_dogs' => false,
            'has_cats' => false,
            'energy_preference' => 3,
            'house_type' => 'casa_patio_grande',
            'hours_alone' => 2,
        ];

        $pet = new Pet();
        // Mascota no apta para niños
        $pet->characteristics = [
            'kids' => false,
            'dogs' => true,
            'cats' => true,
            'energy' => 3,
            'space' => 'pequeño',
            'alone' => true,
        ];

        $result = $this->adoptionService->calculateCompatibility($user, $pet);

        $this->assertTrue($result['is_compatible']);
        $this->assertContains('Esta mascota no es recomendada para hogares con niños por motivos de seguridad.', $result['reasons']);
    }

    /**
     * Prueba que un puntaje menor al 65% genere advertencia de compatibilidad sin bloquearla.
     */
    public function test_low_score_warns_compatibility(): void
    {
        $user = new User();
        $user->verification_data = [
            'has_kids' => false,
            'has_dogs' => true,
            'has_cats' => true,
            'energy_preference' => 1,
            'house_type' => 'departamento',
            'hours_alone' => 8,
        ];

        $pet = new Pet();
        // Mascota incompatible con casi todo
        $pet->characteristics = [
            'kids' => true,
            'dogs' => false,
            'cats' => false,
            'energy' => 5,
            'space' => 'grande',
            'alone' => false,
        ];

        $result = $this->adoptionService->calculateCompatibility($user, $pet);

        $this->assertTrue($result['is_compatible']);
        $this->assertEquals(0.00, $result['score']);
        $this->assertContains('El puntaje de compatibilidad total (0%) es inferior al mínimo recomendado del 65.00%.', $result['reasons']);
    }
}

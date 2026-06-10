<?php

namespace Tests\Unit;

use Tests\TestCase;
use App\Models\User;
use App\Models\Pet;
use App\Models\Blacklist;
use App\Services\AdoptionService;
use Illuminate\Foundation\Testing\RefreshDatabase;

class WelfareMatchTest extends TestCase
{
    use RefreshDatabase;

    private AdoptionService $adoptionService;
    private User $reporter;

    protected function setUp(): void
    {
        parent::setUp();
        $this->adoptionService = new AdoptionService();

        // Crear una fundación para asociar los reportes de blacklist
        $this->reporter = User::create([
            'name' => 'Fundación Huellas',
            'email' => 'fundacion@huellas.com',
            'password' => bcrypt('password'),
            'role' => 'fundacion',
        ]);
    }

    /**
     * Prueba que el bloqueo de lista negra funcione por email.
     */
    public function test_blacklist_blocks_by_email(): void
    {
        // Registrar email en la lista negra
        Blacklist::create([
            'email' => 'bad_adopter@test.com',
            'identification_code' => null,
            'reason' => 'Historial de abandono reiterado en plazas públicas.',
            'reported_by' => $this->reporter->id,
        ]);

        $user = new User([
            'email' => 'bad_adopter@test.com',
            'verification_data' => [
                'has_kids' => false,
                'has_dogs' => false,
                'has_cats' => false,
                'energy_preference' => 3,
                'house_type' => 'casa_patio_grande',
                'hours_alone' => 2,
            ],
        ]);

        $pet = new Pet([
            'characteristics' => [
                'kids' => true,
                'dogs' => true,
                'cats' => true,
                'energy' => 3,
                'space' => 'pequeño',
                'alone' => true,
            ],
        ]);

        $result = $this->adoptionService->calculateCompatibility($user, $pet);

        $this->assertFalse($result['is_compatible']);
        $this->assertEquals(0.00, $result['score']);
        $this->assertContains('El adoptante se encuentra registrado en la lista negra: Historial de abandono reiterado en plazas públicas.', $result['reasons']);
    }

    /**
     * Prueba que el bloqueo de lista negra funcione por código de identificación (RUT/DNI).
     */
    public function test_blacklist_blocks_by_identification_code(): void
    {
        // Registrar RUT/DNI en la lista negra
        Blacklist::create([
            'email' => null,
            'identification_code' => '12345678-9',
            'reason' => 'Antecedentes legales de maltrato animal.',
            'reported_by' => $this->reporter->id,
        ]);

        $user = new User([
            'email' => 'innocent_looking_email@test.com',
            'verification_data' => [
                'identification_code' => '12345678-9',
                'has_kids' => false,
                'has_dogs' => false,
                'has_cats' => false,
                'energy_preference' => 3,
                'house_type' => 'casa_patio_grande',
                'hours_alone' => 2,
            ],
        ]);

        $pet = new Pet([
            'characteristics' => [
                'kids' => true,
                'dogs' => true,
                'cats' => true,
                'energy' => 3,
                'space' => 'pequeño',
                'alone' => true,
            ],
        ]);

        $result = $this->adoptionService->calculateCompatibility($user, $pet);

        $this->assertFalse($result['is_compatible']);
        $this->assertContains('El adoptante se encuentra registrado en la lista negra: Antecedentes legales de maltrato animal.', $result['reasons']);
    }

    /**
     * Prueba el desajuste etológico por brecha extrema de energía.
     */
    public function test_extreme_energy_mismatch_warns_compatibility(): void
    {
        $user = new User([
            'email' => 'normal_adopter@test.com',
            'verification_data' => [
                'has_kids' => false,
                'has_dogs' => false,
                'has_cats' => false,
                'energy_preference' => 1, // Muy sedentario
                'house_type' => 'casa_patio_grande',
                'hours_alone' => 2,
            ],
        ]);

        $pet = new Pet([
            // Mascota extremadamente activa (5/5)
            'characteristics' => [
                'kids' => true,
                'dogs' => true,
                'cats' => true,
                'energy' => 5,
                'space' => 'grande',
                'alone' => true,
            ],
        ]);

        $result = $this->adoptionService->calculateCompatibility($user, $pet);

        $this->assertTrue($result['is_compatible']);
        $this->assertContains('La diferencia de nivel de energía entre la mascota (5/5) y tu preferencia (1/5) es extrema, lo cual pone en riesgo el bienestar animal.', $result['reasons']);
    }

    /**
     * Prueba que el umbral mínimo del 65% advierta pero permita postulaciones.
     */
    public function test_threshold_warns_scores_below_65_percent(): void
    {
        $user = new User([
            'email' => 'normal_adopter@test.com',
            'verification_data' => [
                'has_kids' => false,
                'has_dogs' => false, 
                'has_cats' => false,
                'energy_preference' => 3, // Diferencia de 2 puntos con 5 (Penaliza -20%, no es brecha extrema)
                'house_type' => 'departamento', // Diferencia de espacio para perro grande (Penaliza -20%)
                'hours_alone' => 8, // Sufre ansiedad sola > 6 horas (Penaliza -15%)
            ],
        ]);

        $pet = new Pet([
            'characteristics' => [
                'kids' => true,
                'dogs' => true,
                'cats' => true,
                'energy' => 5,
                'space' => 'grande',
                'alone' => false, // No soporta estar sola
            ],
        ]);

        // Puntos: 100 - 20 (energy) - 20 (space) - 15 (alone) = 45%
        $result = $this->adoptionService->calculateCompatibility($user, $pet);

        $this->assertTrue($result['is_compatible']);
        $this->assertEquals(45.00, $result['score']);
        $this->assertContains('El puntaje de compatibilidad total (45%) es inferior al mínimo recomendado del 65.00%.', $result['reasons']);
    }

    /**
     * Prueba que la incompatibilidad con perros advierta pero permita la postulación.
     */
    public function test_dog_incompatibility_warns_adoption(): void
    {
        $user = new User([
            'email' => 'has_dogs@test.com',
            'verification_data' => [
                'has_kids' => false,
                'has_dogs' => true, 
                'has_cats' => false,
                'energy_preference' => 3,
                'house_type' => 'casa_patio_grande',
                'hours_alone' => 2,
            ],
        ]);

        $pet = new Pet([
            'characteristics' => [
                'kids' => true,
                'dogs' => false, // Incompatible con otros perros
                'cats' => true,
                'energy' => 3,
                'space' => 'pequeño',
                'alone' => true,
            ],
        ]);

        $result = $this->adoptionService->calculateCompatibility($user, $pet);

        $this->assertTrue($result['is_compatible']);
        $this->assertContains('Esta mascota no convive bien con otros perros y tienes perros registrados en tu hogar.', $result['reasons']);
    }

    /**
     * Prueba que la incompatibilidad con gatos advierta pero permita la postulación.
     */
    public function test_cat_incompatibility_warns_adoption(): void
    {
        $user = new User([
            'email' => 'has_cats@test.com',
            'verification_data' => [
                'has_kids' => false,
                'has_dogs' => false, 
                'has_cats' => true,
                'energy_preference' => 3,
                'house_type' => 'casa_patio_grande',
                'hours_alone' => 2,
            ],
        ]);

        $pet = new Pet([
            'characteristics' => [
                'kids' => true,
                'dogs' => true,
                'cats' => false, // Incompatible con gatos
                'energy' => 3,
                'space' => 'pequeño',
                'alone' => true,
            ],
        ]);

        $result = $this->adoptionService->calculateCompatibility($user, $pet);

        $this->assertTrue($result['is_compatible']);
        $this->assertContains('Esta mascota no convive bien con gatos y tienes gatos en tu hogar.', $result['reasons']);
    }
}

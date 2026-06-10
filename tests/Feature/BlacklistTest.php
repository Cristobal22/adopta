<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use App\Models\User;
use App\Models\Pet;
use App\Models\Blacklist;

class BlacklistTest extends TestCase
{
    use RefreshDatabase;

    private User $adopter;
    private User $foundation;
    private Pet $pet;

    protected function setUp(): void
    {
        parent::setUp();

        $this->foundation = User::create([
            'name' => 'Fundación Amiga',
            'email' => 'fundacion@amiga.com',
            'password' => bcrypt('password'),
            'role' => 'fundacion',
        ]);

        $this->adopter = User::create([
            'name' => 'Adoptante Test',
            'email' => 'blacklisted@badtest.com',
            'password' => bcrypt('password'),
            'role' => 'adoptante',
            'verification_data' => [
                'has_kids' => false,
                'has_dogs' => false,
                'has_cats' => false,
                'energy_preference' => 3,
                'house_type' => 'casa_patio_grande',
                'hours_alone' => 2,
            ],
        ]);

        $this->pet = Pet::create([
            'name' => 'Kira',
            'species' => 'perro',
            'breed' => 'Ovejero',
            'age_text' => '2 años',
            'status' => 'en_adopcion',
            'gender' => 'hembra',
            'latitude' => -33.4489,
            'longitude' => -70.6693,
            'microchip_code' => '999999999999999',
            'clinical_history' => [],
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
     * Prueba que el controlador de adopciones rechace postulaciones de usuarios en la lista negra.
     */
    public function test_controller_rejects_postulation_if_user_is_blacklisted(): void
    {
        // Registrar en lista negra
        Blacklist::create([
            'email' => 'blacklisted@badtest.com',
            'identification_code' => null,
            'reason' => 'Antecedentes graves de abandono.',
            'reported_by' => $this->foundation->id,
        ]);

        // Simular postulación
        $response = $this->actingAs($this->adopter)->post(route('adoptions.store'), [
            'pet_id' => $this->pet->id,
        ]);

        // Verificar que redirige atrás con errores
        $response->assertStatus(302);
        $response->assertSessionHasErrors('compatibility');

        // Confirmar que no se creó la adopción en la base de datos
        $this->assertDatabaseMissing('adoptions', [
            'pet_id' => $this->pet->id,
            'adopter_id' => $this->adopter->id,
        ]);
    }
}

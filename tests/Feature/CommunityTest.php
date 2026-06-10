<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use App\Models\User;
use App\Models\CommunitySpot;
use App\Models\PackWalk;

class CommunityTest extends TestCase
{
    use RefreshDatabase;

    private User $user;

    protected function setUp(): void
    {
        parent::setUp();

        $this->user = User::create([
            'name' => 'Comunidad User',
            'email' => 'comunidad@test.com',
            'password' => bcrypt('password'),
            'role' => 'adoptante',
            'points' => 10,
        ]);
    }

    /**
     * Prueba el render del Radar Pet-Friendly.
     */
    public function test_can_view_radar_hub(): void
    {
        CommunitySpot::create([
            'name' => 'Parque Canino Las Rosas',
            'type' => 'parque',
            'latitude' => -33.4489,
            'longitude' => -70.6693,
        ]);

        PackWalk::create([
            'title' => 'Caminata de Bulldogs',
            'neighborhood' => 'Providencia',
            'walk_date' => now()->addDays(2),
            'latitude' => -33.4400,
            'longitude' => -70.6600,
        ]);

        $response = $this->actingAs($this->user)->get(route('community.radar'));

        $response->assertStatus(200);
        $response->assertSee('Parque Canino Las Rosas');
        $response->assertSee('Caminata de Bulldogs');
    }

    /**
     * Prueba el registro colaborativo de un spot pet-friendly.
     */
    public function test_can_register_new_community_spot(): void
    {
        $response = $this->actingAs($this->user)->post(route('community.spots.store'), [
            'name' => 'Café Guau',
            'type' => 'restaurant',
            'latitude' => -33.4500,
            'longitude' => -70.6700,
            'description' => 'Servicio de galletas caninas gratis.',
            'address' => 'Av. Italia 1020',
        ]);

        $response->assertStatus(302); // Redirect back
        $this->assertDatabaseHas('community_spots', [
            'name' => 'Café Guau',
            'type' => 'restaurant',
        ]);
    }

    /**
     * Prueba coordinar un paseo en manada y recibir puntos (Gamificación).
     */
    public function test_can_coordinate_pack_walk_and_gain_points(): void
    {
        $response = $this->actingAs($this->user)->post(route('community.walks.store'), [
            'title' => 'Paseo de Cachorros',
            'neighborhood' => 'Ñuñoa',
            'walk_date' => now()->addDays(3)->toDateTimeString(),
            'latitude' => -33.4600,
            'longitude' => -70.6500,
            'description' => 'Reunión en Plaza Ñuñoa.',
            'agreement' => true,
        ]);

        $response->assertStatus(302);

        $this->assertDatabaseHas('pack_walks', [
            'title' => 'Paseo de Cachorros',
            'neighborhood' => 'Ñuñoa',
        ]);

        // Verificar incremento de puntos (10 base + 15 de recompensa = 25 puntos)
        $this->assertEquals(25, $this->user->fresh()->points);
    }
}

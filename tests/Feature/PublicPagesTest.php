<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use App\Models\User;

class PublicPagesTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Test that the Welcome page is publicly accessible.
     */
    public function test_welcome_page_renders_successfully(): void
    {
        $response = $this->get('/');

        $response->assertStatus(200);
        
        // Check that Inertia renders the 'Welcome' component
        $inertiaData = $response->original->getData()['page'] ?? [];
        $this->assertEquals('Welcome', $inertiaData['component'] ?? null);
    }

    /**
     * Test that the Nosotros (About) page is publicly accessible.
     */
    public function test_nosotros_page_renders_successfully(): void
    {
        $response = $this->get('/nosotros');

        $response->assertStatus(200);

        // Check that Inertia renders the 'About' component
        $inertiaData = $response->original->getData()['page'] ?? [];
        $this->assertEquals('About', $inertiaData['component'] ?? null);
    }

    /**
     * Test that the Bazar hub is publicly accessible to guests and defaults to 0 points.
     */
    public function test_bazar_page_renders_successfully_for_guest_with_zero_points(): void
    {
        $response = $this->get('/bazar');

        $response->assertStatus(200);
        
        // Assert Inertia page structure props for points and component
        $inertiaData = $response->original->getData()['page'] ?? [];
        $this->assertEquals('Bazar/Hub', $inertiaData['component'] ?? null);
        $this->assertArrayHasKey('props', $inertiaData);
        $this->assertEquals(0, $inertiaData['props']['points'] ?? null);
    }

    /**
     * Test that the Bazar hub renders for authenticated users with their actual points.
     */
    public function test_bazar_page_renders_successfully_for_authenticated_user_with_points(): void
    {
        $user = User::create([
            'name' => 'Comprador Test',
            'email' => 'comprador@test.com',
            'password' => bcrypt('password123'),
            'role' => 'adoptante',
            'points' => 250,
            'phone' => '+56900000000',
        ]);

        $response = $this->actingAs($user)->get('/bazar');

        $response->assertStatus(200);
        
        // Assert points match the authenticated user's points
        $inertiaData = $response->original->getData()['page'] ?? [];
        $this->assertEquals('Bazar/Hub', $inertiaData['component'] ?? null);
        $this->assertEquals(250, $inertiaData['props']['points'] ?? null);
    }

    /**
     * Test that claiming a reward requires authentication.
     */
    public function test_claim_reward_requires_authentication(): void
    {
        $response = $this->post('/bazar/claim-reward', [
            'reward_id' => 'rew_1',
            'cost' => 150,
        ]);

        $response->assertRedirect(route('login'));
    }

    /**
     * Test that starting premium checkout requires authentication.
     */
    public function test_purchase_premium_requires_authentication(): void
    {
        $response = $this->post('/bazar/premium', [
            'plan_name' => 'Suscripción Visibilidad Bronce',
            'price' => 4990,
        ]);

        $response->assertRedirect(route('login'));
    }

    /**
     * Test that the pet catalog is publicly accessible.
     */
    public function test_pet_catalog_is_publicly_accessible(): void
    {
        $response = $this->get('/pets');

        $response->assertStatus(200);
        $inertiaData = $response->original->getData()['page'] ?? [];
        $this->assertEquals('Pets/Index', $inertiaData['component'] ?? null);
    }

    /**
     * Test that an authenticated user can claim a reward successfully and the cost is resolved server-side.
     */
    public function test_authenticated_user_can_claim_reward_successfully_with_server_cost(): void
    {
        $user = User::create([
            'name' => 'Comprador Test',
            'email' => 'comprador@test.com',
            'password' => bcrypt('password123'),
            'role' => 'adoptante',
            'points' => 400, // Tiene 400 puntos
            'phone' => '+56900000000',
        ]);

        // Intentar canjear rew_2 (Baño clínico gratis que cuesta 300 puntos en el servidor)
        // Intentamos sabotear enviando 'cost' => 1 en el body
        $response = $this->actingAs($user)->post('/bazar/claim-reward', [
            'reward_id' => 'rew_2',
            'cost' => 1, // Intentamos manipular
        ]);

        $response->assertStatus(302); // Redirect back
        $user->refresh();

        // Debe descontar 300 puntos, no 1. Quedando con 100 puntos.
        $this->assertEquals(100, $user->points);
    }

    /**
     * Test that claiming a reward fails if points are insufficient.
     */
    public function test_claim_reward_fails_if_points_are_insufficient(): void
    {
        $user = User::create([
            'name' => 'Comprador Test',
            'email' => 'comprador@test.com',
            'password' => bcrypt('password123'),
            'role' => 'adoptante',
            'points' => 50, // Tiene 50 puntos
            'phone' => '+56900000000',
        ]);

        // Intentar canjear rew_2 que cuesta 300 puntos
        $response = $this->actingAs($user)->from('/bazar')->post('/bazar/claim-reward', [
            'reward_id' => 'rew_2',
        ]);

        $response->assertRedirect('/bazar');
        $response->assertSessionHasErrors('points');
        $user->refresh();

        // Los puntos deben mantenerse en 50
        $this->assertEquals(50, $user->points);
    }
}

<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use App\Models\User;

class LogisticsTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Test that guest users are redirected to login when accessing /logistics.
     */
    public function test_guest_is_redirected_to_login(): void
    {
        $response = $this->get('/logistics');

        $response->assertRedirect(route('login'));
    }

    /**
     * Test that authenticated users can successfully access the logistics page.
     */
    public function test_authenticated_user_can_access_logistics(): void
    {
        $user = User::create([
            'name' => 'Transportista Test',
            'email' => 'chofer@test.com',
            'password' => bcrypt('password123'),
            'role' => 'adoptante',
            'phone' => '+56900000000',
        ]);

        $response = $this->actingAs($user)->get('/logistics');

        $response->assertStatus(200);
        
        $inertiaData = $response->original->getData()['page'] ?? [];
        $this->assertEquals('Logistics/Hub', $inertiaData['component'] ?? null);
    }
}

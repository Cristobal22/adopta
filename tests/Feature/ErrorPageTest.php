<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Inertia\Testing\AssertableInertia as Assert;
use Tests\TestCase;

class ErrorPageTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        // Forzar debug en falso para que el Exception Handler responda con la página Error.vue de Inertia
        config(['app.debug' => false]);
    }

    /**
     * Prueba que una ruta no existente (404) devuelva el componente Error.vue.
     */
    public function test_non_existent_route_returns_404_error_inertia_page()
    {
        $response = $this->get('/non-existent-route-path-testing');

        $response->assertStatus(404);
        $response->assertInertia(function (Assert $page) {
            $page->component('Error')
                 ->where('status', 404);
        });
    }

    /**
     * Prueba que el acceso denegado (403) devuelva el componente Error.vue para usuarios adoptantes.
     */
    public function test_forbidden_route_returns_403_error_inertia_page()
    {
        // Crear un usuario adoptante
        $user = User::create([
            'name' => 'Adoptante Test',
            'email' => 'adoptantetest@example.com',
            'password' => bcrypt('password'),
            'role' => 'adoptante',
            'status' => 'verificado',
        ]);

        // Intentar ingresar a una ruta protegida del backoffice
        $response = $this->actingAs($user)->get('/backoffice/pets/create');

        $response->assertStatus(403);
        $response->assertInertia(function (Assert $page) {
            $page->component('Error')
                 ->where('status', 403);
        });
    }
}

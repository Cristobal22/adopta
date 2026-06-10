<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;
use App\Models\User;
use App\Models\Pet;
use App\Models\Adoption;

class ContractVaultSecurityTest extends TestCase
{
    use RefreshDatabase;

    private User $adopter1;
    private User $adopter2;
    private User $admin;
    private Adoption $adoption;

    protected function setUp(): void
    {
        parent::setUp();

        Storage::fake('local');

        $this->adopter1 = User::create([
            'name' => 'Adoptante Uno',
            'email' => 'adoptante1@test.com',
            'password' => bcrypt('password'),
            'role' => 'adoptante',
        ]);

        $this->adopter2 = User::create([
            'name' => 'Adoptante Dos',
            'email' => 'adoptante2@test.com',
            'password' => bcrypt('password'),
            'role' => 'adoptante',
        ]);

        $this->admin = User::create([
            'name' => 'Admin User',
            'email' => 'admin@test.com',
            'password' => bcrypt('password'),
            'role' => 'admin',
        ]);

        $pet = Pet::create([
            'name' => 'Bobby',
            'species' => 'perro',
            'status' => 'adoptado',
            'latitude' => -33.4489,
            'longitude' => -70.6693,
        ]);

        $this->adoption = Adoption::create([
            'pet_id' => $pet->id,
            'adopter_id' => $this->adopter1->id,
            'rescuer_id' => $this->admin->id,
            'status' => 'finalizado',
            'contract_path' => 'contracts/test_contract.html',
            'signature_type' => 'digital_canvas',
            'compatibility_score' => 85.00,
        ]);

        // Guardar archivo simulado
        Storage::disk('local')->put('private/' . $this->adoption->contract_path, '<html>Contrato Firmado Test</html>');
    }

    /**
     * Test que un adoptante puede descargar su propio contrato.
     */
    public function test_adopter_can_download_own_contract(): void
    {
        $response = $this->actingAs($this->adopter1)
            ->get(route('adoptions.download-signed', $this->adoption->id));

        $response->assertStatus(200);
        $this->assertEquals('<html>Contrato Firmado Test</html>', $response->getContent());
    }

    /**
     * Test que un adoptante NO puede descargar el contrato de otra persona.
     */
    public function test_adopter_cannot_download_other_contract(): void
    {
        $response = $this->actingAs($this->adopter2)
            ->get(route('adoptions.download-signed', $this->adoption->id));

        $response->assertStatus(403);
    }

    /**
     * Test que un administrador puede descargar cualquier contrato de la bóveda.
     */
    public function test_admin_can_download_any_contract(): void
    {
        $response = $this->actingAs($this->admin)
            ->get(route('adoptions.download-signed', $this->adoption->id));

        $response->assertStatus(200);
        $this->assertEquals('<html>Contrato Firmado Test</html>', $response->getContent());
    }

    /**
     * Test que un invitado es redirigido al login.
     */
    public function test_guest_cannot_download_contract(): void
    {
        $response = $this->get(route('adoptions.download-signed', $this->adoption->id));

        $response->assertRedirect(route('login'));
    }
}

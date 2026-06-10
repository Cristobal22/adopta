<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;
use App\Models\User;
use App\Models\Pet;
use App\Models\Adoption;

class AdoptionFlowTest extends TestCase
{
    use RefreshDatabase;

    private User $adopter;
    private User $rescuer;
    private Pet $pet;

    protected function setUp(): void
    {
        parent::setUp();

        // Crear adoptante
        $this->adopter = User::create([
            'name' => 'Adoptante Test',
            'email' => 'adopter@test.com',
            'password' => bcrypt('password123'),
            'role' => 'adoptante',
            'phone' => '+56911111111',
            'address' => 'Calle Falsa 123',
            'city' => 'Santiago',
            'status' => 'verificado',
            'points' => 0,
            'verification_data' => [
                'has_kids' => false,
                'has_dogs' => false,
                'has_cats' => false,
                'energy_preference' => 3,
                'house_type' => 'casa_patio_grande',
                'hours_alone' => 2,
            ],
        ]);

        // Crear rescatista
        $this->rescuer = User::create([
            'name' => 'Rescatista Test',
            'email' => 'rescuer@test.com',
            'password' => bcrypt('password123'),
            'role' => 'rescatista',
            'phone' => '+56922222222',
            'address' => 'Calle Mascota 456',
            'city' => 'Santiago',
            'status' => 'verificado',
            'points' => 0,
        ]);

        // Crear mascota en adopción
        $this->pet = Pet::create([
            'name' => 'Copito',
            'species' => 'perro',
            'breed' => 'Maltés',
            'age_text' => '1 año',
            'status' => 'en_adopcion',
            'gender' => 'macho',
            'description' => 'Un perrito muy cariñoso y pequeño.',
            'latitude' => -33.4489,
            'longitude' => -70.6693,
            'microchip_code' => '123456789012345',
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
     * Prueba el flujo completo de postulación, aprobación y firma digital por canvas.
     */
    public function test_complete_adoption_canvas_flow(): void
    {
        Storage::fake('local');

        // 1. Postular a adopción
        $response = $this->actingAs($this->adopter)->post(route('adoptions.store'), [
            'pet_id' => $this->pet->id,
        ]);

        $response->assertRedirect(route('adoptions.index'));
        $this->assertDatabaseHas('adoptions', [
            'pet_id' => $this->pet->id,
            'adopter_id' => $this->adopter->id,
            'status' => 'solicitado',
        ]);

        $adoption = Adoption::where('pet_id', $this->pet->id)->firstOrFail();

        // 2. Aprobación por parte del Rescatista (cambiar a en_proceso para firma)
        $response = $this->actingAs($this->rescuer)->post(route('adoptions.status', $adoption->id), [
            'status' => 'en_proceso',
        ]);

        $response->assertStatus(302); // Redirect back
        $this->assertEquals('en_proceso', $adoption->fresh()->status);

        // 3. Adoptante firma usando canvas
        $simulatedBase64Image = 'data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAQAAAC1HAwCAAAAC0lEQVR42mNkYAAAAAYAAjCB0C8AAAAASUVORK5CYII=';
        $response = $this->actingAs($this->adopter)->post(route('adoptions.sign-canvas', $adoption->id), [
            'signature_data' => $simulatedBase64Image,
        ]);

        $response->assertRedirect(route('adoptions.show', $adoption->id));

        $freshAdoption = $adoption->fresh();
        $this->assertEquals('finalizado', $freshAdoption->status);
        $this->assertEquals('digital_canvas', $freshAdoption->signature_type);
        $this->assertNotNull($freshAdoption->contract_path);
        
        // Verificar que el estado del perro cambió a adoptado
        $this->assertEquals('adoptado', $this->pet->fresh()->status);
    }

    /**
     * Prueba la firma de adopción mediante carga de un archivo PDF.
     */
    public function test_adoption_pdf_upload_flow(): void
    {
        Storage::fake('local');

        // Crear una adopción en estado 'en_proceso' directamente
        $adoption = Adoption::create([
            'pet_id' => $this->pet->id,
            'adopter_id' => $this->adopter->id,
            'rescuer_id' => $this->rescuer->id,
            'status' => 'en_proceso',
            'compatibility_score' => 100,
        ]);

        // Simular archivo PDF
        $fakePdf = UploadedFile::fake()->create('contract.pdf', 1024, 'application/pdf');

        $response = $this->actingAs($this->adopter)->post(route('adoptions.sign-upload', $adoption->id), [
            'contract_file' => $fakePdf,
        ]);

        $response->assertRedirect(route('adoptions.show', $adoption->id));

        $freshAdoption = $adoption->fresh();
        $this->assertEquals('finalizado', $freshAdoption->status);
        $this->assertEquals('uploaded_pdf', $freshAdoption->signature_type);
        $this->assertNotNull($freshAdoption->contract_path);
        
        // Verificar que el archivo se guardó
        Storage::disk('local')->assertExists($freshAdoption->contract_path);
        
        // Verificar que el estado del perro cambió a adoptado
        $this->assertEquals('adoptado', $this->pet->fresh()->status);
    }
}

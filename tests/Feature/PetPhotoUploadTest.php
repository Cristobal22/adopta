<?php

namespace Tests\Feature;

use App\Models\Pet;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Tests\TestCase;

class PetPhotoUploadTest extends TestCase
{
    use RefreshDatabase;

    protected User $adminUser;

    protected function setUp(): void
    {
        parent::setUp();

        // Crear usuario administrador para interactuar con el backoffice de mascotas
        $this->adminUser = User::create([
            'name' => 'Admin Test',
            'email' => 'admin@test.com',
            'password' => bcrypt('password123'),
            'role' => 'admin',
            'status' => 'verificado',
        ]);
    }

    /**
     * Prueba que al registrar una mascota con foto, esta se optimice y se guarde en public/images/pets.
     */
    public function test_store_pet_with_photo_optimizes_and_saves_publicly()
    {
        $fakePhoto = UploadedFile::fake()->image('bobby.jpg', 800, 600);

        $response = $this->actingAs($this->adminUser)->post('/backoffice/pets', [
            'name' => 'Bobby',
            'species' => 'perro',
            'breed' => 'Poodle',
            'age_text' => '1 año',
            'status' => 'en_adopcion',
            'gender' => 'macho',
            'description' => 'Un perrito juguetón.',
            'latitude' => -33.45,
            'longitude' => -70.66,
            'microchip_code' => '999111222333444',
            'photo' => $fakePhoto,
        ]);

        $response->assertRedirect(route('pets.index'));

        // Obtener la mascota creada
        $pet = Pet::where('name', 'Bobby')->first();
        $this->assertNotNull($pet);
        $this->assertNotNull($pet->photo_path);

        // Verificar conversión WebP y ruta
        $this->assertTrue(str_starts_with($pet->photo_path, 'images/pets/'));
        $this->assertTrue(str_ends_with($pet->photo_path, '.webp'));

        // Verificar existencia física del archivo
        $absolutePath = public_path($pet->photo_path);
        $this->assertTrue(file_exists($absolutePath));

        // Limpiar archivo de prueba
        if (file_exists($absolutePath)) {
            @unlink($absolutePath);
        }
    }

    /**
     * Prueba que al actualizar la foto de una mascota, se guarde la nueva y se elimine la antigua.
     */
    public function test_update_pet_photo_deletes_old_photo_and_saves_new_one()
    {
        // 1. Crear una mascota inicial con una foto simulada
        $fakePhoto1 = UploadedFile::fake()->image('old_photo.jpg', 600, 600);
        
        $this->actingAs($this->adminUser)->post('/backoffice/pets', [
            'name' => 'Luna',
            'species' => 'gato',
            'breed' => 'Mestizo',
            'age_text' => '2 años',
            'status' => 'en_adopcion',
            'gender' => 'hembra',
            'photo' => $fakePhoto1,
        ]);

        $pet = Pet::where('name', 'Luna')->first();
        $oldPhotoPath = $pet->photo_path;
        $this->assertTrue(file_exists(public_path($oldPhotoPath)));

        // 2. Subir una nueva foto mediante spoofing de PUT (POST con _method = PUT)
        $fakePhoto2 = UploadedFile::fake()->image('new_photo.png', 700, 700);

        $response = $this->actingAs($this->adminUser)->post("/backoffice/pets/{$pet->id}", [
            '_method' => 'PUT',
            'name' => 'Luna',
            'species' => 'gato',
            'breed' => 'Mestizo',
            'age_text' => '2 años',
            'status' => 'en_adopcion',
            'gender' => 'hembra',
            'photo' => $fakePhoto2,
        ]);

        $response->assertRedirect(route('pets.index'));
        $pet->refresh();

        $newPhotoPath = $pet->photo_path;

        // Verificar que las rutas son diferentes y que la foto nueva existe
        $this->assertNotEquals($oldPhotoPath, $newPhotoPath);
        $this->assertTrue(file_exists(public_path($newPhotoPath)));

        // Verificar que la foto antigua fue eliminada físicamente del disco
        $this->assertFalse(file_exists(public_path($oldPhotoPath)));

        // Limpiar archivos de prueba
        if (file_exists(public_path($newPhotoPath))) {
            @unlink(public_path($newPhotoPath));
        }
    }
}

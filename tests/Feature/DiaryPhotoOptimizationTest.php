<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;
use App\Models\User;
use App\Models\Pet;
use App\Models\Adoption;
use App\Services\ImageService;

class DiaryPhotoOptimizationTest extends TestCase
{
    use RefreshDatabase;

    private User $adopter;
    private Adoption $adoption;

    protected function setUp(): void
    {
        parent::setUp();

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

        $pet = Pet::create([
            'name' => 'Copito',
            'species' => 'perro',
            'breed' => 'Maltés',
            'age_text' => '1 año',
            'status' => 'en_adopcion',
            'gender' => 'macho',
            'description' => 'Pequeño perrito.',
            'microchip_code' => '111222333444555',
            'characteristics' => [
                'kids' => true,
                'dogs' => true,
                'cats' => true,
                'energy' => 3,
                'space' => 'pequeño',
                'alone' => true,
            ],
        ]);

        $this->adoption = Adoption::create([
            'pet_id' => $pet->id,
            'adopter_id' => $this->adopter->id,
            'rescuer_id' => $this->adopter->id, // Simple self rescuer
            'status' => 'finalizado', // Must be finalized to post diaries
            'compatibility_score' => 100,
        ]);
    }

    /**
     * Test that the ImageService correctly resizes and converts images to WebP.
     */
    public function test_image_service_optimizes_and_converts_to_webp(): void
    {
        Storage::fake('local');

        // Create a large fake image (e.g., 2000x1500 pixels JPEG)
        $fakeImage = UploadedFile::fake()->image('test_photo.jpg', 2000, 1500);

        $imageService = new ImageService();
        $savedPath = $imageService->optimizeAndSave($fakeImage, 'private/test_diaries', 1200, 75);

        // Assert that the file is stored and has a .webp extension
        Storage::disk('local')->assertExists($savedPath);
        $this->assertStringEndsWith('.webp', $savedPath);

        // If GD is loaded, verify that the image dimensions are scaled down to 1200px max width
        if (extension_loaded('gd')) {
            $imgData = Storage::disk('local')->get($savedPath);
            $gdImage = imagecreatefromstring($imgData);
            $this->assertNotFalse($gdImage);
            
            $width = imagesx($gdImage);
            $height = imagesy($gdImage);
            
            $this->assertEquals(1200, $width);
            $this->assertEquals(900, $height); // 2000x1500 scaled to 1200 width maintains aspect ratio (1200 / 2000 * 1500 = 900)
            
            imagedestroy($gdImage);
        }
    }

    /**
     * Test that sending a diary entry with a photo uses the ImageService to store it in optimized WebP format.
     */
    public function test_diary_upload_uses_image_service_and_saves_optimized(): void
    {
        Storage::fake('local');

        $fakePhoto = UploadedFile::fake()->image('pet_diary.png', 1600, 1200);

        $response = $this->actingAs($this->adopter)->post(route('adoptions.diaries.store', $this->adoption->id), [
            'emoji_mood' => '😊',
            'comment' => 'Hoy la mascota estuvo súper tranquila en el parque y durmió bien.',
            'photo' => $fakePhoto,
        ]);

        $response->assertStatus(302); // Redirects back

        // Fetch the created diary entry
        $diaryEntry = $this->adoption->fresh()->diaries()->first();
        
        $this->assertNotNull($diaryEntry);
        $this->assertNotNull($diaryEntry->photo_path);
        
        // Assert the saved path ends with webp
        $this->assertStringEndsWith('.webp', $diaryEntry->photo_path);
        Storage::disk('local')->assertExists($diaryEntry->photo_path);
    }
}

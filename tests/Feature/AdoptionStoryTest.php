<?php

namespace Tests\Feature;

use App\Models\Adoption;
use App\Models\AdoptionDiary;
use App\Models\Pet;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AdoptionStoryTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Test que valida que cualquiera (invitado) puede ver la historia pública de una mascota adoptada.
     */
    public function test_guests_can_view_public_success_story(): void
    {
        $pet = Pet::create([
            'name' => 'Fido',
            'species' => 'perro',
            'status' => 'adoptado',
            'age_text' => '2 años',
            'gender' => 'macho',
            'description' => 'Un perrito muy cariñoso.',
        ]);
        $adopter = User::factory()->create(['role' => 'adoptante']);
        $rescuer = User::factory()->create(['role' => 'fundacion']);

        $adoption = Adoption::create([
            'pet_id' => $pet->id,
            'adopter_id' => $adopter->id,
            'rescuer_id' => $rescuer->id,
            'status' => 'finalizado',
            'compatibility_score' => 90,
        ]);

        // 1. Bitácora pública aprobada (debe ser visible)
        $approvedDiary = AdoptionDiary::create([
            'adoption_id' => $adoption->id,
            'report_date' => now()->toDateString(),
            'emoji_mood' => '😊',
            'comment' => 'El perrito está feliz y adaptado.',
            'is_public' => true,
            'moderation_status' => 'approved',
        ]);

        // 2. Bitácora pública pendiente (NO debe ser visible)
        $pendingDiary = AdoptionDiary::create([
            'adoption_id' => $adoption->id,
            'report_date' => now()->toDateString(),
            'emoji_mood' => '😐',
            'comment' => 'Bitácora en espera de moderación.',
            'is_public' => true,
            'moderation_status' => 'pending',
        ]);

        // 3. Bitácora privada (NO debe ser visible)
        $privateDiary = AdoptionDiary::create([
            'adoption_id' => $adoption->id,
            'report_date' => now()->toDateString(),
            'emoji_mood' => '😞',
            'comment' => 'Comentario privado del adoptante.',
            'is_public' => false,
            'moderation_status' => 'approved',
        ]);

        $response = $this->get(route('pets.story', $pet->id));

        $response->assertStatus(200);
        $response->assertInertia(fn ($page) => $page
            ->component('Pets/PublicStory')
            ->has('diaries', 1) // Solo debe venir 1 bitácora (la aprobada)
            ->where('diaries.0.id', $approvedDiary->id)
        );
    }

    /**
     * Test que valida que los usuarios no autorizados no pueden ingresar al panel de moderación.
     */
    public function test_unauthorized_users_are_blocked_from_moderation_panel(): void
    {
        $adopter = User::factory()->create(['role' => 'adoptante']);

        // 1. Invitado es redirigido a login
        $response = $this->get('/backoffice/moderation');
        $response->assertRedirect('/login');

        // 2. Adoptante regular recibe 403 Forbidden
        $response = $this->actingAs($adopter)->get('/backoffice/moderation');
        $response->assertStatus(403);
    }

    /**
     * Test que valida que el personal de la fundación/admin puede acceder al panel y moderar bitácoras.
     */
    public function test_staff_can_moderate_and_adopter_receives_huellas_on_approval(): void
    {
        $pet = Pet::create([
            'name' => 'Fido',
            'species' => 'perro',
            'status' => 'adoptado',
            'age_text' => '2 años',
            'gender' => 'macho',
            'description' => 'Un perrito muy cariñoso.',
        ]);
        $adopter = User::factory()->create(['role' => 'adoptante', 'points' => 100]);
        $rescuer = User::factory()->create(['role' => 'fundacion']);

        $adoption = Adoption::create([
            'pet_id' => $pet->id,
            'adopter_id' => $adopter->id,
            'rescuer_id' => $rescuer->id,
            'status' => 'finalizado',
            'compatibility_score' => 95,
        ]);

        $diary = AdoptionDiary::create([
            'adoption_id' => $adoption->id,
            'report_date' => now()->toDateString(),
            'emoji_mood' => '😊',
            'comment' => '¡Fotos de paseo público!',
            'is_public' => true,
            'moderation_status' => 'pending',
        ]);

        // 1. Acceder al panel de moderación como fundación
        $response = $this->actingAs($rescuer)->get('/backoffice/moderation');
        $response->assertStatus(200);
        $response->assertInertia(fn ($page) => $page
            ->component('Backoffice/Moderation/Index')
            ->has('diaries', 1)
            ->where('diaries.0.id', $diary->id)
        );

        // 2. Aprobar bitácora pública
        $response = $this->actingAs($rescuer)->post(route('backoffice.moderation.approve', $diary->id));
        $response->assertStatus(302);

        // Validar cambio en base de datos
        $this->assertDatabaseHas('adoption_diaries', [
            'id' => $diary->id,
            'moderation_status' => 'approved',
        ]);

        // Validar premio de 50 Huellas al adoptante
        $this->assertEquals(150, $adopter->fresh()->points);
    }

    /**
     * Test que valida que el rechazo de la bitácora funciona correctamente sin premiar.
     */
    public function test_staff_can_reject_moderation_without_awarding_points(): void
    {
        $pet = Pet::create([
            'name' => 'Fido',
            'species' => 'perro',
            'status' => 'adoptado',
            'age_text' => '2 años',
            'gender' => 'macho',
            'description' => 'Un perrito muy cariñoso.',
        ]);
        $adopter = User::factory()->create(['role' => 'adoptante', 'points' => 100]);
        $rescuer = User::factory()->create(['role' => 'fundacion']);

        $adoption = Adoption::create([
            'pet_id' => $pet->id,
            'adopter_id' => $adopter->id,
            'rescuer_id' => $rescuer->id,
            'status' => 'finalizado',
            'compatibility_score' => 95,
        ]);

        $diary = AdoptionDiary::create([
            'adoption_id' => $adoption->id,
            'report_date' => now()->toDateString(),
            'emoji_mood' => '😡',
            'comment' => 'Actualización con texto inapropiado.',
            'is_public' => true,
            'moderation_status' => 'pending',
        ]);

        // Rechazar publicación
        $response = $this->actingAs($rescuer)->post(route('backoffice.moderation.reject', $diary->id));
        $response->assertStatus(302);

        // Validar cambio en base de datos
        $this->assertDatabaseHas('adoption_diaries', [
            'id' => $diary->id,
            'moderation_status' => 'rejected',
        ]);

        // Validar que NO se le sumaron puntos
        $this->assertEquals(100, $adopter->fresh()->points);
    }

    /**
     * Test que valida que la creación de una bitácora pública con foto requiere la casilla de consentimiento.
     */
    public function test_creating_public_diary_with_photo_requires_consent(): void
    {
        \Illuminate\Support\Facades\Storage::fake('local');

        $pet = Pet::create([
            'name' => 'Fido',
            'species' => 'perro',
            'status' => 'adoptado',
            'age_text' => '2 años',
            'gender' => 'macho',
            'description' => 'Un perrito muy cariñoso.',
        ]);
        $adopter = User::factory()->create(['role' => 'adoptante']);
        $rescuer = User::factory()->create(['role' => 'fundacion']);

        $adoption = Adoption::create([
            'pet_id' => $pet->id,
            'adopter_id' => $adopter->id,
            'rescuer_id' => $rescuer->id,
            'status' => 'finalizado',
            'compatibility_score' => 95,
        ]);

        $photo = \Illuminate\Http\UploadedFile::fake()->image('pup.jpg');

        // 1. Postear con foto y hacerlo público, pero SIN consentimiento de foto
        $response = $this->actingAs($adopter)->post(route('adoptions.diaries.store', $adoption->id), [
            'emoji_mood' => '😊',
            'comment' => 'El perrito comió súper bien hoy en casa.',
            'photo' => $photo,
            'is_public' => true,
            'photo_consent' => false,
        ]);

        $response->assertSessionHasErrors('photo_consent');

        // 2. Postear con foto y hacerlo público, CON consentimiento de foto
        $response = $this->actingAs($adopter)->post(route('adoptions.diaries.store', $adoption->id), [
            'emoji_mood' => '😊',
            'comment' => 'El perrito comió súper bien hoy en casa.',
            'photo' => $photo,
            'is_public' => true,
            'photo_consent' => true,
        ]);

        $response->assertSessionHasNoErrors();
        $this->assertDatabaseHas('adoption_diaries', [
            'comment' => 'El perrito comió súper bien hoy en casa.',
            'is_public' => true,
            'photo_consent' => true,
        ]);
    }
}

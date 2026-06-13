<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\User;
use App\Models\Pet;
use App\Models\Adoption;
use App\Models\AdoptionDiary;
use App\Models\UserBadge;
use App\Models\SystemMilestone;
use App\Models\UberTrip;
use App\Services\MilestoneService;
use Illuminate\Foundation\Testing\RefreshDatabase;

class MilestoneTest extends TestCase
{
    use RefreshDatabase;

    private MilestoneService $milestoneService;

    protected function setUp(): void
    {
        parent::setUp();
        $this->milestoneService = new MilestoneService();
    }

    /**
     * Prueba que el hito global Comunidad Fundadora se active al llegar a 100 usuarios y premie a todos.
     */
    public function test_global_milestone_community_fundadora_triggers_at_15_users(): void
    {
        // 1. Crear 14 usuarios iniciales
        User::factory()->count(14)->create([
            'points' => 0,
        ]);

        $this->milestoneService->checkGlobalMilestones();

        $milestone = SystemMilestone::where('milestone_key', 'comunidad_fundadora')->first();
        $this->assertNotNull($milestone);
        $this->assertFalse($milestone->is_reached);
        $this->assertEquals(14, $milestone->current_value);

        // Ningún usuario debe tener puntos de bono aún
        $this->assertEquals(0, User::first()->points);

        // 2. Crear el usuario número 15 para cruzar el umbral
        User::factory()->create([
            'points' => 0,
        ]);

        $this->milestoneService->checkGlobalMilestones();

        $milestone->refresh();
        $this->assertTrue($milestone->is_reached);
        $this->assertEquals(15, $milestone->current_value);

        // Todos los usuarios deben haber recibido el bono de +50 puntos
        $this->assertEquals(50, User::first()->points);
    }

    /**
     * Prueba que el hito individual "Pionero" se otorgue al completar el perfil etológico.
     */
    public function test_user_badge_pionero_awarded_on_profile_completion(): void
    {
        $user = User::create([
            'name' => 'Adoptante Test',
            'email' => 'adopter@test.com',
            'password' => bcrypt('password'),
            'role' => 'adoptante',
            'points' => 0,
            'verification_data' => null,
        ]);

        // Intentar chequear hitos con perfil incompleto (sin RUT/DNI)
        $awarded = $this->milestoneService->checkUserMilestones($user);
        $this->assertEmpty($awarded);
        $this->assertFalse(UserBadge::where('user_id', $user->id)->where('badge_key', 'pionero')->exists());

        // Completar perfil (tiene RUT/DNI en verification_data)
        $user->verification_data = [
            'identification_code' => '12.345.678-9',
            'house_type' => 'departamento',
            'has_kids' => false,
            'has_dogs' => false,
            'has_cats' => false,
            'hours_alone' => 4,
            'energy_preference' => 3,
            'budget_confirmed' => true,
        ];
        $user->save();

        $awarded = $this->milestoneService->checkUserMilestones($user);
        $this->assertCount(1, $awarded);
        $this->assertEquals('pionero', $awarded[0]->badge_key);

        $user->refresh();
        $this->assertEquals(50, $user->points); // 50 puntos otorgados
        $this->assertTrue(UserBadge::where('user_id', $user->id)->where('badge_key', 'pionero')->exists());
    }

    /**
     * Prueba que el hito individual "Tutor Comprometido" se otorgue tras 3 reportes diarios positivos aprobados.
     */
    public function test_user_badge_tutor_comprometido_awarded_on_three_positive_diaries(): void
    {
        // Adoptante, Rescatista y Mascota
        $adopter = User::factory()->create(['role' => 'adoptante', 'points' => 0]);
        $rescuer = User::factory()->create(['role' => 'rescatista']);
        $pet = Pet::create([
            'name' => 'Puppy',
            'species' => 'perro',
            'status' => 'en_adopcion',
            'gender' => 'macho',
        ]);

        $adoption = Adoption::create([
            'pet_id' => $pet->id,
            'adopter_id' => $adopter->id,
            'rescuer_id' => $rescuer->id,
            'status' => 'finalizado',
        ]);

        // Registrar 2 diarios correctos
        for ($i = 0; $i < 2; $i++) {
            AdoptionDiary::create([
                'adoption_id' => $adoption->id,
                'emoji_mood' => '😊',
                'comment' => 'Todo marcha espectacular en el hogar.',
                'ai_sentiment_score' => 0.8,
                'ai_abuse_flag' => false,
                'moderation_status' => 'approved',
                'report_date' => now()->subDays($i)->toDateString(),
            ]);
        }

        // Evaluar hitos (todavía no cumple, lleva 2)
        $awarded = $this->milestoneService->checkUserMilestones($adopter);
        $this->assertEmpty($awarded);

        // Crear el tercer diario para cruzar el umbral
        AdoptionDiary::create([
            'adoption_id' => $adoption->id,
            'emoji_mood' => '😊',
            'comment' => 'Muy adaptado a la familia.',
            'ai_sentiment_score' => 0.9,
            'ai_abuse_flag' => false,
            'moderation_status' => 'approved',
            'report_date' => now()->toDateString(),
        ]);

        // Evaluar hitos (ahora sí debe premiar)
        $awarded = $this->milestoneService->checkUserMilestones($adopter);
        $this->assertCount(1, $awarded);
        $this->assertEquals('tutor_comprometido', $awarded[0]->badge_key);

        $adopter->refresh();
        $this->assertEquals(100, $adopter->points); // +100 puntos otorgados
        $this->assertTrue(UserBadge::where('user_id', $adopter->id)->where('badge_key', 'tutor_comprometido')->exists());
    }
}

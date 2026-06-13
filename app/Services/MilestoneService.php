<?php

namespace App\Services;

use App\Models\User;
use App\Models\UserBadge;
use App\Models\SystemMilestone;
use App\Models\Pet;
use App\Models\UberTrip;
use App\Models\AdoptionDiary;
use App\Services\AuditService;
use Illuminate\Support\Facades\DB;

class MilestoneService
{
    /**
     * Inicializa los hitos del sistema si no existen.
     */
    public function initializeMilestones(): void
    {
        $milestones = [
            [
                'milestone_key' => 'comunidad_fundadora',
                'name' => 'Comunidad Fundadora (15 Pre-registros)',
                'threshold' => 15,
            ],
            [
                'milestone_key' => 'primeros_pasos',
                'name' => 'Primeros Pasos (5 Mascotas Registradas)',
                'threshold' => 5,
            ],
            [
                'milestone_key' => 'logistica_activa',
                'name' => 'Logística Activa (2 Traslados Completados)',
                'threshold' => 2,
            ]
        ];

        foreach ($milestones as $m) {
            SystemMilestone::firstOrCreate(
                ['milestone_key' => $m['milestone_key']],
                [
                    'name' => $m['name'],
                    'threshold' => $m['threshold'],
                    'current_value' => 0,
                    'is_reached' => false,
                ]
            );
        }
    }

    /**
     * Verifica y actualiza los hitos colectivos de la red.
     */
    public function checkGlobalMilestones(): void
    {
        $this->initializeMilestones();

        // 1. Comunidad Fundadora: Conteo de usuarios
        $userCount = User::count();
        $this->evaluateGlobalMilestone('comunidad_fundadora', $userCount, function () {
            // Automatización: Bono de +50 Huellas a todos los usuarios actuales
            User::query()->increment('points', 50);

            AuditService::log('global_milestone_automation', null, null, [
                'milestone' => 'comunidad_fundadora',
                'action' => 'awarded_50_points_to_all_users',
                'user_count' => User::count(),
            ]);
        });

        // 2. Primeros Pasos: Conteo de mascotas
        $petCount = Pet::count();
        $this->evaluateGlobalMilestone('primeros_pasos', $petCount);

        // 3. Logística Activa: Conteo de viajes completados
        $tripCount = UberTrip::where('status', 'completado')->count();
        $this->evaluateGlobalMilestone('logistica_activa', $tripCount);
    }

    /**
     * Evalúa un hito colectivo individual y ejecuta su automatización si corresponde.
     */
    private function evaluateGlobalMilestone(string $key, int $currentValue, ?callable $automation = null): void
    {
        $milestone = SystemMilestone::where('milestone_key', $key)->first();
        if (!$milestone) return;

        $milestone->current_value = $currentValue;

        if (!$milestone->is_reached && $currentValue >= $milestone->threshold) {
            $milestone->is_reached = true;
            $milestone->reached_at = now();
            $milestone->save();

            AuditService::log('global_milestone_reached', null, null, [
                'milestone_key' => $key,
                'name' => $milestone->name,
                'threshold' => $milestone->threshold,
                'current_value' => $currentValue,
            ]);

            if ($automation) {
                $automation();
            }
        } else {
            $milestone->save();
        }
    }

    /**
     * Verifica y otorga medallas a un usuario específico según sus acciones.
     */
    public function checkUserMilestones(User $user): array
    {
        $awarded = [];

        // 1. Medalla: Pionero
        // Se otorga si completó su perfil etológico
        $hasPionero = UserBadge::where('user_id', $user->id)->where('badge_key', 'pionero')->exists();
        if (!$hasPionero && isset($user->verification_data['identification_code'])) {
            $badge = UserBadge::create([
                'user_id' => $user->id,
                'badge_key' => 'pionero',
                'badge_name' => 'Perfil Pionero',
                'badge_icon' => '🥇',
            ]);
            $user->increment('points', 50);

            AuditService::log('badge_earned', $badge, null, [
                'badge_key' => 'pionero',
                'points_awarded' => 50,
            ]);
            $awarded[] = $badge;
        }

        // 2. Medalla: Tutor Comprometido
        // Se otorga si tiene 3 o más reportes diarios aprobados sin flags de abuso y con sentimiento positivo (> 0)
        $hasTutor = UserBadge::where('user_id', $user->id)->where('badge_key', 'tutor_comprometido')->exists();
        if (!$hasTutor) {
            $diariesCount = AdoptionDiary::whereHas('adoption', function ($q) use ($user) {
                $q->where('adopter_id', $user->id);
            })
            ->where('moderation_status', 'approved')
            ->where('ai_abuse_flag', false)
            ->where('ai_sentiment_score', '>=', 0.00)
            ->count();

            if ($diariesCount >= 3) {
                $badge = UserBadge::create([
                    'user_id' => $user->id,
                    'badge_key' => 'tutor_comprometido',
                    'badge_name' => 'Tutor Comprometido',
                    'badge_icon' => '🏡',
                ]);
                $user->increment('points', 100);

                AuditService::log('badge_earned', $badge, null, [
                    'badge_key' => 'tutor_comprometido',
                    'points_awarded' => 100,
                    'reward' => 'Cupón de Descuento 15% Bazar',
                ]);
                $awarded[] = $badge;
            }
        }

        // 3. Medalla: Héroe del Camino
        // Se otorga si ha completado al menos 3 viajes de Uber Solidario
        $hasHeroe = UserBadge::where('user_id', $user->id)->where('badge_key', 'heroe_camino')->exists();
        if (!$hasHeroe) {
            $completedTrips = UberTrip::where('driver_id', $user->id)
                ->where('status', 'completado')
                ->count();

            if ($completedTrips >= 3) {
                $badge = UserBadge::create([
                    'user_id' => $user->id,
                    'badge_key' => 'heroe_camino',
                    'badge_name' => 'Héroe del Camino',
                    'badge_icon' => '🚗',
                ]);
                $user->increment('points', 150);

                AuditService::log('badge_earned', $badge, null, [
                    'badge_key' => 'heroe_camino',
                    'points_awarded' => 150,
                ]);
                $awarded[] = $badge;
            }
        }

        return $awarded;
    }
}

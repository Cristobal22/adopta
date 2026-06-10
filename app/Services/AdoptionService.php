<?php

namespace App\Services;

use App\Models\Pet;
use App\Models\User;

class AdoptionService
{
    /**
     * Calcula la compatibilidad (0.00% a 100.00%) entre el adoptante y la mascota.
     * También devuelve si hay algún bloqueo crítico de seguridad.
     */
    public function calculateCompatibility(User $user, Pet $pet): array
    {
        $vData = $user->verification_data;
        $pChars = $pet->characteristics;

        if (!$vData || !$pChars) {
            return [
                'score' => 0.00,
                'is_compatible' => false,
                'reasons' => ['El perfil del adoptante o de la mascota está incompleto.'],
            ];
        }

        $score = 100.00;
        $reasons = [];
        $isBlocked = false;

        // 0. Bloqueo Crítico: Lista Negra
        $identificationCode = $vData['identification_code'] ?? null;
        $isBlacklisted = \App\Models\Blacklist::where('email', $user->email)
            ->when($identificationCode, function ($query, $code) {
                return $query->orWhere('identification_code', $code);
            })
            ->first();

        if ($isBlacklisted) {
            return [
                'score' => 0.00,
                'is_compatible' => false,
                'reasons' => ['El adoptante se encuentra registrado en la lista negra: ' . $isBlacklisted->reason],
            ];
        }

        // 1. Advertencia: Niños pequeños
        // Si la mascota NO es apta para niños y el adoptante tiene niños
        if (isset($pChars['kids']) && !$pChars['kids'] && isset($vData['has_kids']) && $vData['has_kids']) {
            $score -= 40;
            $reasons[] = 'Esta mascota no es recomendada para hogares con niños por motivos de seguridad.';
        }

        // 2. Advertencia: Otros Perros
        if (isset($pChars['dogs']) && !$pChars['dogs'] && isset($vData['has_dogs']) && $vData['has_dogs']) {
            $score -= 30;
            $reasons[] = 'Esta mascota no convive bien con otros perros y tienes perros registrados en tu hogar.';
        }

        // 3. Advertencia: Otros Gatos
        if (isset($pChars['cats']) && !$pChars['cats'] && isset($vData['has_cats']) && $vData['has_cats']) {
            $score -= 30;
            $reasons[] = 'Esta mascota no convive bien con gatos y tienes gatos en tu hogar.';
        }

        // 4. Nivel de Energía (1 a 5)
        if (isset($pChars['energy']) && isset($vData['energy_preference'])) {
            $energyDiff = abs((int)$pChars['energy'] - (int)$vData['energy_preference']);
            
            // Advertencia por brecha de energía extrema (>= 3)
            if ($energyDiff >= 3) {
                $reasons[] = 'La diferencia de nivel de energía entre la mascota (' . $pChars['energy'] . '/5) y tu preferencia (' . $vData['energy_preference'] . '/5) es extrema, lo cual pone en riesgo el bienestar animal.';
            }

            if ($energyDiff > 0) {
                $penalty = $energyDiff * 10; // 10% de penalización por punto de diferencia
                $score -= $penalty;
                $reasons[] = 'Diferencia en el nivel de energía requerido (' . $pChars['energy'] . '/5) en contraste con tu preferencia (' . $vData['energy_preference'] . '/5).';
            }
        }

        // 5. Espacio en el hogar
        if (isset($pChars['space']) && isset($vData['house_type'])) {
            $spaceReq = $pChars['space']; // 'pequeño', 'mediano', 'grande'
            $houseType = $vData['house_type']; // 'departamento', 'casa_sin_patio', 'casa_patio_mediano', 'casa_patio_grande'

            if ($spaceReq === 'grande' && in_array($houseType, ['departamento', 'casa_sin_patio'])) {
                $score -= 20;
                $reasons[] = 'La mascota requiere un espacio amplio o patio grande y vives en un espacio muy reducido.';
            } elseif ($spaceReq === 'mediano' && $houseType === 'departamento') {
                $score -= 10;
                $reasons[] = 'La mascota se adapta mejor a hogares con patio de tamaño medio.';
            }
        }

        // 6. Horas a solas
        if (isset($pChars['alone']) && !$pChars['alone'] && isset($vData['hours_alone']) && (int)$vData['hours_alone'] > 6) {
            $score -= 15;
            $reasons[] = 'La mascota sufre de ansiedad por separación y requiere estar acompañada la mayor parte del tiempo (estarás fuera por más de 6 horas).';
        }

        // Asegurar rango 0 - 100
        $score = max(0.00, $score);

        // Advertencia si el puntaje final es inferior al 65.00% (Auditoría de Bienestar Animal)
        if ($score < 65.00) {
            $reasons[] = 'El puntaje de compatibilidad total (' . round($score, 2) . '%) es inferior al mínimo recomendado del 65.00%.';
        }

        return [
            'score' => round($score, 2),
            'is_compatible' => !$isBlocked,
            'reasons' => $reasons,
        ];
    }
}

<?php

namespace App\Http\Controllers;

use App\Models\Pet;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class PublicStoryController extends Controller
{
    /**
     * Muestra la historia de éxito de una mascota adoptada.
     */
    public function showStory(Pet $pet): Response
    {
        // Obtener la adopción finalizada de esta mascota
        $adoption = $pet->adoptions()->where('status', 'finalizado')->with('adopter')->first();

        $petData = [
            'id' => $pet->id,
            'name' => $pet->name,
            'species' => $pet->species,
            'breed' => $pet->breed,
            'age_text' => $pet->age_text,
            'description' => $pet->description,
            'photo_path' => $pet->photo_path,
        ];

        $adoptionData = null;
        $diariesData = [];

        if ($adoption) {
            $adoptionData = [
                'id' => $adoption->id,
                'updated_at' => $adoption->updated_at,
                'commune' => $adoption->adopter ? explode(',', $adoption->adopter->city)[0] : 'Santiago',
            ];

            $diariesData = $adoption->diaries()
                ->where('is_public', true)
                ->where('moderation_status', 'approved')
                ->orderBy('report_date', 'asc')
                ->get(['id', 'adoption_id', 'report_date', 'emoji_mood', 'comment', 'photo_path']);
        }

        return Inertia::render('Pets/PublicStory', [
            'pet' => $petData,
            'adoption' => $adoptionData,
            'diaries' => $diariesData,
        ]);
    }
}

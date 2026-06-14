<?php

namespace App\Http\Controllers;

use App\Models\Pet;
use Inertia\Inertia;
use Inertia\Response;

class FlyerController extends Controller
{
    /**
     * Muestra el generador de flyers de la mascota.
     */
    public function show(Pet $pet): Response
    {
        $pet->load('sponsorships');
        return Inertia::render('Pets/Flyer', [
            'pet' => $pet,
        ]);
    }
}

<?php

namespace App\Http\Controllers;

use App\Models\CommunitySpot;
use App\Models\PackWalk;
use App\Services\AuditService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;
use Inertia\Response;

class CommunityController extends Controller
{
    /**
     * Muestra el mapa colaborativo Radar Pet-Friendly y Paseos en Manada.
     */
    public function showRadar(): Response
    {
        $spots = CommunitySpot::all();
        $walks = PackWalk::where('walk_date', '>=', now())
            ->orderBy('walk_date', 'asc')
            ->get();

        return Inertia::render('Community/Radar', [
            'spots' => $spots,
            'walks' => $walks,
        ]);
    }

    /**
     * Registra un nuevo comercio, parque u hotel Pet-Friendly.
     */
    public function storeSpot(Request $request): RedirectResponse
    {
        $request->validate([
            'name' => ['required', 'string', 'max:150'],
            'type' => ['required', 'in:parque,restaurant,hotel,veterinaria'],
            'latitude' => ['required', 'numeric', 'min:-90', 'max:90'],
            'longitude' => ['required', 'numeric', 'min:-180', 'max:180'],
            'description' => ['nullable', 'string', 'max:500'],
            'address' => ['nullable', 'string', 'max:255'],
        ]);

        $spot = CommunitySpot::create($request->all());

        AuditService::log('create_community_spot', $spot, null, $spot->toArray());

        return back()->with('success', '¡Gracias por colaborar! El lugar Pet-Friendly "' . $spot->name . '" ha sido agregado al mapa.');
    }

    /**
     * Coordina un nuevo paseo en manada para socializar perros en el barrio.
     */
    public function storeWalk(Request $request): RedirectResponse
    {
        $request->validate([
            'title' => ['required', 'string', 'max:150'],
            'neighborhood' => ['required', 'string', 'max:100'],
            'walk_date' => ['required', 'date', 'after:now'],
            'latitude' => ['required', 'numeric', 'min:-90', 'max:90'],
            'longitude' => ['required', 'numeric', 'min:-180', 'max:180'],
            'description' => ['nullable', 'string', 'max:500'],
            'agreement' => ['required', 'accepted'],
        ]);

        $walk = PackWalk::create($request->all());

        AuditService::log('create_pack_walk', $walk, null, $walk->toArray());

        // Otorgar 15 Huellas al usuario por organizar un evento comunitario
        Auth::user()->increment('points', 15);

        return back()->with('success', '¡Paseo en manada coordinado! Sumaste 15 Huellas por tu colaboración comunitaria.');
    }
}

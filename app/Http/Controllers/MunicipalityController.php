<?php

namespace App\Http\Controllers;

use App\Models\Adoption;
use App\Models\AdoptionDiary;
use App\Models\Operative;
use App\Models\Pet;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;
use Inertia\Response;

class MunicipalityController extends Controller
{
    /**
     * Muestra el panel principal de la municipalidad con sus estadísticas comunales.
     */
    public function index(): Response
    {
        $user = Auth::user();
        if ($user->role !== 'municipalidad') {
            abort(403, 'No tienes permisos para acceder a esta área municipal.');
        }

        $commune = $user->city; // Formato: "Comuna, Región"

        // 1. Obtener adopciones vinculadas a la comuna (donde el adoptante viva en esta comuna)
        $adoptions = Adoption::whereHas('adopter', function ($q) use ($commune) {
            $q->where('city', $commune);
        })->with(['pet', 'adopter', 'rescuer'])->get();

        // 2. Obtener mascotas de la comuna (adoptadas o albergadas por rescatistas locales)
        $pets = Pet::whereHas('adoptions', function ($q) use ($commune) {
            $q->whereHas('adopter', function ($ad) use ($commune) {
                $ad->where('city', $commune);
            });
        })->orWhere(function ($q) use ($commune) {
            // Mascotas registradas por rescatistas de esta comuna
            $q->whereHas('adoptions', function ($sub) use ($commune) {
                $sub->whereHas('rescuer', function ($res) use ($commune) {
                    $res->where('city', $commune);
                });
            });
        })->get(['id', 'name', 'species', 'status', 'latitude', 'longitude']);

        // 3. Obtener operativos veterinarios programados de la comuna
        $operatives = Operative::where('commune', $commune)
            ->orderBy('date', 'asc')
            ->get();

        // 4. Contar alertas críticas de maltrato/SOS activas en la comuna
        $abuseAlertsCount = AdoptionDiary::where(function ($query) {
                $query->where('ai_abuse_flag', true)
                      ->orWhere('ai_sentiment_score', '<', -0.4);
            })
            ->whereHas('adoption', function ($q) use ($commune) {
                $q->whereHas('adopter', function ($ad) use ($commune) {
                    $ad->where('city', $commune);
                });
            })
            ->count();

        return Inertia::render('Municipality/Dashboard', [
            'commune' => $commune,
            'adoptionsCount' => $adoptions->count(),
            'petsCount' => $pets->count(),
            'abuseAlertsCount' => $abuseAlertsCount,
            'operatives' => $operatives,
            'pets' => $pets,
            'adoptions' => $adoptions,
        ]);
    }

    /**
     * Registra una nueva campaña u operativo veterinario comunal.
     */
    public function storeOperative(Request $request): RedirectResponse
    {
        $user = Auth::user();
        if ($user->role !== 'municipalidad') {
            abort(403);
        }

        $request->validate([
            'title' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'date' => ['required', 'date'],
            'capacity' => ['required', 'integer', 'min:1'],
        ]);

        Operative::create([
            'municipality_id' => $user->id,
            'title' => $request->title,
            'description' => $request->description,
            'date' => $request->date,
            'commune' => $user->city,
            'capacity' => $request->capacity,
            'status' => 'programado',
        ]);

        return back()->with('success', 'Operativo veterinario comunal programado y publicado con éxito.');
    }

    /**
     * Muestra las alertas de maltrato detectadas por la IA para su fiscalización.
     */
    public function inspectAbuseFlags(): Response
    {
        $user = Auth::user();
        if ($user->role !== 'municipalidad') {
            abort(403);
        }

        $commune = $user->city;

        $alerts = AdoptionDiary::where(function ($query) {
                $query->where('ai_abuse_flag', true)
                      ->orWhere('ai_sentiment_score', '<', -0.4);
            })
            ->whereHas('adoption', function ($q) use ($commune) {
                $q->whereHas('adopter', function ($ad) use ($commune) {
                    $ad->where('city', $commune);
                });
            })
            ->with(['adoption.pet', 'adoption.adopter'])
            ->orderBy('id', 'desc')
            ->get();

        return Inertia::render('Municipality/AuditAbuse', [
            'commune' => $commune,
            'alerts' => $alerts,
        ]);
    }
}

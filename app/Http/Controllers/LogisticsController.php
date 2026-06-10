<?php

namespace App\Http\Controllers;

use App\Models\UberTrip;
use App\Models\Pet;
use App\Services\AuditService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;
use Inertia\Response;

class LogisticsController extends Controller
{
    /**
     * Muestra el panel de Uber Solidario y Banco de Recursos.
     */
    public function showLogisticsHub(): Response
    {
        $user = Auth::user();
        
        // Listar viajes solicitados o donde el usuario es parte
        $availableTrips = UberTrip::with(['pet', 'requester', 'driver'])
            ->where('status', 'solicitado')
            ->orderBy('created_at', 'desc')
            ->get();

        $myTrips = UberTrip::with(['pet', 'requester', 'driver'])
            ->where('driver_id', $user->id)
            ->orWhere('requester_id', $user->id)
            ->orderBy('updated_at', 'desc')
            ->get();

        // Obtener mascotas disponibles para traslados
        $pets = Pet::whereIn('status', ['rescatado', 'en_transito'])->get();

        // Simulación del Banco de Recursos (base de datos en memoria / json)
        $resources = [
            [
                'id' => 1,
                'title' => 'Saco de alimento perro adulto 15kg',
                'type' => 'alimento',
                'status' => 'disponible',
                'donor' => 'Juan Pérez',
                'description' => 'Marca premium, cerrado y sellado.',
            ],
            [
                'id' => 2,
                'title' => 'Plato comedero de acero inoxidable doble',
                'type' => 'juguete',
                'status' => 'disponible',
                'donor' => 'María Gómez',
                'description' => 'Comedero con base antideslizante para cachorros o gatos.',
            ],
        ];

        return Inertia::render('Logistics/Hub', [
            'availableTrips' => $availableTrips,
            'myTrips' => $myTrips,
            'pets' => $pets,
            'resources' => $resources,
        ]);
    }

    /**
     * Crea una nueva solicitud de traslado (Uber Solidario).
     */
    public function requestTrip(Request $request): RedirectResponse
    {
        $user = Auth::user();
        
        $validated = $request->validate([
            'pet_id' => ['nullable', 'exists:pets,id'],
            'origin' => ['required', 'string', 'max:255'],
            'destination' => ['required', 'string', 'max:255'],
        ]);

        $trip = UberTrip::create([
            'pet_id' => $validated['pet_id'],
            'requester_id' => $user->id,
            'origin' => $validated['origin'],
            'destination' => $validated['destination'],
            'status' => 'solicitado',
        ]);

        AuditService::log('request_uber_trip', $trip, null, $trip->toArray());

        return back()->with('success', 'Solicitud de traslado voluntario publicada con éxito.');
    }

    /**
     * Conductor acepta realizar el traslado.
     */
    public function acceptTrip(UberTrip $trip): RedirectResponse
    {
        $user = Auth::user();
        if ($trip->requester_id === $user->id) {
            return back()->withErrors(['driver' => 'No puedes aceptar tu propio viaje solicitado.']);
        }

        $oldValues = $trip->toArray();
        $trip->update([
            'driver_id' => $user->id,
            'status' => 'aceptado',
        ]);

        AuditService::log('accept_uber_trip', $trip, $oldValues, $trip->getChanges());

        return back()->with('success', 'Has aceptado realizar este traslado. ¡Gracias por tu solidaridad!');
    }

    /**
     * Marca el viaje como completado.
     */
    public function completeTrip(UberTrip $trip): RedirectResponse
    {
        $user = Auth::user();
        if ($trip->driver_id !== $user->id && $user->role !== 'admin') {
            abort(403);
        }

        $oldValues = $trip->toArray();
        $trip->update([
            'status' => 'completado',
        ]);

        // Asignar puntos por voluntariado en el Club de Huellas (Gamificación)
        $driver = $trip->driver;
        if ($driver) {
            $driver->increment('points', 50); // 50 puntos por traslado voluntario
        }

        AuditService::log('complete_uber_trip', $trip, $oldValues, $trip->getChanges());

        return back()->with('success', 'Traslado marcado como completado. Se han asignado 50 puntos a tu cuenta.');
    }
}

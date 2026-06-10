<?php

namespace App\Http\Controllers;

use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;
use Inertia\Response;

class UserController extends Controller
{
    /**
     * Muestra el formulario de estilo de vida del adoptante.
     */
    public function showProfileForm(): Response
    {
        $user = Auth::user();
        $hasActiveAdoptions = $user->adoptionsAsAdopter()->whereIn('status', ['solicitado', 'en_proceso', 'finalizado'])->exists();
        return Inertia::render('Adopter/ProfileForm', [
            'verification_data' => $user->verification_data ?? [],
            'phone' => $user->phone,
            'address' => $user->address,
            'city' => $user->city,
            'hasActiveAdoptions' => $hasActiveAdoptions,
        ]);
    }

    /**
     * Guarda las respuestas del formulario de estilo de vida del adoptante.
     */
    public function updateProfile(Request $request): RedirectResponse
    {
        $user = Auth::user();

        $validated = $request->validate([
            'phone' => ['required', 'string', 'max:20'],
            'address' => ['required', 'string', 'max:255'],
            'city' => ['required', 'string', 'max:100'],
            'identification_code' => ['required', 'string', 'max:30'],
            'house_type' => ['required', 'in:departamento,casa_sin_patio,casa_patio_mediano,casa_patio_grande'],
            'has_kids' => ['required', 'boolean'],
            'has_dogs' => ['required', 'boolean'],
            'has_cats' => ['required', 'boolean'],
            'hours_alone' => ['required', 'integer', 'min:0', 'max:24'],
            'energy_preference' => ['required', 'integer', 'min:1', 'max:5'],
            'budget_confirmed' => ['required', 'boolean'],
        ]);

        $oldData = $user->verification_data ?? [];
        $oldDni = $oldData['identification_code'] ?? null;

        // Si ya posee trámites iniciados, prohibir modificación de DNI/RUT por seguridad contractual
        $hasAdoptions = $user->adoptionsAsAdopter()->whereIn('status', ['solicitado', 'en_proceso', 'finalizado'])->exists();
        if ($oldDni && $validated['identification_code'] !== $oldDni && $hasAdoptions) {
            return back()->withErrors([
                'identification_code' => 'No puedes modificar tu documento de identidad una vez que tienes procesos de adopción activos o finalizados.',
            ]);
        }

        $oldValues = [
            'phone' => $user->phone,
            'address' => $user->address,
            'city' => $user->city,
            'verification_data' => $user->verification_data,
        ];

        // Guardar cambios
        $user->update([
            'phone' => $validated['phone'],
            'address' => $validated['address'],
            'city' => $validated['city'],
            'verification_data' => [
                'identification_code' => $validated['identification_code'],
                'house_type' => $validated['house_type'],
                'has_kids' => $validated['has_kids'],
                'has_dogs' => $validated['has_dogs'],
                'has_cats' => $validated['has_cats'],
                'hours_alone' => $validated['hours_alone'],
                'energy_preference' => $validated['energy_preference'],
                'budget_confirmed' => $validated['budget_confirmed'],
            ],
            'status' => 'verificado', // Marcamos al adoptante como verificado al completar sus datos
        ]);

        $newValues = [
            'phone' => $user->phone,
            'address' => $user->address,
            'city' => $user->city,
            'verification_data' => $user->verification_data,
        ];

        \App\Services\AuditService::log('update_user_profile', $user, $oldValues, $newValues);

        return redirect()->route('dashboard')->with('success', 'Formulario de estilo de vida guardado correctamente.');
    }
}

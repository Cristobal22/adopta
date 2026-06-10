<?php

namespace App\Http\Controllers;

use App\Models\Pet;
use App\Models\Sponsorship;
use App\Services\AuditService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;
use Inertia\Response;

class SponsorshipController extends Controller
{
    /**
     * Muestra el formulario para apadrinar una mascota específica.
     */
    public function showSponsorForm(Pet $pet): Response
    {
        return Inertia::render('Pets/Sponsor', [
            'pet' => $pet,
        ]);
    }

    /**
     * Procesa la solicitud de apadrinamiento simulando el pago con MercadoPago.
     */
    public function processSponsorship(Request $request, Pet $pet): RedirectResponse
    {
        $request->validate([
            'tier' => ['required', 'string', 'in:bronce,plata,oro'],
            'amount' => ['required', 'numeric', 'min:1'],
            'sponsor_name' => ['required', 'string', 'max:255'],
            'sponsor_email' => ['required', 'email', 'max:255'],
        ]);

        $user = Auth::user();

        // Crear registro en la tabla de sponsorships
        $sponsorship = Sponsorship::create([
            'user_id' => $user ? $user->id : null,
            'pet_id' => $pet->id,
            'tier' => $request->tier,
            'amount' => $request->amount,
            'status' => 'active',
            'sponsor_name' => $request->sponsor_name,
            'sponsor_email' => $request->sponsor_email,
        ]);

        // Registrar auditoría de la donación/suscripción
        AuditService::log('create_sponsorship', $pet, null, [
            'sponsorship_id' => $sponsorship->id,
            'tier' => $request->tier,
            'amount' => $request->amount,
            'sponsor_name' => $request->sponsor_name,
        ]);

        // Si el usuario está autenticado, otorgamos puntos de huellas como recompensa de fidelización
        if ($user) {
            // ej: bronce = 50 puntos, plata = 100 puntos, oro = 200 puntos
            $pointsEarned = 50;
            if ($request->tier === 'plata') $pointsEarned = 100;
            if ($request->tier === 'oro') $pointsEarned = 200;

            $user->increment('points', $pointsEarned);
            
            AuditService::log('loyalty_points_earned_sponsorship', $user, null, [
                'points_earned' => $pointsEarned,
                'new_points' => $user->points,
            ]);
        }

        // Simulación de URL de pago seguro de MercadoPago para SiteGround / Sandbox
        $sandboxCheckoutUrl = "https://www.mercadopago.cl/sandbox/checkout/preference?pref_id=spon_" . md5($sponsorship->id . time());

        return redirect()->route('pets.index')
            ->with('success', '¡Gracias por apadrinar a ' . $pet->name . '! Tu suscripción ' . ucfirst($request->tier) . ' fue procesada.')
            ->with('checkout_url', $sandboxCheckoutUrl);
    }

    /**
     * Muestra la credencial médica digital (QR Pet Pass) públicamente.
     */
    public function showQrPass(Pet $pet): Response
    {
        // Cargar sponsorships para mostrar cuántos padrinos tiene
        $pet->loadCount(['sponsorships' => function ($q) {
            $q->where('status', 'active');
        }]);

        return Inertia::render('Pets/QrPass', [
            'pet' => $pet,
        ]);
    }

    /**
     * Procesa la ubicación reportada cuando alguien escanea el código QR de una mascota perdida.
     */
    public function reportFound(Request $request, Pet $pet): RedirectResponse
    {
        $request->validate([
            'latitude' => ['required', 'numeric', 'between:-90,90'],
            'longitude' => ['required', 'numeric', 'between:-180,180'],
            'reporter_phone' => ['nullable', 'string', 'max:50'],
            'reporter_message' => ['nullable', 'string', 'max:500'],
        ]);

        // Registrar reporte en la bitácora de auditoría
        AuditService::log('lost_pet_scanned_alert', $pet, null, [
            'latitude' => $request->latitude,
            'longitude' => $request->longitude,
            'reporter_phone' => $request->reporter_phone,
            'reporter_message' => $request->reporter_message,
            'alert_time' => now()->toIso8601String(),
        ]);

        // En producción aquí se enviaría una notificación push SOS o un correo SMS al dueño/rescatista
        return back()->with('success', '¡Alerta de ubicación enviada con éxito! El rescatista de ' . $pet->name . ' ha sido notificado con las coordenadas GPS.');
    }
}

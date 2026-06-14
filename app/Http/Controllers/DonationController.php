<?php

namespace App\Http\Controllers;

use App\Models\Donation;
use App\Services\AuditService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;
use Inertia\Response;

class DonationController extends Controller
{
    /**
     * Muestra el panel de trazabilidad financiera de donaciones.
     */
    public function showTraceability(): Response
    {
        $user = Auth::user();

        // Obtener historial de donaciones aprobadas
        $donations = Donation::with('user')
            ->where('status', 'aprobado')
            ->orderBy('created_at', 'desc')
            ->get();

        // Calcular métricas
        $totalRaised = $donations->sum('amount');
        $kitsApadrinadosCount = $donations->whereNotNull('kit_id')->count();

        // Simulación de destino de fondos (trazabilidad exacta):
        // 60% Alimentos, 25% Gastos Veterinarios y Medicamentos, 15% Logística y Traslados
        $distribution = [
            'alimentos' => round($totalRaised * 0.60, 2),
            'veterinario' => round($totalRaised * 0.25, 2),
            'logistica' => round($totalRaised * 0.15, 2),
        ];

        // Obtener donaciones particulares del usuario actual
        $myDonations = Donation::where('user_id', $user->id)
            ->orderBy('created_at', 'desc')
            ->get();

        return Inertia::render('Donations/Traceability', [
            'donations' => $donations,
            'totalRaised' => $totalRaised,
            'kitsApadrinadosCount' => $kitsApadrinadosCount,
            'distribution' => $distribution,
            'myDonations' => $myDonations,
        ]);
    }

    /**
     * Procesa la donación y simula la pasarela de pagos de Flow.cl.
     */
    public function initiateDonation(Request $request): RedirectResponse
    {
        $user = Auth::user();

        $request->validate([
            'amount' => ['required', 'numeric', 'min:1000'],
            'kit_id' => ['nullable', 'string'],
        ]);

        // Registrar la donación como aprobada simulando el éxito de Flow.cl
        $donation = Donation::create([
            'user_id' => $user->id,
            'amount' => $request->amount,
            'payment_id' => 'flow_pay_' . md5($user->id . time()),
            'kit_id' => $request->kit_id,
            'status' => 'aprobado',
        ]);

        AuditService::log('make_donation_financial_entry', $donation, null, $donation->toArray());

        // Sumar puntos al donante (Gamificación: 10% del monto donado en puntos, máx 200 pts)
        $pointsEarned = min(200, (int)($request->amount * 0.01));
        $user->increment('points', $pointsEarned);

        return back()->with('success', '¡Donación registrada con éxito! Tu aporte ha sido asignado al fondo de bienestar. Sumaste ' . $pointsEarned . ' Huellas a tu cuenta.');
    }
}

<?php

namespace App\Http\Controllers;

use App\Models\Adoption;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class VaultController extends Controller
{
    /**
     * Muestra la Bóveda de Contratos para administradores y fundaciones.
     */
    public function showVault(Request $request): Response
    {
        $query = Adoption::with(['pet', 'adopter', 'rescuer'])
            ->where('status', 'finalizado');

        // Búsqueda por microchip, email o nombre de la mascota
        if ($request->has('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->whereHas('pet', function ($pq) use ($search) {
                    $pq->where('name', 'like', "%{$search}%")
                       ->orWhere('microchip_code', 'like', "%{$search}%");
                })->orWhereHas('adopter', function ($uq) use ($search) {
                    $uq->where('name', 'like', "%{$search}%")
                       ->orWhere('email', 'like', "%{$search}%");
                });
            });
        }

        $adoptions = $query->orderBy('updated_at', 'desc')->get();

        // Obtener alertas de cláusulas de tenencia responsable vencidas (compromiso de esterilización)
        // Buscamos mascotas adoptadas hace más de 6 meses que no registren "esterilizacion" en su historial clínico.
        $clauseAlerts = [];
        foreach ($adoptions as $adoption) {
            $pet = $adoption->pet;
            $clinicalHistory = $pet->clinical_history ?? [];
            
            $hasSterilization = false;
            foreach ($clinicalHistory as $event) {
                if (isset($event['type']) && $event['type'] === 'esterilizacion') {
                    $hasSterilization = true;
                    break;
                }
            }

            $monthsSinceAdoption = $adoption->created_at->diffInMonths(now());

            if (!$hasSterilization && $monthsSinceAdoption >= 6) {
                $clauseAlerts[] = [
                    'adoption_id' => $adoption->id,
                    'pet_name' => $pet->name,
                    'adopter_name' => $adoption->adopter->name,
                    'adopter_email' => $adoption->adopter->email,
                    'months_elapsed' => $monthsSinceAdoption,
                    'warning' => 'Cláusula de esterilización obligatoria pendiente (más de 6 meses desde la adopción sin registro clínico).',
                ];
            }
        }

        return Inertia::render('Backoffice/Vault', [
            'adoptions' => $adoptions,
            'clauseAlerts' => $clauseAlerts,
            'filters' => $request->only('search'),
        ]);
    }

    /**
     * Muestra el visor de logs de auditoría para administradores.
     */
    public function showAuditLogs(Request $request): Response
    {
        $query = \App\Models\AuditLog::with('user');

        // Búsqueda por acción, ip_address, usuario
        if ($request->has('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('action', 'like', "%{$search}%")
                  ->orWhere('ip_address', 'like', "%{$search}%")
                  ->orWhereHas('user', function ($uq) use ($search) {
                      $uq->where('name', 'like', "%{$search}%")
                         ->orWhere('email', 'like', "%{$search}%");
                  });
            });
        }

        // Filtro por acción específica si se selecciona
        if ($request->filled('action_filter')) {
            $query->where('action', $request->action_filter);
        }

        // Paginar logs de auditoría ordenados desc
        $logs = $query->orderBy('created_at', 'desc')->paginate(30)->withQueryString();

        // Obtener lista de acciones únicas para el selector de filtros
        $actions = \App\Models\AuditLog::select('action')
            ->distinct()
            ->pluck('action');

        return Inertia::render('Backoffice/AuditLogs', [
            'logs' => $logs,
            'actions' => $actions,
            'filters' => $request->only(['search', 'action_filter']),
        ]);
    }
}

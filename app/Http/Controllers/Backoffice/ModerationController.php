<?php

namespace App\Http\Controllers\Backoffice;

use App\Http\Controllers\Controller;
use App\Models\AdoptionDiary;
use App\Services\AuditService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class ModerationController extends Controller
{
    /**
     * Muestra el panel de moderación con las bitácoras pendientes.
     */
    public function index(): Response
    {
        $diaries = AdoptionDiary::where('is_public', true)
            ->where('moderation_status', 'pending')
            ->with(['adoption.pet', 'adoption.adopter'])
            ->orderBy('created_at', 'asc')
            ->get();

        return Inertia::render('Backoffice/Moderation/Index', [
            'diaries' => $diaries,
        ]);
    }

    /**
     * Aprueba la bitácora pública y premia al adoptante con 50 puntos extra.
     */
    public function approve(AdoptionDiary $diary): RedirectResponse
    {
        $oldValues = $diary->toArray();
        $diary->update([
            'moderation_status' => 'approved',
        ]);

        // Premiar al adoptante por compartir la historia de éxito
        $adopter = $diary->adoption->adopter;
        $adopter->increment('points', 50);

        AuditService::log('approve_public_diary', $diary, $oldValues, $diary->getChanges());

        return back()->with('success', 'Bitácora aprobada para el perfil público. Se han otorgado 50 Huellas extra al adoptante.');
    }

    /**
     * Rechaza la bitácora pública.
     */
    public function reject(AdoptionDiary $diary): RedirectResponse
    {
        $oldValues = $diary->toArray();
        $diary->update([
            'moderation_status' => 'rejected',
        ]);

        AuditService::log('reject_public_diary', $diary, $oldValues, $diary->getChanges());

        return back()->with('success', 'Publicación pública rechazada (permanecerá como privada en el diario personal).');
    }
}

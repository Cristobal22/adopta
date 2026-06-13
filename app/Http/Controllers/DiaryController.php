<?php

namespace App\Http\Controllers;

use App\Models\Adoption;
use App\Models\AdoptionDiary;
use App\Services\AIService;
use App\Services\AuditService;
use App\Services\ImageService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Inertia\Inertia;

class DiaryController extends Controller
{
    protected AIService $aiService;
    protected ImageService $imageService;

    public function __construct(AIService $aiService, ImageService $imageService)
    {
        $this->aiService = $aiService;
        $this->imageService = $imageService;
    }

    /**
     * Guarda una nueva entrada del Diario de Adopción y procesa el análisis de IA.
     */
    public function store(Request $request, Adoption $adoption): RedirectResponse
    {
        $user = Auth::user();
        if ($adoption->adopter_id !== $user->id) {
            abort(403);
        }

        $request->validate([
            'emoji_mood' => ['required', 'string', 'max:5'],
            'comment' => ['required', 'string', 'min:5'],
            'photo' => ['nullable', 'image', 'max:5120'], // Máx 5MB
            'is_public' => ['nullable', 'boolean'],
            'photo_consent' => ['nullable', 'boolean'],
        ]);

        if ($request->boolean('is_public') && $request->hasFile('photo')) {
            $request->validate([
                'photo_consent' => ['required', 'accepted'],
            ], [
                'photo_consent.accepted' => 'Debes autorizar expresamente la publicación de la fotografía para poder compartirla públicamente.',
            ]);
        }

        $photoPath = null;
        $aiAbuseFlag = false;

        // 1. Guardar la foto si existe y optimizarla
        if ($request->hasFile('photo')) {
            $file = $request->file('photo');
            $photoPath = $this->imageService->optimizeAndSave($file, 'private/diaries');
            
            // Correr análisis de visión computacional para abuso/maltrato
            $aiAbuseFlag = $this->aiService->scanPhotoForAbuse($photoPath);
        }

        // 2. Correr análisis de sentimiento predictivo en el texto del comentario
        $aiSentimentScore = $this->aiService->analyzeDiarySentiment($request->comment, $request->emoji_mood);

        // 3. Crear el registro en el diario
        $diary = AdoptionDiary::create([
            'adoption_id' => $adoption->id,
            'report_date' => now()->toDateString(),
            'emoji_mood' => $request->emoji_mood,
            'comment' => $request->comment,
            'photo_path' => $photoPath,
            'ai_sentiment_score' => $aiSentimentScore,
            'ai_abuse_flag' => $aiAbuseFlag,
            'is_public' => $request->boolean('is_public', false),
            'moderation_status' => $request->boolean('is_public', false) ? 'pending' : 'approved',
            'photo_consent' => $request->boolean('photo_consent', false),
        ]);

        // Si hay una alerta de maltrato o el sentimiento es extremadamente negativo, auditamos y registramos
        if ($aiAbuseFlag || $aiSentimentScore < -0.4) {
            AuditService::log('ai_alert_triggered', $diary, null, [
                'score' => $aiSentimentScore,
                'abuse_flag' => $aiAbuseFlag,
                'reason' => 'Comportamiento de IA detectó insatisfacción crítica o maltrato visual.',
            ]);
        } else {
            AuditService::log('create_diary_entry', $diary, null, $diary->toArray());
        }

        // Si la puntuación es baja, sugerimos adiestramiento de forma preventiva
        if ($aiSentimentScore < -0.2) {
            return back()->with('success', 'Reporte diario guardado. Notamos que las cosas están siendo un poco difíciles; hemos activado recursos de apoyo.')
                         ->with('alert_sos', true);
        }

        // Incrementar puntos del adoptante por cumplir con su reporte (Gamificación)
        $user->increment('points', 20); // 20 puntos por cada diario enviado a tiempo

        // Evaluar hitos individuales y globales
        $milestoneService = new \App\Services\MilestoneService();
        $milestoneService->checkUserMilestones($user);
        $milestoneService->checkGlobalMilestones();

        return back()->with('success', '¡Gracias por reportar hoy! Tu diario ha sido enviado y sumaste 20 Huellas a tu cuenta.');
    }

    /**
     * Maneja la activación manual del Botón S.O.S para asistencia en comportamiento.
     */
    public function requestSos(Adoption $adoption): RedirectResponse
    {
        $user = Auth::user();
        if ($adoption->adopter_id !== $user->id) {
            abort(403);
        }

        AuditService::log('user_requested_sos', $adoption, null, [
            'reason' => 'Adoptante presionó botón de auxilio SOS por problemas de comportamiento o adaptación.',
        ]);

        // Retornamos guías preventivas inmediatas
        return back()->with('success', 'Alerta S.O.S activada. Una especialista de la Fundación se pondrá en contacto contigo en las próximas horas. Mientras tanto, hemos habilitado guías de adiestramiento en tu panel.')
                     ->with('show_sos_guides', true);
     }

    /**
     * Muestra la foto de un diario de seguimiento si el usuario está autorizado o es pública y aprobada.
     */
    public function showPhoto(Adoption $adoption, AdoptionDiary $diary)
    {
        if ($diary->adoption_id !== $adoption->id) {
            abort(404);
        }

        // Si es público y está aprobado por moderación, cualquiera puede verlo
        $isPublicAndApproved = $diary->is_public && $diary->moderation_status === 'approved';

        if (!$isPublicAndApproved) {
            $user = Auth::user();
            if (!$user) {
                abort(403, 'Debes iniciar sesión para ver esta foto.');
            }
            if ($user->role === 'adoptante' && $adoption->adopter_id !== $user->id) {
                abort(403);
            }
            if (in_array($user->role, ['rescatista', 'fundacion']) && $adoption->rescuer_id !== $user->id && $user->role !== 'admin') {
                abort(403);
            }
        }

        if (!$diary->photo_path || !Storage::disk('local')->exists($diary->photo_path)) {
            abort(404);
        }

        $file = Storage::disk('local')->get($diary->photo_path);
        $type = Storage::disk('local')->mimeType($diary->photo_path);

        return response($file)->header('Content-Type', $type);
    }
}

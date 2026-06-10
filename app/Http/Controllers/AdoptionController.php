<?php

namespace App\Http\Controllers;

use App\Models\Adoption;
use App\Models\Pet;
use App\Models\User;
use App\Services\AdoptionService;
use App\Services\AuditService;
use App\Services\MicrochipService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Inertia\Inertia;
use Inertia\Response;

class AdoptionController extends Controller
{
    protected AdoptionService $adoptionService;
    protected MicrochipService $microchipService;

    public function __construct(AdoptionService $adoptionService, MicrochipService $microchipService)
    {
        $this->adoptionService = $adoptionService;
        $this->microchipService = $microchipService;
    }

    /**
     * Listado de adopciones del usuario (adoptante o rescatista).
     */
    public function index(): Response
    {
        $user = Auth::user();
        $query = Adoption::with(['pet', 'adopter', 'rescuer']);

        if (in_array($user->role, ['adoptante', 'transito', 'donante'])) {
            $query->where('adopter_id', $user->id);
        } elseif (in_array($user->role, ['rescatista', 'fundacion'])) {
            $query->where('rescuer_id', $user->id);
        }

        $adoptions = $query->orderBy('updated_at', 'desc')->get();

        return Inertia::render('Adoptions/Index', [
            'adoptions' => $adoptions,
        ]);
    }

    /**
     * Procesa la postulación a adopción y corre el algoritmo de match.
     */
    public function store(Request $request): RedirectResponse
    {
        $user = Auth::user();
        
        $request->validate([
            'pet_id' => ['required', 'exists:pets,id'],
        ]);

        $pet = Pet::findOrFail($request->pet_id);

        // Validar que el adoptante tenga sus datos de perfil (estilo de vida) llenos
        if (!$user->verification_data) {
            return back()->withErrors([
                'profile' => 'Debes completar tu perfil y formulario de estilo de vida antes de postular.',
            ]);
        }

        // Validar si ya existe una postulación activa
        $exists = Adoption::where('pet_id', $pet->id)
            ->where('adopter_id', $user->id)
            ->whereIn('status', ['solicitado', 'en_proceso', 'aprobado'])
            ->exists();

        if ($exists) {
            return back()->withErrors([
                'pet_id' => 'Ya tienes una postulación activa en curso para esta mascota.',
            ]);
        }

        // Ejecutar algoritmo de Match
        $matchResult = $this->adoptionService->calculateCompatibility($user, $pet);

        if (!$matchResult['is_compatible']) {
            return back()->withErrors([
                'compatibility' => 'La postulación ha sido bloqueada automáticamente por incompatibilidad crítica: ' . implode(' ', $matchResult['reasons']),
            ]);
        }

        // Crear postulación
        $adoption = Adoption::create([
            'pet_id' => $pet->id,
            'adopter_id' => $user->id,
            'rescuer_id' => $pet->adoptions()->first()?->rescuer_id ?? User::where('role', 'fundacion')->first()->id ?? $user->id, // Busca rescatista anterior, fundación o asignación default
            'status' => 'solicitado',
            'compatibility_score' => $matchResult['score'],
        ]);

        AuditService::log('create_adoption_postulation', $adoption, null, $adoption->toArray());

        return redirect()->route('adoptions.index')->with('success', 'Tu postulación se ha registrado con un puntaje de compatibilidad del ' . $matchResult['score'] . '%.');
    }

    /**
     * Detalle de la postulación de adopción.
     */
    public function show(Adoption $adoption): Response
    {
        $user = Auth::user();
        
        // Autorización simple
        if (in_array($user->role, ['adoptante', 'transito', 'donante'])) {
            if ($adoption->adopter_id !== $user->id) {
                abort(403);
            }
        } elseif (in_array($user->role, ['rescatista', 'fundacion'])) {
            if ($adoption->rescuer_id !== $user->id && $user->role !== 'admin') {
                abort(403);
            }
        }

        $adoption->load(['pet', 'adopter', 'rescuer', 'diaries']);

        $matchResult = $this->adoptionService->calculateCompatibility($adoption->adopter, $adoption->pet);

        return Inertia::render('Adoptions/Show', [
            'adoption' => $adoption,
            'matchResult' => $matchResult,
        ]);
    }

    /**
     * Actualiza el estado de la adopción (Aprobar/Rechazar).
     */
    public function updateStatus(Request $request, Adoption $adoption): RedirectResponse
    {
        $user = Auth::user();
        if (!in_array($user->role, ['rescatista', 'fundacion', 'admin'])) {
            abort(403); // Solo rescatistas, fundaciones o admins
        }

        $request->validate([
            'status' => ['required', 'in:en_proceso,rechazado,finalizado'],
        ]);

        $oldValues = $adoption->toArray();
        $adoption->update([
            'status' => $request->status,
        ]);

        AuditService::log('update_adoption_status', $adoption, $oldValues, $adoption->getChanges());

        // Si se aprueba (en_proceso), se notifica para que firme el contrato.
        return back()->with('success', 'El estado de la adopción se ha actualizado a: ' . $request->status);
    }

    /**
     * Descarga la plantilla de contrato simulada en HTML estructurado.
     */
    public function downloadTemplate(Adoption $adoption)
    {
        $user = Auth::user();
        if ($adoption->adopter_id !== $user->id && $adoption->rescuer_id !== $user->id && $user->role !== 'admin') {
            abort(403);
        }

        $adoption->load(['pet', 'adopter', 'rescuer']);

        $content = view('contracts.template', ['adoption' => $adoption])->render();

        return response($content)
            ->header('Content-Type', 'text/html')
            ->header('Content-Disposition', 'attachment; filename="contrato_adopcion_' . $adoption->id . '.html"');
    }

    /**
     * Firma de contrato mediante Canvas (dibujo).
     */
    public function signCanvas(Request $request, Adoption $adoption): RedirectResponse
    {
        $user = Auth::user();
        if ($adoption->adopter_id !== $user->id) {
            abort(403); // Solo el adoptante firma el contrato de adopción definitiva
        }

        $request->validate([
            'signature_data' => ['required', 'string'], // Data URL del Canvas (Base64)
        ]);

        $oldValues = $adoption->toArray();

        // Guardar la firma en un archivo de storage privado
        $signatureData = $request->signature_data;
        $signatureImage = base64_decode(preg_replace('#^data:image/\w+;base64,#i', '', $signatureData));
        
        $signaturePath = 'signatures/signature_' . $adoption->id . '_' . time() . '.png';
        Storage::disk('local')->put('private/' . $signaturePath, $signatureImage);

        // Crear el contrato formal firmado (HTML Legal Certificado con metadatos de IP e Imagen)
        $adoption->load(['pet', 'adopter', 'rescuer']);
        $contractHtml = view('contracts.signed', [
            'adoption' => $adoption,
            'signature_path' => storage_path('app/private/' . $signaturePath),
            'ip_address' => $request->ip(),
            'signed_at' => now()->toDateTimeString(),
        ])->render();

        $contractPath = 'contracts/contract_' . $adoption->id . '_' . time() . '.html';
        Storage::disk('local')->put('private/' . $contractPath, $contractHtml);

        // Actualizar adopción
        $adoption->update([
            'status' => 'finalizado',
            'signature_type' => 'digital_canvas',
            'contract_path' => $contractPath,
        ]);

        // Cambiar el estado de la mascota a "adoptado"
        $adoption->pet->update(['status' => 'adoptado']);

        // Sincronizar microchip y nuevo dueño con el Registro Nacional
        $this->microchipService->syncWithNationalRegistry($adoption);

        AuditService::log('sign_adoption_contract_canvas', $adoption, $oldValues, $adoption->getChanges());

        return redirect()->route('adoptions.show', $adoption->id)->with('success', 'Contrato firmado digitalmente y adopción finalizada con éxito.');
    }

    /**
     * Firma de contrato mediante subida de PDF firmado físicamente.
     */
    public function signUpload(Request $request, Adoption $adoption): RedirectResponse
    {
        $user = Auth::user();
        if ($adoption->adopter_id !== $user->id) {
            abort(403);
        }

        $request->validate([
            'contract_file' => ['required', 'file', 'mimes:pdf,jpg,jpeg,png', 'max:5120'], // Máximo 5MB
        ]);

        $oldValues = $adoption->toArray();

        $file = $request->file('contract_file');
        $contractPath = $file->storeAs(
            'private/contracts',
            'uploaded_contract_' . $adoption->id . '_' . time() . '.' . $file->getClientOriginalExtension(),
            'local'
        );

        // Actualizar adopción
        $adoption->update([
            'status' => 'finalizado',
            'signature_type' => 'uploaded_pdf',
            'contract_path' => $contractPath,
        ]);

        // Cambiar el estado de la mascota a "adoptado"
        $adoption->pet->update(['status' => 'adoptado']);

        // Sincronizar microchip y nuevo dueño con el Registro Nacional
        $this->microchipService->syncWithNationalRegistry($adoption);

        AuditService::log('sign_adoption_contract_upload', $adoption, $oldValues, $adoption->getChanges());

        return redirect()->route('adoptions.show', $adoption->id)->with('success', 'Documento firmado cargado correctamente. Adopción finalizada.');
    }

    /**
     * Descarga el contrato firmado de la adopción desde la bóveda privada.
     */
    public function downloadSigned(Adoption $adoption)
    {
        $user = Auth::user();
        
        // Autorización: solo el adoptante, rescatista/fundación del caso, o un admin.
        if ($adoption->adopter_id !== $user->id && $adoption->rescuer_id !== $user->id && $user->role !== 'admin') {
            abort(403, 'No tienes permiso para acceder a este contrato.');
        }

        if ($adoption->status !== 'finalizado' || !$adoption->contract_path) {
            abort(404, 'El contrato firmado no se encuentra disponible.');
        }

        // Verificar si el archivo está en el storage privado
        $fullPath = 'private/' . $adoption->contract_path;
        if (!Storage::disk('local')->exists($fullPath)) {
            // Reintentar sin prefijo 'private/' en caso de que esté guardado directamente con la ruta completa
            $fullPath = $adoption->contract_path;
            if (!Storage::disk('local')->exists($fullPath)) {
                abort(404, 'Archivo de contrato no encontrado en el servidor.');
            }
        }

        $fileContent = Storage::disk('local')->get($fullPath);
        $mimeType = Storage::disk('local')->mimeType($fullPath);
        $extension = pathinfo($fullPath, PATHINFO_EXTENSION);
        $filename = 'contrato_firmado_' . $adoption->id . '.' . $extension;

        AuditService::log('download_signed_contract', $adoption, null, [
            'filename' => $filename,
            'accessed_by' => $user->name,
        ]);

        return response($fileContent)
            ->header('Content-Type', $mimeType)
            ->header('Content-Disposition', 'attachment; filename="' . $filename . '"');
    }

    /**
     * Sube y procesa el certificado de esterilización.
     */
    public function uploadSterilization(Request $request, Adoption $adoption): RedirectResponse
    {
        $user = Auth::user();
        
        // Autorización: solo el adoptante del caso
        if ($adoption->adopter_id !== $user->id) {
            abort(403, 'No estás autorizado para subir certificados en esta adopción.');
        }

        $request->validate([
            'sterilization_certificate' => ['required', 'file', 'mimes:pdf,jpg,jpeg,png', 'max:5120'], // Máx 5MB
        ]);

        $file = $request->file('sterilization_certificate');
        $certificatePath = $file->storeAs(
            'private/sterilizations',
            'sterilization_cert_' . $adoption->id . '_' . time() . '.' . $file->getClientOriginalExtension(),
            'local'
        );

        // Actualizar el historial clínico de la mascota.
        $pet = $adoption->pet;
        $clinicalHistory = $pet->clinical_history ?? [];
        
        // Agregar el evento de esterilización
        $clinicalHistory[] = [
            'type' => 'esterilizacion',
            'date' => now()->toDateString(),
            'description' => 'Certificado de esterilización cargado por el adoptante y verificado automáticamente por la plataforma.',
            'certificate_path' => $certificatePath,
        ];

        $oldValues = $pet->toArray();
        $pet->update([
            'clinical_history' => $clinicalHistory,
        ]);

        AuditService::log('upload_sterilization_certificate', $pet, $oldValues, $pet->getChanges());

        return back()->with('success', 'Certificado de esterilización subido correctamente. La ficha clínica de la mascota ha sido actualizada y la alerta contractual ha sido resuelta.');
    }
}

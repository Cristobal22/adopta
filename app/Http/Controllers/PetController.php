<?php

namespace App\Http\Controllers;

use App\Models\Pet;
use App\Services\ImageService;
use App\Services\AuditService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class PetController extends Controller
{
    protected ImageService $imageService;

    public function __construct(ImageService $imageService)
    {
        $this->imageService = $imageService;
    }
    /**
     * Muestra el listado de mascotas con filtros.
     */
    public function index(Request $request): Response
    {
        $query = Pet::query();

        // Filtros de búsqueda
        if ($request->has('search') && $request->search != '') {
            $query->where('name', 'like', '%' . $request->search . '%')
                  ->orWhere('breed', 'like', '%' . $request->search . '%')
                  ->orWhere('microchip_code', 'like', '%' . $request->search . '%');
        }

        if ($request->has('species') && $request->species != '') {
            $query->where('species', $request->species);
        }

        if ($request->has('status') && $request->status != '') {
            $query->where('status', $request->status);
        }

        $pets = $query->orderBy('created_at', 'desc')->paginate(10)->withQueryString();

        return Inertia::render('Pets/Index', [
            'pets' => $pets,
            'filters' => $request->only(['search', 'species', 'status']),
        ]);
    }

    /**
     * Muestra el formulario para crear una mascota.
     */
    public function create(): Response
    {
        return Inertia::render('Pets/Form', [
            'pet' => null,
        ]);
    }

    /**
     * Guarda una nueva mascota y registra la auditoría.
     */
    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'species' => ['required', 'in:perro,gato,otro'],
            'breed' => ['nullable', 'string', 'max:255'],
            'age_text' => ['nullable', 'string', 'max:255'],
            'status' => ['required', 'in:rescatado,en_transito,adoptado,en_adopcion'],
            'gender' => ['required', 'in:macho,hembra'],
            'description' => ['nullable', 'string'],
            'latitude' => ['nullable', 'numeric', 'between:-90,90'],
            'longitude' => ['nullable', 'numeric', 'between:-180,180'],
            'clinical_history' => ['nullable', 'array'],
            'microchip_code' => ['nullable', 'string', 'unique:pets,microchip_code', 'max:50'],
            'characteristics' => ['nullable', 'array'],
            'photo' => ['nullable', 'image', 'max:5120'], // Máx 5MB
        ]);

        // Guardar foto si existe
        if ($request->hasFile('photo')) {
            $validated['photo_path'] = $this->imageService->optimizeAndSavePublic($request->file('photo'), 'images/pets');
        }

        // Formateo inicial para campos JSON vacíos
        $validated['clinical_history'] = $validated['clinical_history'] ?? [];
        $validated['characteristics'] = $validated['characteristics'] ?? [];

        // Omitir campo temporal 'photo' de la inserción directa
        unset($validated['photo']);

        $pet = Pet::create($validated);

        // Registro en Bitácora de Auditoría
        AuditService::log('create_pet', $pet, null, $pet->toArray());

        return redirect()->route('pets.index')->with('success', 'Mascota registrada exitosamente.');
    }

    /**
     * Muestra el formulario para editar una mascota.
     */
    public function edit(Pet $pet): Response
    {
        return Inertia::render('Pets/Form', [
            'pet' => $pet,
        ]);
    }

    /**
     * Actualiza la información de la mascota y audita los cambios.
     */
    public function update(Request $request, Pet $pet): RedirectResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'species' => ['required', 'in:perro,gato,otro'],
            'breed' => ['nullable', 'string', 'max:255'],
            'age_text' => ['nullable', 'string', 'max:255'],
            'status' => ['required', 'in:rescatado,en_transito,adoptado,en_adopcion'],
            'gender' => ['required', 'in:macho,hembra'],
            'description' => ['nullable', 'string'],
            'latitude' => ['nullable', 'numeric', 'between:-90,90'],
            'longitude' => ['nullable', 'numeric', 'between:-180,180'],
            'clinical_history' => ['nullable', 'array'],
            'microchip_code' => ['nullable', 'string', 'max:50', 'unique:pets,microchip_code,' . $pet->id],
            'characteristics' => ['nullable', 'array'],
            'photo' => ['nullable', 'image', 'max:5120'], // Máx 5MB
        ]);

        $oldValues = $pet->toArray();

        // Guardar nueva foto si se sube
        if ($request->hasFile('photo')) {
            $newPhotoPath = $this->imageService->optimizeAndSavePublic($request->file('photo'), 'images/pets');
            $validated['photo_path'] = $newPhotoPath;

            // Eliminar foto antigua física si no es una semilla por defecto
            $oldPhotoPath = $pet->photo_path;
            if ($oldPhotoPath && file_exists(public_path($oldPhotoPath))) {
                $seedPhotos = ['images/pets/kira.png', 'images/pets/milo.png', 'images/pets/thor.png'];
                if (!in_array($oldPhotoPath, $seedPhotos)) {
                    @unlink(public_path($oldPhotoPath));
                }
            }
        }

        // Omitir campo temporal 'photo' de la actualización directa
        unset($validated['photo']);

        $pet->update($validated);
        $newValues = $pet->getChanges();

        // Registrar auditoría si hubo algún cambio real
        if (count($newValues) > 0) {
            AuditService::log('update_pet', $pet, $oldValues, $newValues);
        }

        return redirect()->route('pets.index')->with('success', 'Mascota actualizada exitosamente.');
    }

    /**
     * Elimina una mascota y audita la acción.
     */
    public function destroy(Pet $pet): RedirectResponse
    {
        $oldValues = $pet->toArray();
        $pet->delete();

        AuditService::log('delete_pet', $pet, $oldValues, null);

        return redirect()->route('pets.index')->with('success', 'Mascota eliminada del sistema.');
    }
}

<?php

namespace App\Http\Controllers;

use App\Models\ArtRequest;
use App\Models\ArtRequestModification;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Routing\Controller;

class ArtRequestModificationController extends Controller
{
    public function __construct()
    {
        $this->middleware('permission:content.edit')->only(['store']);
        $this->middleware('permission:content.view')->only(['index']);
    }

    /**
     * Mostrar historial de modificaciones
     */
    public function index(ArtRequest $artRequest)
    {
        $modifications = $artRequest->modifications()->paginate(20);
        
        return view('art-requests.modifications.index', compact('artRequest', 'modifications'));
    }

    /**
     * Registrar una nueva modificación
     */
    public function store(Request $request, ArtRequest $artRequest)
    {
        $validated = $request->validate([
            'modification_type' => 'required|in:COLOR,TAMAÑO,TEXTO,CONTENIDO,POSICIÓN,ESTILO,FUENTE,IMAGEN,OTRO',
            'description' => 'required|string|min:5|max:500',
            'old_value' => 'nullable|string|max:255',
            'new_value' => 'nullable|string|max:255',
            'details' => 'nullable|string|max:1000',
        ]);

        // Registrar la modificación
        $modification = $artRequest->recordModification(
            type: $validated['modification_type'],
            description: $validated['description'],
            oldValue: $validated['old_value'] ? ['value' => $validated['old_value']] : null,
            newValue: $validated['new_value'] ? ['value' => $validated['new_value']] : null,
            details: $validated['details'] ? ['description' => $validated['details']] : null
        );

        // Registrar en auditoría
        Log::info('ArtRequest Modification Recorded', [
            'art_request_id' => $artRequest->id,
            'modification_id' => $modification->id,
            'type' => $validated['modification_type'],
            'user_id' => Auth::id(),
        ]);

        return redirect()->back()->with('success', 'Modificación registrada correctamente');
    }

    /**
     * Eliminar una modificación (solo administradores)
     */
    public function destroy(ArtRequest $artRequest, ArtRequestModification $modification)
    {
        $this->authorize('forceDelete', $modification);

        $modification->delete();

        return redirect()->back()->with('success', 'Modificación eliminada correctamente');
    }

    /**
     * Obtener resumen de modificaciones (JSON para AJAX)
     */
    public function summary(ArtRequest $artRequest)
    {
        return response()->json([
            'total' => $artRequest->getTotalModifications(),
            'by_type' => $artRequest->getModificationsSummary(),
            'recent' => $artRequest->modifications()->take(5)->get()->map(function ($mod) {
                return [
                    'id' => $mod->id,
                    'type' => $mod->modification_type,
                    'description' => $mod->description,
                    'created_by' => $mod->creator->name ?? 'Sistema',
                    'created_at' => $mod->created_at->format('d/m/Y H:i'),
                ];
            }),
        ]);
    }

    /**
     * Marcar modificación como completada/no completada
     */
    public function toggle(ArtRequest $artRequest, ArtRequestModification $modification)
    {
        // Obtener el valor del request y convertirlo a booleano
        $isCompleted = filter_var(request()->input('is_completed'), FILTER_VALIDATE_BOOLEAN);

        $modification->update([
            'is_completed' => $isCompleted,
        ]);

        Log::info('ArtRequest Modification Toggled', [
            'art_request_id' => $artRequest->id,
            'modification_id' => $modification->id,
            'is_completed' => $modification->is_completed,
            'user_id' => Auth::id(),
        ]);

        // Si es una petición AJAX, devolver JSON
        if (request()->expectsJson()) {
            return response()->json([
                'success' => true,
                'is_completed' => (bool) $modification->is_completed,
                'message' => 'Modificación actualizada correctamente'
            ]);
        }

        return redirect()->back()->with('success', 'Estado de la modificación actualizado');
    }
}

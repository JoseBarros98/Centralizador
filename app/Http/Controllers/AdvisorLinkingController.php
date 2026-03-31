<?php

namespace App\Http\Controllers;

use App\Services\AdvisorLinkingService;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class AdvisorLinkingController extends Controller
{
    protected $advisorService;

    public function __construct(AdvisorLinkingService $advisorService)
    {
        $this->advisorService = $advisorService;
    }

    /**
     * Mostrar la interfaz de vinculación de asesores
     */
    public function index(Request $request)
    {
        $search = trim((string) $request->query('search', ''));
        $perPage = (int) $request->query('per_page', 15);
        if (!in_array($perPage, [10, 15, 25, 50], true)) {
            $perPage = 15;
        }

        // Obtener asesores sin vincular con búsqueda y paginación
        $unlinkedAdvisors = $this->advisorService->getUnlinkedAdvisors($search, $perPage);
        $unlinkedAdvisors->withQueryString();

        // Obtener asesores ya vinculados para permitir cambiar usuario vinculado
        $linkedAdvisors = $this->advisorService->getLinkedAdvisors($search, $perPage);
        $linkedAdvisors->withQueryString();
        
        // Obtener estadísticas
        $stats = $this->advisorService->getLinkingStats();
        
        // Obtener todos los usuarios del sistema (potenciales asesores)
        $users = User::where('email', '!=', 'sistema.externo@centtest.local')
            ->orderBy('name')
            ->get();
        
        return view('advisors.link', compact('unlinkedAdvisors', 'linkedAdvisors', 'stats', 'users', 'search', 'perPage'));
    }

    /**
     * Vincular un asesor externo con un usuario del sistema
     */
    public function linkAdvisor(Request $request)
    {
        $request->validate([
            'external_advisor_id' => 'required',
            'user_id' => 'required|exists:users,id'
        ]);

        try {
            $result = $this->advisorService->linkAdvisorToUser(
                $request->external_advisor_id,
                $request->user_id
            );

            if ($result['success']) {
                return response()->json([
                    'success' => true,
                    'message' => $result['message'],
                    'updated' => $result['updated']
                ]);
            } else {
                return response()->json([
                    'success' => false,
                    'message' => $result['message']
                ], 400);
            }
        } catch (\Exception $e) {
            Log::error('Error vinculando asesor: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Error al vincular asesor: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Ejecutar vinculación automática por nombre
     */
    public function autoLink()
    {
        try {
            $result = $this->advisorService->autoLinkAdvisorsByName();

            if ($result['success']) {
                return response()->json([
                    'success' => true,
                    'message' => $result['message'],
                    'linked' => $result['linked'],
                    'errors' => $result['errors']
                ]);
            } else {
                return response()->json([
                    'success' => false,
                    'message' => $result['message']
                ], 400);
            }
        } catch (\Exception $e) {
            Log::error('Error en auto-vinculación: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Error en auto-vinculación: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Obtener estadísticas actualizadas
     */
    public function getStats()
    {
        $stats = $this->advisorService->getLinkingStats();
        return response()->json($stats);
    }
}

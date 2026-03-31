<?php

namespace App\Services;

use App\Models\Inscription;
use App\Models\User;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Auth;

class AdvisorLinkingService
{
    /**
     * Vincular inscripciones de un asesor externo con su cuenta real del sistema
     * 
     * @param int $externalAdvisorId ID del asesor en el sistema externo
     * @param int $userId ID del usuario real en el sistema local
     * @return array
     */
    public function linkAdvisorToUser($externalAdvisorId, $userId)
    {
        try {
            // Verificar que el usuario existe
            $user = User::findOrFail($userId);
            
            // Buscar todas las inscripciones con ese external_advisor_id
            $inscriptions = Inscription::where('external_advisor_id', $externalAdvisorId)->get();
            
            if ($inscriptions->isEmpty()) {
                return [
                    'success' => false,
                    'message' => 'No se encontraron inscripciones para el asesor externo con ID: ' . $externalAdvisorId
                ];
            }
            
            // Actualizar el created_by de todas las inscripciones
            $updated = 0;
            $currentUserId = Auth::check() ? Auth::user()->id : $userId;
            
            foreach ($inscriptions as $inscription) {
                $inscription->update([
                    'created_by' => $userId,
                    'updated_by' => $currentUserId
                ]);
                $updated++;
            }
            
            Log::info("Vinculadas {$updated} inscripciones del asesor externo {$externalAdvisorId} al usuario {$user->name}");
            
            return [
                'success' => true,
                'updated' => $updated,
                'advisor_name' => $user->name,
                'message' => "Se vincularon exitosamente {$updated} inscripciones al usuario {$user->name}"
            ];
            
        } catch (\Exception $e) {
            Log::error('Error al vincular asesor: ' . $e->getMessage());
            return [
                'success' => false,
                'message' => 'Error al vincular asesor: ' . $e->getMessage()
            ];
        }
    }
    
    /**
     * Vincular automáticamente asesores por nombre coincidente
     * 
     * @return array
     */
    public function autoLinkAdvisorsByName()
    {
        try {
            $linked = 0;
            $errors = 0;
            
            // Obtener inscripciones con asesor externo
            $inscriptions = Inscription::whereNotNull('external_advisor_name')
                ->whereNotNull('external_advisor_id')
                ->get();
            
            $processedAdvisors = [];
            
            foreach ($inscriptions as $inscription) {
                // Si ya procesamos este asesor externo, saltar
                if (isset($processedAdvisors[$inscription->external_advisor_id])) {
                    continue;
                }
                
                // Buscar usuario con nombre similar
                $user = $this->findUserByName($inscription->external_advisor_name);
                
                if ($user) {
                    // Vincular todas las inscripciones de este asesor
                    $result = $this->linkAdvisorToUser($inscription->external_advisor_id, $user->id);
                    
                    if ($result['success']) {
                        $linked += $result['updated'];
                        $processedAdvisors[$inscription->external_advisor_id] = $user->id;
                    } else {
                        $errors++;
                    }
                }
            }
            
            return [
                'success' => true,
                'linked' => $linked,
                'errors' => $errors,
                'message' => "Se vincularon automáticamente {$linked} inscripciones"
            ];
            
        } catch (\Exception $e) {
            Log::error('Error en auto-vinculación: ' . $e->getMessage());
            return [
                'success' => false,
                'message' => 'Error en auto-vinculación: ' . $e->getMessage()
            ];
        }
    }
    
    /**
     * Buscar usuario por nombre (con normalización)
     * 
     * @param string $name
     * @return User|null
     */
    protected function findUserByName($name)
    {
        // Normalizar nombre (quitar acentos, convertir a minúsculas)
        $normalizedName = $this->normalizeName($name);
        
        // Buscar usuarios
        $users = User::all();
        
        foreach ($users as $user) {
            $normalizedUserName = $this->normalizeName($user->name);
            
            // Comparar nombres normalizados
            if ($normalizedUserName === $normalizedName) {
                return $user;
            }
            
            // Comparar con similitud (más del 80% de coincidencia)
            similar_text($normalizedName, $normalizedUserName, $percent);
            if ($percent > 80) {
                return $user;
            }
        }
        
        return null;
    }
    
    /**
     * Normalizar nombre (quitar acentos, minúsculas, etc.)
     * 
     * @param string $name
     * @return string
     */
    protected function normalizeName($name)
    {
        // Convertir a minúsculas
        $name = strtolower($name);
        
        // Quitar acentos
        $name = iconv('UTF-8', 'ASCII//TRANSLIT', $name);
        
        // Quitar espacios múltiples
        $name = preg_replace('/\s+/', ' ', $name);
        
        // Trim
        $name = trim($name);
        
        return $name;
    }
    
    /**
     * Obtener lista de asesores externos sin vincular
     * 
        * @return LengthAwarePaginator
     */
        public function getUnlinkedAdvisors(string $search = '', int $perPage = 15): LengthAwarePaginator
    {
        // Obtener usuario "Sistema Externo"
        $externalSystemUser = User::where('email', 'sistema.externo@centtest.local')->first();
        
        if (!$externalSystemUser) {
            return new LengthAwarePaginator([], 0, $perPage);
        }
        
        // Obtener inscripciones del usuario "Sistema Externo"
        $query = Inscription::where('created_by', $externalSystemUser->id)
            ->whereNotNull('external_advisor_id')
            ->whereNotNull('external_advisor_name')
            ->select('external_advisor_id', 'external_advisor_name', DB::raw('COUNT(*) as inscriptions_count'));

        if ($search !== '') {
            $query->where(function ($q) use ($search) {
                $q->where('external_advisor_name', 'like', '%' . $search . '%')
                    ->orWhere('external_advisor_id', 'like', '%' . $search . '%');
            });
        }

        $unlinked = $query
            ->groupBy('external_advisor_id', 'external_advisor_name')
            ->orderBy('external_advisor_name')
            ->paginate($perPage);
        
        $unlinked->getCollection()->transform(function ($item) {
            return [
                'external_id' => $item->external_advisor_id,
                'name' => $item->external_advisor_name,
                'inscriptions_count' => $item->inscriptions_count,
                'suggested_user' => $this->findUserByName($item->external_advisor_name)
            ];
        });

        return $unlinked;
    }

    /**
     * Obtener lista de asesores externos ya vinculados
     *
     * @return LengthAwarePaginator
     */
    public function getLinkedAdvisors(string $search = '', int $perPage = 15): LengthAwarePaginator
    {
        $externalSystemUser = User::where('email', 'sistema.externo@centtest.local')->first();

        $query = Inscription::whereNotNull('external_advisor_id')
            ->whereNotNull('external_advisor_name')
            ->select(
                'external_advisor_id',
                'external_advisor_name',
                DB::raw('MAX(created_by) as user_id'),
                DB::raw('COUNT(*) as inscriptions_count')
            );

        if ($externalSystemUser) {
            $query->where('created_by', '!=', $externalSystemUser->id);
        }

        if ($search !== '') {
            $query->where(function ($q) use ($search) {
                $q->where('external_advisor_name', 'like', '%' . $search . '%')
                    ->orWhere('external_advisor_id', 'like', '%' . $search . '%');
            });
        }

        $linked = $query
            ->groupBy('external_advisor_id', 'external_advisor_name')
            ->orderBy('external_advisor_name')
            ->paginate($perPage, ['*'], 'linked_page');

        $linked->getCollection()->transform(function ($item) {
            return [
                'external_id' => $item->external_advisor_id,
                'name' => $item->external_advisor_name,
                'inscriptions_count' => $item->inscriptions_count,
                'user_id' => $item->user_id,
                'current_user' => User::find($item->user_id),
            ];
        });

        return $linked;
    }
    
    /**
     * Obtener estadísticas de vinculación
     * 
     * @return array
     */
    public function getLinkingStats()
    {
        $externalSystemUser = User::where('email', 'sistema.externo@centtest.local')->first();
        
        if (!$externalSystemUser) {
            return [
                'total' => 0,
                'linked' => 0,
                'unlinked' => 0
            ];
        }
        
        $total = Inscription::whereNotNull('external_id')->count();
        $unlinked = Inscription::where('created_by', $externalSystemUser->id)->count();
        $linked = $total - $unlinked;
        
        return [
            'total' => $total,
            'linked' => $linked,
            'unlinked' => $unlinked,
            'percentage_linked' => $total > 0 ? round(($linked / $total) * 100, 2) : 0
        ];
    }
}

<?php

namespace App\Services;

use App\Models\Inscription;
use App\Models\User;
use App\Models\Profession;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class InscriptionSyncService
{
    protected $externalConnection = 'mysql_external';
    protected $externalSystemUser;

    public function __construct()
    {
        // Obtener el usuario especial "Sistema Externo"
        $this->externalSystemUser = User::where('email', 'sistema.externo@centtest.local')->first();
        
        if (!$this->externalSystemUser) {
            throw new \Exception('Usuario "Sistema Externo" no encontrado. Ejecute el seeder ExternalSystemUserSeeder.');
        }
    }

    /**
     * Sincronizar todas las inscripciones desde la base de datos externa
     */
    public function syncAll($programId = null)
    {
        try {
            $externalInscriptions = $this->fetchExternalInscriptions($programId);
            $synced = 0;
            $errors = 0;

            foreach ($externalInscriptions as $extInscription) {
                try {
                    $this->syncInscription($extInscription);
                    $synced++;
                } catch (\Exception $e) {
                    $errors++;
                    Log::error("Error sincronizando inscripción ID {$extInscription->id_estudiante}: " . $e->getMessage());
                }
            }

            return [
                'success' => true,
                'synced' => $synced,
                'errors' => $errors,
                'total' => count($externalInscriptions)
            ];
        } catch (\Exception $e) {
            Log::error('Error en sincronización de inscripciones: ' . $e->getMessage());
            return [
                'success' => false,
                'message' => $e->getMessage()
            ];
        }
    }

    /**
     * Sincronizar inscripciones de un programa específico desde la base de datos externa
     */
    public function syncByProgram($programId)
    {
        return $this->syncAll($programId);
    }

    /**
     * Obtener inscripciones de la base de datos externa usando procedimiento almacenado
     */
    protected function fetchExternalInscriptions($programId = null)
    {
        if ($programId) {
            // Llamar al procedimiento almacenado para un programa específico
            $inscriptions = DB::connection($this->externalConnection)
                ->select('CALL sp_obtener_inscripciones_programa(?)', [$programId]);
            
            return $inscriptions;
        } else {
            // Si no se especifica programa, obtener de todos los programas
            $allInscriptions = [];
            
            // Obtener todos los programas con code (id_programa externo)
            $programs = \App\Models\Program::whereNotNull('code')->get();
            
            foreach ($programs as $program) {
                try {
                    $inscriptions = DB::connection($this->externalConnection)
                        ->select('CALL sp_obtener_inscripciones_programa(?)', [$program->code]);
                    
                    $allInscriptions = array_merge($allInscriptions, $inscriptions);
                } catch (\Exception $e) {
                    Log::warning("Error obteniendo inscripciones del programa {$program->name} (ID externo: {$program->code}): " . $e->getMessage());
                }
            }
            
            return $allInscriptions;
        }
    }

    /**
     * Sincronizar una inscripción individual
     * 
     * IMPORTANTE: Busca por external_id + external_program_id para permitir que un estudiante
     * tenga múltiples inscripciones con diferentes asesores en diferentes programas
     */
    protected function syncInscription($extInscription)
    {
        // Mapear profesión
        $professionId = $this->mapProfession($extInscription->profesion_estudiante);
        
        // Mapear programa usando el id_programa externo
        $programId = $this->mapProgram($extInscription->id_programa);
        $externalProgramId = $extInscription->id_programa ?? null;

        // PASO 1: Buscar por external_id + external_program_id (combinación)
        // Esto permite que el mismo estudiante tenga múltiples inscripciones en diferentes programas
        $inscription = Inscription::where('external_id', $extInscription->id_estudiante)
                                  ->where('external_program_id', $externalProgramId)
                                  ->first();

        // Datos mapeados desde la DB externa
        $data = [
            'external_id' => $extInscription->id_estudiante,
            'code' => $extInscription->code ?? Inscription::generateCode(
                $extInscription->nombre_completo_estudiante,
                $extInscription->nro_ci_estudiante
            ),
            'full_name' => $extInscription->nombre_completo_estudiante,
            'ci' => $extInscription->nro_ci_estudiante,
            'birth_date' => $extInscription->fecha_nacimiento_estudiante,
            'phone' => $extInscription->telefono_estudiante ?? 'N/A',
            'email' => $extInscription->email_estudiante,
            'profession_id' => $professionId,
            'inscription_date' => $extInscription->fecha_inscripcion,
            'payment_plan' => $extInscription->plan_pago,
            
            // Estados de la DB externa
            'external_inscription_status' => $extInscription->estado_inscripcion_estudiante,
            'external_academic_status' => $extInscription->estado_academico ?? null,
            'external_degree_status' => $extInscription->estado_titulacion ?? null,
            'external_university_enrolled' => (bool)($extInscription->inscrito_universidad ?? false),
            'external_preregistration_date' => $extInscription->fecha_registro_preinscrito ?? null,
            
            // Información del asesor externo
            'external_advisor_id' => $extInscription->idasesor,
            'external_advisor_name' => $extInscription->nombre_completo_asesor ?? null,
            
            // ID del programa externo
            'external_program_id' => $externalProgramId,
            
            // Control de sincronización
            'is_synced' => true,
            'last_synced_at' => now(),
        ];

        if ($inscription) {
            // PASO 2: Si ya existe inscripción para este programa, ACTUALIZAR pero PRESERVAR created_by
            // Solo actualizamos los campos sincronizados de la BD externa
            // PRESERVAMOS el asesor original (created_by) para no perder histórico
            $createdByOriginal = $inscription->created_by;
            
            $inscription->update($data);
            
            // Restaurar el asesor original si es diferente
            if ($createdByOriginal !== $inscription->created_by) {
                $inscription->created_by = $createdByOriginal;
                $inscription->save();
                
                Log::info("Sincronización preservó asesor original", [
                    'inscription_id' => $inscription->id,
                    'external_id' => $extInscription->id_estudiante,
                    'program_id' => $externalProgramId,
                    'advisor_name' => $extInscription->nombre_completo_asesor
                ]);
            }
            
            // Sincronizar la relación con el programa usando la tabla pivot
            if ($programId) {
                if (!$inscription->programs()->where('program_id', $programId)->exists()) {
                    $inscription->programs()->attach($programId);
                }
            }
            
            Log::info("Inscripción sincronizada (existente)", [
                'inscription_id' => $inscription->id,
                'student' => $extInscription->nombre_completo_estudiante,
                'program' => $externalProgramId
            ]);
        } else {
            // PASO 3: Si NO existe, crear nueva inscripción
            // Esta es una NUEVA inscripción del estudiante a un NUEVO programa
            
            // Verificar si existe una inscripción con el mismo email+CI para este programa
            if ($extInscription->email_estudiante) {
                $existingByEmail = Inscription::where('email', $extInscription->email_estudiante)
                    ->where('ci', $extInscription->nro_ci_estudiante)
                    ->where('external_program_id', $externalProgramId)
                    ->first();
                
                if ($existingByEmail) {
                    // Actualizar con external_id y preservar created_by
                    $createdByOriginal = $existingByEmail->created_by;
                    $existingByEmail->update($data);
                    $existingByEmail->created_by = $createdByOriginal;
                    $existingByEmail->save();
                    
                    return $existingByEmail;
                }
            }
            
            // Asignar el nuevo asesor como creador
            // Si viene del sistema externo, usar sistema.externo; si no, usar el asesor del registro
            $newAdvisor = $this->findOrCreateAdvisor($extInscription);
            $data['created_by'] = $newAdvisor ? $newAdvisor->id : $this->externalSystemUser->id;
            
            // Inicializar campos locales con valores por defecto
            $data['status'] = 'Completando';
            $data['local_payment_status'] = 'Pendiente';
            
            $inscription = Inscription::create($data);
            
            // Asociar con el programa usando la tabla pivot
            if ($programId) {
                $inscription->programs()->attach($programId);
            }
            
            Log::info("Nueva inscripción creada para estudiante en programa", [
                'inscription_id' => $inscription->id,
                'student' => $extInscription->nombre_completo_estudiante,
                'external_program_id' => $externalProgramId,
                'advisor' => $extInscription->nombre_completo_asesor
            ]);
        }

        return $inscription;
    }

    /**
     * Buscar o crear usuario asesor por nombre desde la BD externa
     * No modifica el sistema si no encuentra, solo retorna null
     */
    protected function findOrCreateAdvisor($extInscription)
    {
        if (!$extInscription->nombre_completo_asesor) {
            return null;
        }

        // Intentar encontrar usuario por nombre
        $advisor = User::where('name', $extInscription->nombre_completo_asesor)
                      ->first();

        return $advisor;
    }
    
    /**
     * Mapear programa de la DB externa a la local usando el código
     */
    protected function mapProgram($externalProgramId)
    {
        if (!$externalProgramId) {
            return null;
        }

        // Buscar programa por código (que almacena el id_programa externo)
        $program = \App\Models\Program::where('code', $externalProgramId)->first();

        if (!$program) {
            Log::warning("Programa con código externo {$externalProgramId} no encontrado");
            return null;
        }

        return $program->id;
    }

    /**
     * Mapear profesión de la DB externa a la local
     */
    protected function mapProfession($externalProfessionName)
    {
        if (!$externalProfessionName) {
            return null;
        }

        // Buscar profesión por nombre similar
        $profession = Profession::where('name', 'LIKE', '%' . $externalProfessionName . '%')
            ->orWhere('name', 'LIKE', '%' . strtolower($externalProfessionName) . '%')
            ->first();

        if (!$profession) {
            // Si no existe, crear la profesión
            $profession = Profession::create([
                'name' => $externalProfessionName,
                'description' => 'Sincronizado desde sistema externo'
            ]);
        }

        return $profession->id;
    }

    /**
     * Obtener inscripciones que necesitan sincronización
     */
    public function getUnsyncedInscriptions()
    {
        return Inscription::where('is_synced', false)
            ->orWhere(function ($query) {
                $query->where('is_synced', true)
                    ->where('last_synced_at', '<', now()->subHours(24)); // No sincronizadas en últimas 24 horas
            })
            ->get();
    }

    /**
     * Verificar conexión con DB externa
     */
    public function testConnection()
    {
        try {
            DB::connection($this->externalConnection)->getPdo();
            return true;
        } catch (\Exception $e) {
            return false;
        }
    }
}

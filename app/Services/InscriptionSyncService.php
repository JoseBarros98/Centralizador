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
     */
    protected function syncInscription($extInscription)
    {
        // Buscar si ya existe la inscripción por external_id
        $inscription = Inscription::where('external_id', $extInscription->id_estudiante)->first();

        // Mapear profesión
        $professionId = $this->mapProfession($extInscription->profesion_estudiante);
        
        // Mapear programa usando el id_programa externo
        $programId = $this->mapProgram($extInscription->id_programa);

        // Datos mapeados desde la DB externa (sin program_id, se manejará con la tabla pivot)
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
            'payment_plan' => $extInscription->plan_pago, // Ya es string, se guarda tal cual
            
            // Estados de la DB externa
            'external_inscription_status' => $extInscription->estado_inscripcion_estudiante,
            'external_academic_status' => $extInscription->estado_academico ?? null,
            'external_degree_status' => $extInscription->estado_titulacion ?? null,
            'external_university_enrolled' => (bool)($extInscription->inscrito_universidad ?? false),
            'external_preregistration_date' => $extInscription->fecha_registro_preinscrito ?? null,
            
            // Información del asesor externo
            'external_advisor_id' => $extInscription->idasesor,
            'external_advisor_name' => $extInscription->nombre_completo_asesor ?? null,
            
            // ID del programa externo (ahora viene directamente de la DB externa)
            'external_program_id' => $extInscription->id_programa ?? null,
            
            // Control de sincronización
            'is_synced' => true,
            'last_synced_at' => now(),
        ];

        if ($inscription) {
            // Actualizar inscripción existente
            // Solo actualizamos los campos sincronizados, no los campos locales
            $inscription->update($data);
            
            // Sincronizar la relación con el programa usando la tabla pivot
            if ($programId) {
                // Si ya está asociado a este programa, no hacer nada
                // Si no está asociado, agregarlo
                if (!$inscription->programs()->where('program_id', $programId)->exists()) {
                    $inscription->programs()->attach($programId);
                }
            }
        } else {
            // Verificar si existe una inscripción con el mismo email (para evitar duplicados)
            if ($extInscription->email_estudiante) {
                $existingByEmail = Inscription::where('email', $extInscription->email_estudiante)
                    ->where('ci', $extInscription->nro_ci_estudiante)
                    ->first();
                
                if ($existingByEmail) {
                    // Si existe con el mismo email y CI, actualizar su external_id y datos
                    $existingByEmail->update($data);
                    return $existingByEmail;
                }
            }
            
            // Crear nueva inscripción
            // Asignar el usuario "Sistema Externo" como creador
            $data['created_by'] = $this->externalSystemUser->id;
            
            // Inicializar campos locales con valores por defecto
            $data['status'] = 'Completando'; // Estado por defecto (para compatibilidad)
            $data['local_payment_status'] = 'Pendiente'; // Estado de pago local por defecto
            
            $inscription = Inscription::create($data);
            
            // Asociar con el programa usando la tabla pivot
            if ($programId) {
                $inscription->programs()->attach($programId);
            }
        }

        return $inscription;
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

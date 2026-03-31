<?php

namespace App\Services;

use App\Models\Module;
use App\Models\Program;
use App\Models\External\ExternalModule;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class ModuleSyncService
{
    protected $teacherSyncService;

    public function __construct(TeacherSyncService $teacherSyncService)
    {
        $this->teacherSyncService = $teacherSyncService;
    }
    /**
     * Sincroniza todos los módulos de todos los programas
     */
    public function syncAll(): array
    {
        $stats = [
            'programs_processed' => 0,
            'modules_created' => 0,
            'modules_updated' => 0,
            'modules_deleted' => 0,
            'errors' => 0,
            'total_modules' => 0,
        ];

        try {
            // Obtener todos los programas locales
            $programs = Program::all();
            $stats['programs_processed'] = $programs->count();

            Log::info("Iniciando sincronización de módulos para {$stats['programs_processed']} programas");

            foreach ($programs as $program) {
                try {
                    $this->syncModulesByProgram($program, $stats);
                } catch (\Exception $e) {
                    $stats['errors']++;
                    Log::error("Error sincronizando módulos del programa {$program->code}: {$e->getMessage()}");
                }
            }

            Log::info("Sincronización de módulos completada", $stats);

        } catch (\Exception $e) {
            Log::error("Error en la sincronización de módulos: {$e->getMessage()}");
            throw $e;
        }

        return $stats;
    }

    /**
     * Sincroniza los módulos de un programa específico
     */
    public function syncModulesByProgram(Program $program, array &$stats): void
    {
        try {
            // Obtener módulos externos usando el stored procedure
            $externalModules = ExternalModule::getModulesByProgram($program->code);
            
            if ($externalModules->isEmpty()) {
                Log::info("No se encontraron módulos externos para el programa {$program->code}");
                return;
            }

            $stats['total_modules'] += $externalModules->count();

            // Obtener módulos locales actuales del programa
            $currentLocalModules = Module::where('program_id', $program->id)
                ->get()
                ->keyBy(function ($module) use ($program) {
                    // Generar clave única basada en nombre del módulo
                    return $this->generateModuleKey($module->name, $program->code);
                });

            $processedKeys = [];

            // Procesar cada módulo externo
            foreach ($externalModules as $externalModule) {
                $moduleKey = $this->generateModuleKey(
                    $externalModule->nombre_modulo ?? '', 
                    $program->code
                );
                $processedKeys[] = $moduleKey;

                $localData = ExternalModule::toLocalFormat($externalModule, $program->id);

                // Sincronizar docente si hay external_teacher_id
                $teacherId = null;
                if (!empty($localData['external_teacher_id'])) {
                    $teacher = $this->teacherSyncService->syncTeacher(
                        $localData['external_teacher_id'],
                        $localData['teacher_name']
                    );
                    
                    if ($teacher) {
                        $teacherId = $teacher->id;
                    }
                }

                // Remover external_teacher_id del array (no existe en la tabla modules)
                unset($localData['external_teacher_id']);

                if ($currentLocalModules->has($moduleKey)) {
                    // Actualizar módulo existente
                    $localModule = $currentLocalModules->get($moduleKey);
                    
                    if ($this->hasChanges($localModule, $localData)) {
                        // Preservar campos editables localmente solo si no fueron editados
                        // Si teacher_id local es null o es externo, actualizar con el nuevo
                        if ($teacherId && ($localModule->teacher_id === null || $localModule->teacher->is_external ?? false)) {
                            $localData['teacher_id'] = $teacherId;
                        } else {
                            $localData['teacher_id'] = $localModule->teacher_id;
                        }
                        
                        $localData['monitor_id'] = $localModule->monitor_id;
                        $localData['recovery_start_date'] = $localModule->recovery_start_date;
                        $localData['recovery_end_date'] = $localModule->recovery_end_date;
                        $localData['recovery_notes'] = $localModule->recovery_notes;
                        $localData['teacher_rating'] = $localModule->teacher_rating;

                        $localModule->update($localData);
                        $stats['modules_updated']++;
                        Log::info("Módulo actualizado: {$localData['name']} del programa {$program->code}");
                    }
                } else {
                    // Crear nuevo módulo con teacher_id si está disponible
                    if ($teacherId) {
                        $localData['teacher_id'] = $teacherId;
                    }
                    
                    Module::create($localData);
                    $stats['modules_created']++;
                    Log::info("Módulo creado: {$localData['name']} del programa {$program->code}");
                }
            }

            // Eliminar módulos que ya no existen en la BD externa
            // (opcional, comentado por seguridad - descomentar si se desea)
            /*
            foreach ($currentLocalModules as $key => $localModule) {
                if (!in_array($key, $processedKeys)) {
                    $localModule->delete();
                    $stats['modules_deleted']++;
                    Log::info("Módulo eliminado: {$localModule->name} del programa {$program->code}");
                }
            }
            */

        } catch (\Exception $e) {
            Log::error("Error sincronizando módulos del programa {$program->code}: {$e->getMessage()}");
            throw $e;
        }
    }

    /**
     * Sincroniza módulos de un programa específico por código
     */
    public function syncByProgramCode(string $programCode): bool
    {
        try {
            $program = Program::where('code', $programCode)->first();

            if (!$program) {
                Log::warning("Programa no encontrado localmente: {$programCode}");
                return false;
            }

            $stats = [
                'programs_processed' => 0,
                'modules_created' => 0,
                'modules_updated' => 0,
                'modules_deleted' => 0,
                'errors' => 0,
                'total_modules' => 0,
            ];

            $this->syncModulesByProgram($program, $stats);

            Log::info("Sincronización completada para programa {$programCode}", $stats);

            return true;
        } catch (\Exception $e) {
            Log::error("Error sincronizando módulos del programa {$programCode}: {$e->getMessage()}");
            return false;
        }
    }

    /**
     * Verifica si hay cambios entre el módulo local y los datos externos
     */
    protected function hasChanges(Module $localModule, array $externalData): bool
    {
        $fieldsToCheck = [
            'name',
            'status',
            'teacher_name',
        ];

        foreach ($fieldsToCheck as $field) {
            $localValue = $localModule->$field;
            $externalValue = $externalData[$field] ?? null;

            if ($localValue != $externalValue) {
                return true;
            }
        }

        // Verificar fechas
        if ($localModule->start_date?->format('Y-m-d') != ($externalData['start_date'] ?? null)) {
            return true;
        }

        if ($localModule->finalization_date?->format('Y-m-d') != ($externalData['finalization_date'] ?? null)) {
            return true;
        }

        return false;
    }

    /**
     * Genera una clave única para identificar un módulo
     */
    protected function generateModuleKey(string $moduleName, string $programCode): string
    {
        return md5($programCode . '_' . $moduleName);
    }

    /**
     * Obtiene estadísticas de la sincronización
     */
    public function getStats(): array
    {
        return [
            'local_modules_count' => Module::count(),
            'local_programs_count' => Program::count(),
            'modules_by_program' => Module::select('program_id', DB::raw('count(*) as total'))
                ->groupBy('program_id')
                ->with('program:id,code,name')
                ->get()
                ->map(function ($item) {
                    return [
                        'program_code' => $item->program->code ?? 'N/A',
                        'program_name' => $item->program->name ?? 'N/A',
                        'modules_count' => $item->total,
                    ];
                }),
            'last_sync' => Module::max('updated_at'),
        ];
    }
}

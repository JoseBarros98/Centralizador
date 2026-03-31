<?php

namespace App\Models\External;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class ExternalModule extends Model
{
    /**
     * Conexión a la base de datos externa
     */
    protected $connection = 'mysql_external';

    /**
     * No hay tabla asociada directamente, usamos stored procedure
     */
    protected $table = null;

    /**
     * No tiene timestamps
     */
    public $timestamps = false;

    /**
     * No se puede insertar o actualizar
     */
    protected $guarded = ['*'];

    /**
     * Obtiene los módulos de un programa específico usando el stored procedure
     * 
     * @param string $programCode Código del programa (id_programa)
     * @return \Illuminate\Support\Collection
     */
    public static function getModulesByProgram(string $programCode)
    {
        try {
            // Ejecutar el stored procedure
            $results = DB::connection('mysql_external')
                ->select('CALL sp_obtener_modulos_programa(?)', [$programCode]);

            return collect($results);
        } catch (\Exception $e) {
            Log::error("Error ejecutando sp_obtener_modulos_programa para programa {$programCode}: {$e->getMessage()}");
            throw $e;
        }
    }

    /**
     * Obtiene todos los módulos de todos los programas
     * 
     * @param array $programCodes Array de códigos de programas
     * @return \Illuminate\Support\Collection
     */
    public static function getAllModules(array $programCodes)
    {
        $allModules = collect();

        foreach ($programCodes as $programCode) {
            try {
                $modules = self::getModulesByProgram($programCode);
                $allModules = $allModules->merge($modules);
            } catch (\Exception $e) {
                Log::error("Error obteniendo módulos del programa {$programCode}: {$e->getMessage()}");
                // Continuar con el siguiente programa en caso de error
                continue;
            }
        }

        return $allModules;
    }

    /**
     * Mapea los datos externos al formato del modelo local
     * 
     * @param object $externalModule Objeto con datos del stored procedure
     * @param int $localProgramId ID del programa en la BD local
     * @return array
     */
    public static function toLocalFormat($externalModule, int $localProgramId): array
    {
        return [
            'program_id' => $localProgramId,
            'name' => $externalModule->nombre_modulo ?? null,
            'start_date' => $externalModule->fecha_inicio ?? null,
            'finalization_date' => $externalModule->fecha_fin ?? null,
            'status' => $externalModule->estado_modulo ?? null,
            'teacher_name' => $externalModule->docente ?? null,
            'external_teacher_id' => $externalModule->id_docente ?? null, // ID del docente en BD externa
            // teacher_id se asignará durante la sincronización
        ];
    }

    /**
     * Genera un identificador único para el módulo basado en programa y nombre
     * Esto ayuda a identificar módulos entre sincronizaciones
     * 
     * @param object $externalModule
     * @param string $programCode
     * @return string
     */
    public static function generateModuleKey($externalModule, string $programCode): string
    {
        $moduleName = $externalModule->nombre_modulo ?? 'sin_nombre';
        return md5($programCode . '_' . $moduleName);
    }
}

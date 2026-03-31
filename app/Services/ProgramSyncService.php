<?php

namespace App\Services;

use App\Models\Program;
use App\Models\External\ExternalProgram;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class ProgramSyncService
{
    /**
     * Sincroniza todos los programas desde la base de datos externa
     */
    public function syncAll(): array
    {
        $stats = [
            'created' => 0,
            'updated' => 0,
            'errors' => 0,
            'total' => 0,
        ];

        try {
            // Obtener todos los programas de la base de datos externa
            $externalPrograms = ExternalProgram::all();
            $stats['total'] = $externalPrograms->count();

            Log::info("Iniciando sincronización de {$stats['total']} programas");

            foreach ($externalPrograms as $externalProgram) {
                try {
                    $this->syncProgram($externalProgram, $stats);
                } catch (\Exception $e) {
                    $stats['errors']++;
                    Log::error("Error sincronizando programa {$externalProgram->id_programa}: {$e->getMessage()}");
                }
            }

            Log::info("Sincronización completada", $stats);

        } catch (\Exception $e) {
            Log::error("Error en la sincronización de programas: {$e->getMessage()}");
            throw $e;
        }

        return $stats;
    }

    /**
     * Sincroniza un programa individual
     */
    protected function syncProgram(ExternalProgram $externalProgram, array &$stats): void
    {
        $localData = $externalProgram->toLocalFormat();

        // Buscar el programa local por código
        $localProgram = Program::where('code', $localData['code'])->first();

        if ($localProgram) {
            // Actualizar solo si hay cambios
            if ($this->hasChanges($localProgram, $localData)) {
                // Preservar campos locales que no vienen de la BD externa
                $localData['academic_code'] = $localProgram->academic_code;
                $localData['area'] = $localProgram->area;

                $localProgram->update($localData);
                $stats['updated']++;
                Log::info("Programa actualizado: {$localData['code']} - {$localData['name']}");
            }
        } else {
            // Crear nuevo programa
            Program::create($localData);
            $stats['created']++;
            Log::info("Programa creado: {$localData['code']} - {$localData['name']}");
        }
    }

    /**
     * Verifica si hay cambios entre el programa local y los datos externos
     */
    protected function hasChanges(Program $localProgram, array $externalData): bool
    {
        $fieldsToCheck = [
            'accounting_code',
            'name',
            'version',
            'group',
            'year',
            'status',
            'postgraduate_id',
            'modality',
            'passing_grade',
        ];

        foreach ($fieldsToCheck as $field) {
            // Comparar valores, considerando nulls
            $localValue = $localProgram->$field;
            $externalValue = $externalData[$field] ?? null;

            // Para fechas, convertir a string para comparar
            if (in_array($field, ['start_date', 'finalization_date', 'registration_date'])) {
                $localValue = $localProgram->$field?->format('Y-m-d');
                $externalValue = $externalData[$field] ? 
                    (is_string($externalData[$field]) ? $externalData[$field] : $externalData[$field]->format('Y-m-d')) 
                    : null;
            }

            if ($localValue != $externalValue) {
                return true;
            }
        }

        // También verificar las fechas
        if ($localProgram->start_date?->format('Y-m-d') != ($externalData['start_date'] ?? null)?->format('Y-m-d')) {
            return true;
        }

        if ($localProgram->finalization_date?->format('Y-m-d') != ($externalData['finalization_date'] ?? null)?->format('Y-m-d')) {
            return true;
        }

        if ($localProgram->registration_date?->format('Y-m-d') != ($externalData['registration_date'] ?? null)?->format('Y-m-d')) {
            return true;
        }

        return false;
    }

    /**
     * Sincroniza un programa específico por código
     */
    public function syncByCode(string $code): bool
    {
        try {
            $externalProgram = ExternalProgram::where('id_programa', $code)->first();

            if (!$externalProgram) {
                Log::warning("Programa no encontrado en la BD externa: {$code}");
                return false;
            }

            $stats = ['created' => 0, 'updated' => 0, 'errors' => 0];
            $this->syncProgram($externalProgram, $stats);

            return true;
        } catch (\Exception $e) {
            Log::error("Error sincronizando programa {$code}: {$e->getMessage()}");
            return false;
        }
    }

    /**
     * Obtiene estadísticas de la sincronización
     */
    public function getStats(): array
    {
        return [
            'local_count' => Program::count(),
            'external_count' => ExternalProgram::count(),
            'last_sync' => Program::max('updated_at'),
        ];
    }
}

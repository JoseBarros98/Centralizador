<?php

namespace App\Services;

use App\Models\Teacher;
use Illuminate\Support\Facades\Log;

class TeacherSyncService
{
    /**
     * Sincronizar o crear un docente desde datos externos
     * 
     * @param string|null $externalId ID del docente en la BD externa
     * @param string|null $fullName Nombre completo del docente
     * @return Teacher|null
     */
    public function syncTeacher($externalId, $fullName)
    {
        // Si no hay external_id, no podemos sincronizar
        if (empty($externalId)) {
            return null;
        }

        try {
            // Buscar si ya existe el docente
            $teacher = Teacher::findByExternalId($externalId);

            if ($teacher) {
                // Si existe, verificar si necesita actualización del nombre
                if (!empty($fullName) && $teacher->name !== $fullName) {
                    $teacher->update(['name' => $fullName]);
                    Log::info("Docente actualizado: {$externalId} - {$fullName}");
                }
            } else {
                // Crear nuevo docente
                $teacher = Teacher::create([
                    'external_id' => $externalId,
                    'name' => $fullName ?? 'Docente ' . $externalId,
                    'is_external' => true,
                ]);
                Log::info("Docente creado: {$externalId} - {$fullName}");
            }

            return $teacher;
        } catch (\Exception $e) {
            Log::error("Error al sincronizar docente {$externalId}: " . $e->getMessage());
            return null;
        }
    }

    /**
     * Sincronizar múltiples docentes desde un array de datos
     * 
     * @param array $teachersData Array con ['external_id' => ..., 'name' => ...]
     * @return array Array de Teacher objects creados/actualizados
     */
    public function syncMultipleTeachers(array $teachersData)
    {
        $syncedTeachers = [];

        foreach ($teachersData as $data) {
            $teacher = $this->syncTeacher(
                $data['external_id'] ?? null,
                $data['name'] ?? null
            );

            if ($teacher) {
                $syncedTeachers[] = $teacher;
            }
        }

        return $syncedTeachers;
    }

    /**
     * Obtener estadísticas de docentes sincronizados
     * 
     * @return array
     */
    public function getStats()
    {
        return [
            'total_teachers' => Teacher::count(),
            'external_teachers' => Teacher::where('is_external', true)->count(),
            'local_teachers' => Teacher::where('is_external', false)->count(),
        ];
    }
}

<?php

namespace App\Http\Controllers;

use App\Models\Module;
use App\Models\ModuleClass;
use App\Models\Program;
use App\Models\Attendance;
use App\Helpers\NameMatcher;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\DB;
use Illuminate\Routing\Controller;
use Barryvdh\DomPDF\Facade\Pdf;
use PhpOffice\PhpSpreadsheet\IOFactory;

class AttendanceController extends Controller
{
    public function __construct()
    {
        $this->middleware('permission:program.view_attendance')->only(['show', 'summary']);
        $this->middleware('permission:program.manage_attendance')->only(['uploadForm', 'upload', 'recalculatePercentages']);
        $this->middleware('permission:program.export_attendance')->only(['exportPdf']);
    }

    /**
     * Muestra el formulario para cargar un archivo de asistencia.
     */
    public function uploadForm(Program $program, Module $module, ModuleClass $class)
    {
        return view('attendances.upload', compact('program', 'module', 'class'));
    }

    /**
     * Procesa el archivo de asistencia cargado.
     */
    public function upload(Request $request, Program $program, Module $module, ModuleClass $class)
    {
        Log::info('Solicitud de carga de asistencia recibida', $request->all());
        
        try {
            // Validar que se ha enviado un archivo
            if (!$request->hasFile('attendance_file')) {
                return back()->withErrors(['attendance_file' => 'Debe seleccionar un archivo XLSX de asistencia.']);
            }
            
            $file = $request->file('attendance_file');
            Log::info('Archivo recibido', [
                'nombre' => $file->getClientOriginalName(),
                'extension' => $file->getClientOriginalExtension(),
                'mime_type' => $file->getMimeType(),
                'tamaño' => $file->getSize()
            ]);
            
            // Verificar que el archivo es un xlsx
            if ($file->getClientOriginalExtension() !== 'xlsx') {
                return back()->withErrors(['attendance_file' => 'El archivo debe ser un XLSX válido.']);
            }
            
            // Cargar el archivo XLSX
            $spreadsheet = IOFactory::load($file->getRealPath());
            $sheet = $spreadsheet->getActiveSheet();
            
            // Convertir la hoja a array
            $rows = $sheet->toArray();
            
            Log::info('Filas leídas del XLSX: ' . count($rows));
            
            // Variables para almacenar metadatos
            $classDurationFromXLSX = null;
            $startTimeFromXLSX = null;
            $endTimeFromXLSX = null;
            
            // Buscar la fila de encabezados (normalmente la primera fila)
            $headerRow = 0;
            $headers = $rows[$headerRow];
            Log::info('Encabezados encontrados', $headers);
            
            // Identificar las columnas relevantes según tu estructura
            $nameIndex = null;
            $surnameIndex = null;
            $emailIndex = null;
            $durationIndex = null;
            $joinTimeIndex = null;
            $leaveTimeIndex = null;

            foreach ($headers as $index => $header) {
                $header = strtolower(trim($header));
                if (strpos($header, 'nombre') !== false) $nameIndex = $index;
                if (strpos($header, 'apellido') !== false) $surnameIndex = $index;
                if (strpos($header, 'correo') !== false || strpos($header, 'email') !== false) $emailIndex = $index;
                if (strpos($header, 'duraci') !== false || strpos($header, 'duration') !== false) $durationIndex = $index;
                if (strpos($header, 'hora a la que se unió') !== false || strpos($header, 'join time') !== false) $joinTimeIndex = $index;
                if (strpos($header, 'hora a la que abandonó') !== false || strpos($header, 'leave time') !== false) $leaveTimeIndex = $index;
            }

            if ($nameIndex === null || $surnameIndex === null || $emailIndex === null || $durationIndex === null) {
                return back()->withErrors(['attendance_file' => 'No se pudieron identificar las columnas necesarias en el XLSX.']);
            }
            
            // Obtener todas las inscripciones del programa
            $inscriptions = $program->inscriptions()->get();

            // Inicializar contadores
            $registeredCount = 0;
            $unregisteredCount = 0;
            $matchedByNameCount = 0;
            $mergedEntriesCount = 0; // Si no hay lógica de fusión, dejar en 0

            // Procesar cada fila de datos (saltando los encabezados)
            for ($i = $headerRow + 1; $i < count($rows); $i++) {
                $row = $rows[$i];
                if (count($row) <= max($nameIndex, $surnameIndex, $emailIndex, $durationIndex, $joinTimeIndex, $leaveTimeIndex)) continue;

                $firstName = trim($row[$nameIndex]);
                $surname = trim($row[$surnameIndex]);
                $email = trim($row[$emailIndex]);
                $durationStr = trim($row[$durationIndex]);
                $joinTime = $joinTimeIndex !== null ? trim($row[$joinTimeIndex]) : null;
                $leaveTime = $leaveTimeIndex !== null ? trim($row[$leaveTimeIndex]) : null;

                // Concatenar nombre completo del XLSX
                $fullNameXlsx = mb_strtoupper(trim($firstName . ' ' . $surname), 'UTF-8');

                // Convertir duración a minutos
                $duration = $this->parseDurationToMinutes($durationStr);

                // Buscar inscripción por nombre completo
                $inscription = $inscriptions->first(function($i) use ($fullNameXlsx) {
                    $fullNameInscription = mb_strtoupper(trim($i->first_name . ' ' . $i->paternal_surname . ' ' . $i->maternal_surname), 'UTF-8');
                    return $fullNameInscription === $fullNameXlsx;
                });

                // Contar registrados y no registrados
                if ($inscription) {
                    $registeredCount++;
                } else {
                    $unregisteredCount++;
                }

                // Determinar el estado de asistencia
                if ($duration < 30) {
                    $status = 'absent';
                } elseif ($duration < 60) {
                    $status = 'late';
                } else {
                    $status = 'present';
                }

                // Crear el registro de asistencia
                $class->attendances()->create([
                    'inscription_id' => $inscription ? $inscription->id : null,
                    'name' => $inscription ? $inscription->getFullName() : $fullNameXlsx,
                    'email' => $email,
                    'duration' => $duration,
                    'is_registered_inscription' => $inscription ? true : false,
                    'attendance_percentage' => 0, // Calcula si lo necesitas
                    'status' => $status,
                ]);
            }
            
            $message = "Se procesaron $registeredCount participantes registrados y $unregisteredCount no registrados. $matchedByNameCount coincidencias se realizaron por similitud de nombre. Se fusionaron $mergedEntriesCount entradas duplicadas.";
            
            return redirect()->route('attendances.show', [$program->id, $module->id, $class->id])
                ->with('success', $message);
                
        } catch (\Exception $e) {
            Log::error('Error al procesar el archivo: ' . $e->getMessage(), [
                'exception' => $e,
                'trace' => $e->getTraceAsString()
            ]);
            
            return back()->withErrors(['attendance_file' => 'Error al procesar el archivo: ' . $e->getMessage()]);
        }
    }

    /**
     * Muestra los detalles de asistencia para una clase.
     */
    public function show(Program $program, Module $module, ModuleClass $class)
    {
        // Cargar las asistencias con sus inscritos relacionados
        $attendances = $class->attendances()
            ->with('inscription')
            ->orderBy('is_registered_inscription', 'desc')
            ->orderBy('name')
            ->get();
        
        // Obtener inscritos registrados que no asistieron
        $presentInscriptionIds = $attendances->pluck('inscription_id')->filter()->toArray();
        $absentInscription = $program->inscriptions()
            ->whereNotIn('id', $presentInscriptionIds)
            ->orderBy('first_name')
            ->orderBy('paternal_surname')
            ->orderBy('maternal_surname')
            ->get();
        
        // Obtener la duración de la clase para mostrarla en la vista
        $classDuration = $class->end_time->diffInMinutes($class->start_time);
        
        // Buscar si hay un archivo de metadatos con la duración real
        $metadataFiles = Storage::disk('local')->files('attendances');
        $metadata = null;
        
        foreach ($metadataFiles as $file) {
            if (strpos($file, 'metadata_attendance_' . $class->id . '_') !== false) {
                $metadata = json_decode(Storage::disk('local')->get($file), true);
                Log::info('Metadatos encontrados para la clase ID: ' . $class->id, $metadata);
                break;
            }
        }
        
        return view('attendances.show', compact('program', 'module', 'class', 'attendances', 'absentInscription', 'classDuration', 'metadata'));
    }

    /**
     * Muestra un resumen de asistencia para todas las clases del módulo.
     */
    public function summary(Program $program, Module $module)
    {
        // Obtener todas las clases del módulo ordenadas por fecha
        $classes = $module->classes()
            ->orderBy('class_date')
            ->orderBy('start_time')
            ->get();
        
        // Obtener todos los inscritos del programa
        $inscriptions = $program->inscriptions()
            ->orderBy('first_name')
            ->orderBy('paternal_surname')
            ->orderBy('maternal_surname')
            ->get();
        
        // Crear una matriz de asistencia
        $attendanceMatrix = [];
        
        foreach ($inscriptions as $inscription) {
            $inscriptionAttendance = [
                'inscription' => $inscription,
                'classes' => []
            ];
            
            foreach ($classes as $class) {
                // Buscar si existe un registro de asistencia para este inscrito en esta clase
                $attendance = Attendance::where('module_class_id', $class->id)
                    ->where('inscription_id', $inscription->id)
                    ->first();
                
                $inscriptionAttendance['classes'][$class->id] = [
                    'attended' => $attendance && ($attendance->status === 'present' || $attendance->status === 'late'),
                    'status' => $attendance ? $attendance->status : null,
                    'percentage' => $attendance ? $attendance->attendance_percentage : 0,
                    'duration' => $attendance ? $attendance->duration : 0
                ];
            }
            
            // Calcular estadísticas de asistencia para este inscrito
            $totalClasses = count($classes);
            $attendedClasses = count(array_filter($inscriptionAttendance['classes'], function($a) {
                return $a['attended'];
            }));
            
            $inscriptionAttendance['stats'] = [
                'total' => $totalClasses,
                'attended' => $attendedClasses,
                'percentage' => $totalClasses > 0 ? ($attendedClasses / $totalClasses) * 100 : 0
            ];
            
            $attendanceMatrix[] = $inscriptionAttendance;
        }
        
        return view('attendances.summary', compact('program', 'module', 'classes', 'inscriptions', 'attendanceMatrix'));
    }

    /**
     * Recalcula los porcentajes de asistencia para una clase.
     */
    public function recalculatePercentages(Program $program, Module $module, ModuleClass $class)
    {
        try {
            // Intentar obtener la duración del archivo de metadatos
            $metadata = null;
            $metadataFiles = Storage::disk('local')->files('attendances');
            
            foreach ($metadataFiles as $file) {
                if (strpos($file, 'metadata_attendance_' . $class->id . '_') !== false) {
                    $metadata = json_decode(Storage::disk('local')->get($file), true);
                    Log::info('Metadatos encontrados para recalcular porcentajes, clase ID: ' . $class->id, $metadata);
                    break;
                }
            }
            
            // Determinar la duración de la clase en minutos
            if ($metadata && isset($metadata['class_duration']) && $metadata['class_duration'] > 0) {
                $classDuration = $metadata['class_duration'];
                Log::info('Usando duración de clase de los metadatos: ' . $classDuration . ' minutos');
            } else {
                // Si no hay metadatos válidos, usamos la duración calculada de la clase
                $classDuration = $class->end_time->diffInMinutes($class->start_time);
                
                // Verificar que la duración de la clase sea válida
                if ($classDuration <= 0) {
                    Log::warning('Duración de clase inválida (0 minutos) para la clase ID: ' . $class->id);
                    $classDuration = 120; // Valor predeterminado si la duración es inválida
                }
                
                Log::info('Usando duración calculada de la clase: ' . $classDuration . ' minutos');
            }
            
            // Obtener todas las asistencias de la clase
            $attendances = $class->attendances()->get();
            
            foreach ($attendances as $attendance) {
                // Recalcular el porcentaje de asistencia
                $attendancePercentage = ($attendance->duration / $classDuration) * 100;
                
                // Asegurarse de que el porcentaje no exceda el 100%
                $attendancePercentage = min(100, $attendancePercentage);
                
                Log::info('Recalculando para: ' . $attendance->name, [
                    'duración_asistencia' => $attendance->duration,
                    'duración_clase' => $classDuration,
                    'porcentaje_anterior' => $attendance->attendance_percentage,
                    'porcentaje_nuevo' => $attendancePercentage
                ]);
                
                // Actualizar el estado basado en la duración en minutos
                if ($attendance->duration < 45) {
                    $status = 'absent'; // Falta
                } else if ($attendance->duration >= 100) {
                    $status = 'present'; // Presente
                } else {
                    $status = 'late'; // Atraso
                }
                
                // Actualizar el registro
                $attendance->update([
                    'attendance_percentage' => $attendancePercentage,
                    'status' => $status
                ]);
            }
            
            $message = 'Los porcentajes de asistencia han sido recalculados correctamente.';
            if ($metadata && isset($metadata['class_duration'])) {
                $message .= " Se utilizó la duración de la clase: {$metadata['class_duration']} minutos.";
            }
            
            return redirect()->route('attendances.show', [$program->id, $module->id, $class->id])
                ->with('success', $message);
            
        } catch (\Exception $e) {
            Log::error('Error al recalcular porcentajes: ' . $e->getMessage(), [
                'exception' => $e,
                'trace' => $e->getTraceAsString()
            ]);
            
            return back()->withErrors(['error' => 'Error al recalcular los porcentajes: ' . $e->getMessage()]);
        }
    }

    /**
     * Genera un PDF con el resumen de asistencia del módulo.
     */
    public function exportPdf(Program $program, Module $module)
    {
        // Obtener todas las clases del módulo ordenadas por fecha
        $classes = $module->classes()
            ->orderBy('class_date')
            ->orderBy('start_time')
            ->get();
        
        // Obtener todos los inscritos del programa
        $inscriptions = $program->inscriptions()
            ->orderBy('first_name')
            ->orderBy('paternal_surname')
            ->orderBy('maternal_surname')
            ->get();
        
        // Crear una matriz de asistencia (igual que en el método summary)
        $attendanceMatrix = [];
        
        foreach ($inscriptions as $inscription) {
            $inscriptionAttendance = [
                'inscription' => $inscription,
                'classes' => []
            ];
            
            foreach ($classes as $class) {
                // Buscar si existe un registro de asistencia para este inscrito en esta clase
                $attendance = Attendance::where('module_class_id', $class->id)
                    ->where('inscription_id', $inscription->id)
                    ->first();
            
                $inscriptionAttendance['classes'][$class->id] = [
                    'attended' => $attendance && ($attendance->status === 'present' || $attendance->status === 'late'),
                    'status' => $attendance ? $attendance->status : null,
                    'percentage' => $attendance ? $attendance->attendance_percentage : 0,
                    'duration' => $attendance ? $attendance->duration : 0
                ];
            }
            
            // Calcular estadísticas de asistencia para este inscrito
            $totalClasses = count($classes);
            $attendedClasses = count(array_filter($inscriptionAttendance['classes'], function($a) {
                return $a['attended'];
            }));
            
            $inscriptionAttendance['stats'] = [
                'total' => $totalClasses,
                'attended' => $attendedClasses,
                'percentage' => $totalClasses > 0 ? ($attendedClasses / $totalClasses) * 100 : 0
            ];
            
            $attendanceMatrix[] = $inscriptionAttendance;
        }
        
        // Generar el PDF
        $pdf = PDF::loadView('attendances.summary-pdf', compact('program', 'module', 'classes', 'inscriptions', 'attendanceMatrix'));
        
        // Configurar opciones del PDF
        $pdf->setPaper('a4', 'portrait');
        
        // Descargar el PDF
        $filename = 'asistencia_' . $module->name . '_' . now()->format('Ymd_His') . '.pdf';
        return $pdf->download($filename);
    }
    
    /**
     * Convierte una cadena de duración a minutos.
     *
     * @param string $durationStr
     * @return int
     */
    protected function parseDurationToMinutes($durationStr)
    {
        $durationStr = trim($durationStr);

        // Si es solo un número, se asume que son minutos
        if (is_numeric($durationStr)) {
            return (int)$durationStr;
        }

        // Formato HH:MM:SS o MM:SS
        if (preg_match('/^(\d{1,2}):(\d{2})(?::(\d{2}))?$/', $durationStr, $matches)) {
            $hours = isset($matches[3]) ? (int)$matches[1] : 0;
            $minutes = isset($matches[3]) ? (int)$matches[2] : (int)$matches[1];
            $seconds = isset($matches[3]) ? (int)$matches[3] : (int)$matches[2];

            if ($hours > 0) {
                return $hours * 60 + $minutes + ($seconds >= 30 ? 1 : 0);
            } else {
                return $minutes + ($seconds >= 30 ? 1 : 0);
            }
        }

        // Formato tipo "1h 10m", "2h", "45m"
        if (preg_match('/(?:(\d+)\s*h)?\s*(?:(\d+)\s*m)?/i', $durationStr, $matches)) {
            $hours = isset($matches[1]) && $matches[1] !== '' ? (int)$matches[1] : 0;
            $minutes = isset($matches[2]) && $matches[2] !== '' ? (int)$matches[2] : 0;
            return $hours * 60 + $minutes;
        }

        // Si no se puede interpretar, retorna 0
        return 0;
    }
}

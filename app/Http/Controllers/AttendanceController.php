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
use Illuminate\Support\Facades\Auth;
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
                Log::error('No se recibió archivo');
                return back()->withErrors(['attendance_file' => 'Debe seleccionar un archivo XLSX de asistencia.']);
            }
            
            $file = $request->file('attendance_file');
            
            // Verificar que el archivo se subió correctamente
            if (!$file->isValid()) {
                Log::error('Archivo inválido', [
                    'error' => $file->getError(),
                    'error_message' => $file->getErrorMessage()
                ]);
                return back()->withErrors(['attendance_file' => 'Error al subir el archivo: ' . $file->getErrorMessage()]);
            }
            
            Log::info('Archivo recibido', [
                'nombre' => $file->getClientOriginalName(),
                'extension' => $file->getClientOriginalExtension(),
                'mime_type' => $file->getMimeType(),
                'tamaño' => $file->getSize(),
                'path' => $file->getRealPath(),
                'temp_name' => $file->getPathname()
            ]);
            
            // Verificar que el archivo es un xlsx
            if ($file->getClientOriginalExtension() !== 'xlsx') {
                return back()->withErrors(['attendance_file' => 'El archivo debe ser un XLSX válido.']);
            }
            
            // Verificar que el archivo existe y tiene contenido
            $filePath = $file->getRealPath();
            
            if (!$filePath || !file_exists($filePath)) {
                Log::error('Archivo no existe en path', ['path' => $filePath]);
                // Intentar con getPathname() como alternativa
                $filePath = $file->getPathname();
                Log::info('Intentando con getPathname()', ['path' => $filePath]);
            }
            
            if (!file_exists($filePath)) {
                Log::error('Archivo no existe en ningún path');
                return back()->withErrors(['attendance_file' => 'No se pudo acceder al archivo subido.']);
            }
            
            $fileSize = filesize($filePath);
            Log::info('Tamaño del archivo', ['bytes' => $fileSize]);
            
            if ($fileSize === 0) {
                Log::error('Archivo vacío', ['path' => $filePath]);
                return back()->withErrors(['attendance_file' => 'El archivo está vacío.']);
            }
            
            // Verificar que es un archivo ZIP válido (los XLSX son archivos ZIP)
            $zipCheck = new \ZipArchive();
            $zipResult = $zipCheck->open($filePath, \ZipArchive::CHECKCONS);
            if ($zipResult !== true) {
                Log::error('El archivo no es un ZIP válido', [
                    'path' => $filePath,
                    'zip_error_code' => $zipResult
                ]);
                return back()->withErrors(['attendance_file' => 'El archivo XLSX parece estar corrupto. Por favor, vuelva a guardarlo desde Excel.']);
            }
            $zipCheck->close();
            
            // Cargar el archivo XLSX con el reader específico
            Log::info('Intentando cargar archivo con IOFactory', ['path' => $filePath]);
            
            try {
                // Leer el contenido del archivo en memoria
                $fileContent = file_get_contents($filePath);
                Log::info('Archivo leído en memoria', ['size' => strlen($fileContent)]);
                
                // Crear un archivo temporal en memoria
                $tempStream = fopen('php://temp', 'r+');
                fwrite($tempStream, $fileContent);
                rewind($tempStream);
                
                // Obtener el path del stream temporal
                $meta = stream_get_meta_data($tempStream);
                $tempStreamPath = $meta['uri'];
                
                Log::info('Stream temporal creado', ['stream_path' => $tempStreamPath]);
                
                // Intentar con el reader de XLSX específicamente
                $reader = \PhpOffice\PhpSpreadsheet\IOFactory::createReader('Xlsx');
                $spreadsheet = $reader->load($tempStreamPath);
                
                fclose($tempStream);
            } catch (\Exception $e) {
                Log::error('Error al leer con stream, intentando método directo', [
                    'error' => $e->getMessage()
                ]);
                // Si falla, intentar leer directamente desde el path original
                $reader = \PhpOffice\PhpSpreadsheet\IOFactory::createReader('Xlsx');
                $reader->setReadDataOnly(true);
                $spreadsheet = $reader->load($filePath);
            }
            
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

                // Buscar inscripción con múltiples métodos de matching
                $inscription = $this->findInscriptionMatch($inscriptions, $fullNameXlsx, $email);

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

                // Crear o actualizar el registro de asistencia
                $attendanceData = [
                    'name' => $inscription ? $inscription->getFullName() : $fullNameXlsx,
                    'email' => $email,
                    'duration' => $duration,
                    'is_registered_inscription' => $inscription ? true : false,
                    'attendance_percentage' => 0, // Calcula si lo necesitas
                    'status' => $status,
                ];

                // Usar updateOrCreate para evitar duplicados
                if ($inscription) {
                    // Para inscritos, buscar por inscription_id y module_class_id
                    Attendance::updateOrCreate(
                        [
                            'module_class_id' => $class->id,
                            'inscription_id' => $inscription->id,
                        ],
                        $attendanceData
                    );
                } else {
                    // Para no inscritos, buscar por email/nombre y module_class_id
                    Attendance::updateOrCreate(
                        [
                            'module_class_id' => $class->id,
                            'email' => $email,
                            'inscription_id' => null,
                        ],
                        $attendanceData
                    );
                }
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
        // Cargar las asistencias con sus inscritos relacionados, filtrando solo las que tienen inscription válida
        // o las que no tienen inscription_id (participantes no registrados)
        $attendances = $class->attendances()
            ->with('inscription')
            ->where(function($query) {
                $query->whereHas('inscription')  // Tiene una inscripción válida
                      ->orWhereNull('inscription_id');  // O es un participante no registrado
            })
            ->orderBy('is_registered_inscription', 'desc')
            ->orderBy('name')
            ->get();
        
        // Obtener inscritos registrados que no asistieron con información de licencias
        $presentInscriptionIds = $attendances->pluck('inscription_id')->filter()->toArray();
        $absentInscription = $program->inscriptions()
            ->whereNotIn('inscriptions.id', $presentInscriptionIds)
            ->orderBy('full_name')
            ->get();

        // Verificar si hay licencias para los inscritos ausentes
        foreach ($absentInscription as $inscription) {
            $licenseRecord = Attendance::where('module_class_id', $class->id)
                ->where('inscription_id', $inscription->id)
                ->where('license_type', '!=', null)
                ->first();
            
            $inscription->license_info = $licenseRecord ? [
                'type' => $licenseRecord->license_type,
                'notes' => $licenseRecord->license_notes,
                'granted_by' => $licenseRecord->license_granted_by,
                'granted_at' => $licenseRecord->license_granted_at
            ] : null;
        }
        
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
            ->orderBy('full_name')
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
                    'attended' => $attendance && ($attendance->status === 'present' || $attendance->status === 'late') && !$attendance->has_license,
                    'status' => $attendance ? $attendance->status : null,
                    'percentage' => $attendance ? $attendance->attendance_percentage : 0,
                    'duration' => $attendance ? $attendance->duration : 0,
                    'has_license' => $attendance ? $attendance->has_license : false,
                    'license_type' => $attendance ? $attendance->license_type : null,
                    'license_notes' => $attendance ? $attendance->license_notes : null,
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
            ->orderBy('full_name')
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
                    'attended' => $attendance && ($attendance->status === 'present' || $attendance->status === 'late') && !$attendance->has_license,
                    'status' => $attendance ? $attendance->status : null,
                    'percentage' => $attendance ? $attendance->attendance_percentage : 0,
                    'duration' => $attendance ? $attendance->duration : 0,
                    'has_license' => $attendance ? $attendance->has_license : false,
                    'license_type' => $attendance ? $attendance->license_type : null,
                    'license_notes' => $attendance ? $attendance->license_notes : null,
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
     * Busca una inscripción usando múltiples métodos de matching.
     */
    private function findInscriptionMatch($inscriptions, $fullNameXlsx, $email)
    {
        // Separar palabras pegadas usando mayúsculas como delimitador
        // Ejemplo: "AlexanderUrquizu" -> "Alexander Urquizu"
        $fullNameXlsx = preg_replace('/([a-z])([A-Z])/', '$1 $2', $fullNameXlsx);
        
        // Normalizar el nombre del archivo XLSX
        $normalizedXlsxName = NameMatcher::normalizeName($fullNameXlsx);
        
        $bestMatch = null;
        $highestSimilarity = 0;

        foreach ($inscriptions as $inscription) {
            // Método 1: Coincidencia por email (más confiable)
            if (!empty($email) && !empty($inscription->email)) {
                if (strtolower(trim($inscription->email)) === strtolower(trim($email))) {
                    return $inscription;
                }
            }

            // Separar palabras pegadas en el nombre de la inscripción también
            // Ejemplo: "MaritzaSerrudo" -> "Maritza Serrudo"
            $inscriptionFullName = preg_replace('/([a-z])([A-Z])/', '$1 $2', $inscription->full_name);
            
            // Normalizar el nombre completo de la inscripción
            $normalizedInscriptionName = NameMatcher::normalizeName($inscriptionFullName);
            
            // Método 2: Coincidencia exacta de nombres normalizados
            if ($normalizedInscriptionName === $normalizedXlsxName) {
                return $inscription;
            }
            
            // Método 3: Comparar palabras individuales sin importar el orden
            // Ejemplo: "pedro montes flores" = "montes flores pedro"
            $xlsxWords = array_filter(explode(' ', $normalizedXlsxName));
            $inscriptionWords = array_filter(explode(' ', $normalizedInscriptionName));
            
            // Si tienen la misma cantidad de palabras y todas coinciden (sin importar orden)
            if (count($xlsxWords) === count($inscriptionWords) && count($xlsxWords) >= 2) {
                sort($xlsxWords);
                sort($inscriptionWords);
                if ($xlsxWords === $inscriptionWords) {
                    Log::info('Coincidencia exacta encontrada (diferente orden)', [
                        'xlsx_name' => $fullNameXlsx,
                        'inscription_name' => $inscription->full_name
                    ]);
                    return $inscription;
                }
            }
            
            // Método 4: Coincidencia parcial - todas las palabras del Excel están en la inscripción
            // Ejemplo: "ANAHI SERRUDO" está contenido en "ANAHI MARITZA SERRUDO CALDERON"
            if (count($xlsxWords) >= 2) {
                $allWordsFound = true;
                foreach ($xlsxWords as $word) {
                    if (!in_array($word, $inscriptionWords)) {
                        $allWordsFound = false;
                        break;
                    }
                }
                
                if ($allWordsFound) {
                    // Calcular un score basado en cuántas palabras coinciden
                    $matchScore = (count($xlsxWords) / count($inscriptionWords)) * 100;
                    
                    // Si es mejor que el match anterior
                    if ($matchScore > $highestSimilarity) {
                        $highestSimilarity = $matchScore;
                        $bestMatch = $inscription;
                        
                        Log::info('Coincidencia parcial encontrada (nombre completo vs parcial)', [
                            'xlsx_name' => $fullNameXlsx,
                            'inscription_name' => $inscription->full_name,
                            'inscription_normalized' => $normalizedInscriptionName,
                            'score' => $matchScore
                        ]);
                    }
                    continue;
                }
            }
            
            // Método 5: Contar cuántas palabras coinciden (al menos 2)
            if (count($xlsxWords) >= 2 && count($inscriptionWords) >= 2) {
                $matchingWords = count(array_intersect($xlsxWords, $inscriptionWords));
                
                // Si coinciden al menos 2 palabras
                if ($matchingWords >= 2) {
                    $wordMatchPercent = ($matchingWords / max(count($xlsxWords), count($inscriptionWords))) * 100;
                    
                    if ($wordMatchPercent > $highestSimilarity && $wordMatchPercent >= 50) {
                        $highestSimilarity = $wordMatchPercent;
                        $bestMatch = $inscription;
                    }
                    continue;
                }
            }
            
            // Método 6: Usar similar_text para encontrar coincidencias por similitud
            similar_text($normalizedXlsxName, $normalizedInscriptionName, $percent);
            
            // Considerar match si es mayor al 70% y es el mejor encontrado
            if ($percent > $highestSimilarity && $percent > 70) {
                $highestSimilarity = $percent;
                $bestMatch = $inscription;
                
                // Si encontramos un match perfecto, salir del bucle
                if ($percent == 100) {
                    break;
                }
            }
        }

        if ($bestMatch) {
            Log::info('Coincidencia encontrada', [
                'xlsx_name' => $fullNameXlsx,
                'inscription_name' => $bestMatch->full_name,
                'similarity' => $highestSimilarity
            ]);
        } else {
            Log::warning('No se encontró coincidencia para', [
                'xlsx_name' => $fullNameXlsx,
                'normalized' => $normalizedXlsxName,
                'email' => $email
            ]);
        }

        return $bestMatch;
    }

    /**
     * Calcula la similitud entre dos nombres usando múltiples algoritmos.
     */
    private function calculateNameSimilarity($name1, $name2)
    {
        // Normalizar espacios y caracteres especiales
        $name1 = preg_replace('/\s+/', ' ', trim($name1));
        $name2 = preg_replace('/\s+/', ' ', trim($name2));
        
        // Similar text percentage
        similar_text($name1, $name2, $percent);
        
        // Levenshtein distance (convertir a porcentaje)
        $maxLen = max(strlen($name1), strlen($name2));
        if ($maxLen == 0) return 100;
        
        $levenshtein = levenshtein($name1, $name2);
        $levenshteinPercent = (($maxLen - $levenshtein) / $maxLen) * 100;
        
        // Retornar el promedio de ambos métodos
        return ($percent + $levenshteinPercent) / 2;
    }
    
    /**
     * Convierte una cadena de duración a minutos.
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

    /**
     * Muestra la vista de asistencias con gestión de licencias.
     */
    public function showWithLicenses(Program $program, Module $module, ModuleClass $class)
    {
        // Cargar las asistencias con sus inscritos relacionados
        $attendances = $class->attendances()
            ->with('inscription')
            ->orderBy('is_registered_inscription', 'desc')
            ->orderBy('name')
            ->get();
        
        // Obtener inscritos registrados que no asistieron O que tienen registros de licencia revocada
        $presentInscriptionIds = $attendances->where('duration', '>', 0)->pluck('inscription_id')->filter()->toArray();
        
        // Obtener todos los inscritos ausentes (sin asistencia real) para poder otorgarles licencias
        $absentInscriptions = $program->inscriptions()
            ->whereNotIn('inscriptions.id', $presentInscriptionIds)
            ->orderBy('full_name')
            ->get();

        return view('attendances.show_with_licenses', compact('program', 'module', 'class', 'attendances', 'absentInscriptions'));
    }

    /**
     * Otorga una licencia o permiso a una asistencia.
     */
    public function grantLicense(Request $request, Program $program, Module $module, ModuleClass $class, Attendance $attendance)
    {
        $request->validate([
            'license_type' => 'required|in:permiso,licencia_medica,licencia_laboral,emergencia_familiar,otro',
            'license_notes' => 'nullable|string|max:500'
        ]);

        $attendance->update([
            'has_license' => true,
            'license_type' => $request->license_type,
            'license_notes' => $request->license_notes,
            'license_granted_by' => Auth::user()->name ?? 'Sistema',
            'license_granted_at' => now()
        ]);

        return redirect()
            ->route('attendances.show_with_licenses', [$program->id, $module->id, $class->id])
            ->with('success', 'Licencia otorgada exitosamente.');
    }

    /**
     * Revoca una licencia o permiso de una asistencia.
     */
    public function revokeLicense(Request $request, Program $program, Module $module, ModuleClass $class, Attendance $attendance)
    {
        $attendance->update([
            'has_license' => false,
            'license_type' => null,
            'license_notes' => null,
            'license_granted_by' => null,
            'license_granted_at' => null
        ]);

        return redirect()
            ->route('attendances.show_with_licenses', [$program->id, $module->id, $class->id])
            ->with('success', 'Licencia revocada exitosamente.');
    }

    /**
     * Otorga una licencia a un participante inscrito ausente.
     */
    public function grantLicenseToAbsent(Request $request, Program $program, Module $module, ModuleClass $class, $inscription)
    {
        $request->validate([
            'license_type' => 'required|in:permiso,licencia_medica,licencia_laboral,emergencia_familiar,otro',
            'license_notes' => 'nullable|string|max:500'
        ]);

        // Crear o actualizar registro de asistencia para el inscrito ausente
        $attendanceData = [
            'inscription_id' => $inscription,
            'name' => \App\Models\Inscription::findOrFail($inscription)->getFullName(),
            'email' => \App\Models\Inscription::findOrFail($inscription)->email ?? '',
            'duration' => 0,
            'is_registered_inscription' => true,
            'attendance_percentage' => 0,
            'status' => 'absent',
            'has_license' => true,
            'license_type' => $request->license_type,
            'license_notes' => $request->license_notes,
            'license_granted_by' => Auth::user()->name ?? 'Sistema',
            'license_granted_at' => now()
        ];

        Attendance::updateOrCreate(
            [
                'module_class_id' => $class->id,
                'inscription_id' => $inscription,
            ],
            $attendanceData
        );

        return redirect()
            ->route('attendances.show_with_licenses', [$program->id, $module->id, $class->id])
            ->with('success', 'Licencia otorgada exitosamente al participante ausente.');
    }

    /**
     * Revoca una licencia de un participante inscrito ausente.
     */
    public function revokeLicenseFromAbsent(Request $request, Program $program, Module $module, ModuleClass $class, $inscription)
    {
        $attendance = Attendance::where('module_class_id', $class->id)
            ->where('inscription_id', $inscription)
            ->first();

        if ($attendance) {
            // Si es un registro creado específicamente para la licencia (sin asistencia real)
            if ($attendance->duration == 0 && $attendance->status == 'absent' && $attendance->has_license) {
                // Eliminarlo completamente para que el participante vuelva a aparecer como "sin licencia"
                $attendance->delete();
            } else {
                // Si es un registro real de asistencia, solo quitar la licencia
                $attendance->update([
                    'has_license' => false,
                    'license_type' => null,
                    'license_notes' => null,
                    'license_granted_by' => null,
                    'license_granted_at' => null
                ]);
            }
        }

        return redirect()
            ->route('attendances.show_with_licenses', [$program->id, $module->id, $class->id])
            ->with('success', 'Licencia revocada exitosamente. Ahora puede otorgar una nueva licencia.');
    }
}

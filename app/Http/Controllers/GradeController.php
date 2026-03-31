<?php

namespace App\Http\Controllers;

use App\Models\Grade;
use App\Models\Module;
use App\Models\Program;
use App\Models\Participant;
use App\Models\Inscription;
use Illuminate\Http\Request;
use PhpOffice\PhpSpreadsheet\IOFactory;
use App\Helpers\NameMatcher;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class GradeController extends Controller
{
    public function __construct()
    {
        $this->middleware(['permission:program.view'])->only(['index', 'show', 'summary', 'programSummary']);
        $this->middleware(['permission:program.create'])->only(['uploadForm', 'upload']);
    }

public function uploadForm(Program $program, Module $module)
    {
        return view('grades.upload', compact('program', 'module'));
    }

    /**
     * Procesar el archivo de calificaciones subido.
     */
    public function upload(Request $request, Program $program, Module $module)
    {
        $request->validate([
            'grades_file' => 'required|file|mimes:xlsx,xls',
        ]);

        try {
            $file = $request->file('grades_file');
            
            // Verificar que el archivo existe y tiene contenido
            $filePath = $file->getRealPath();
            if (!file_exists($filePath) || filesize($filePath) === 0) {
                Log::error('Archivo de calificaciones vacío o no existe', ['path' => $filePath]);
                return redirect()->back()->with('error', 'El archivo está vacío o no se pudo leer.');
            }
            
            $spreadsheet = IOFactory::load($filePath);
            $worksheet = $spreadsheet->getActiveSheet();
            $rows = $worksheet->toArray();

            // Eliminar calificaciones anteriores para este módulo
            if ($request->has('replace') && $request->replace) {
                Grade::where('module_id', $module->id)->delete();
            }

            // Buscar las columnas necesarias
            $nameColumn = null;
            $lastNameColumn = null;
            $gradeColumn = null;

            // Asumimos que los encabezados están en la primera fila
            foreach ($rows[0] as $index => $header) {
                if (!is_string($header)) {
                    continue;
                }
                
                $header = trim(strtolower($header));
                
                if (str_contains($header, 'nombre')) {
                    $nameColumn = $index;
                } elseif (str_contains($header, 'apellido')) {
                    $lastNameColumn = $index;
                } elseif (str_contains($header, 'total del curso') || str_contains($header, 'total del curso(real)') || str_contains($header, 'total del curso (real)')) {
                    $gradeColumn = $index;
                }
            }

            Log::info('Columnas detectadas:', [
                'nameColumn' => $nameColumn,
                'lastNameColumn' => $lastNameColumn,
                'gradeColumn' => $gradeColumn,
                'headers' => $rows[0]
            ]);

            if ($nameColumn === null || $lastNameColumn === null || $gradeColumn === null) {
                return redirect()->back()->with('error', 'El archivo no tiene el formato esperado. Se requieren las columnas: Nombre, Apellido(s), Total del curso (Real). Columnas detectadas: ' . implode(', ', array_filter($rows[0])));
            }

            $inscriptions = $program->inscriptions()->get();
            $gradesAdded = 0;
            $gradesUpdated = 0;
            $unmatchedStudents = [];

            // Procesar filas de datos (saltando la primera fila de encabezados)
            for ($i = 1; $i < count($rows); $i++) {
                $row = $rows[$i];
                
                // Verificar si la fila tiene datos
                if (empty($row[$nameColumn]) && empty($row[$lastNameColumn])) {
                    continue;
                }

                $name = trim($row[$nameColumn]);
                $lastName = trim($row[$lastNameColumn]);
                $gradeValue = floatval(str_replace(',', '.', $row[$gradeColumn]));
                $fullName = $name . ' ' . $lastName;
                
                // Buscar inscripción por nombre
                $matchedInscription = $this->findBestInscriptionMatch($fullName, $inscriptions);

                // Crear o actualizar la calificación
                $gradeRecord = Grade::updateOrCreate(
                    [
                        'module_id' => $module->id,
                        'name' => $name,
                        'last_name' => $lastName,
                    ],
                    [
                        'inscription_id' => $matchedInscription ? $matchedInscription->id : null,
                        'grade' => $gradeValue,
                        'approved' => $gradeValue >= 71,
                        'original_name' => $fullName,
                    ]
                );

                if (!$matchedInscription) {
                    $unmatchedStudents[] = $fullName;
                }

                if ($gradeRecord->wasRecentlyCreated) {
                    $gradesAdded++;
                } else {
                    $gradesUpdated++;
                }
            }

            $message = "Calificaciones procesadas: {$gradesAdded} añadidas, {$gradesUpdated} actualizadas.";
            
            if (!empty($unmatchedStudents)) {
                $message .= " <strong>Estudiantes no asociados:</strong> " . implode(', ', $unmatchedStudents);
                session()->flash('warning', 'Algunos estudiantes no se asociaron con inscripciones');
            }

            return redirect()->route('grades.summary', [$program->id, $module->id])
                ->with('success', $message);

        } catch (\Exception $e) {
            Log::error('Error al procesar archivo de calificaciones: ' . $e->getMessage());
            return redirect()->back()->with('error', 'Error al procesar el archivo: ' . $e->getMessage());
        }
    }

    /**
     * Encontrar la mejor coincidencia de inscripción para un nombre
     */
    protected function findBestInscriptionMatch($fullName, $inscriptions)
    {
        $bestMatch = null;
        $highestSimilarity = 0;
        $normalizedExcelName = NameMatcher::normalizeName($fullName);

        foreach ($inscriptions as $inscription) {
            // Usar el campo full_name de la inscripción
            $inscriptionName = $inscription->full_name;
            
            $normalizedInscriptionName = NameMatcher::normalizeName($inscriptionName);
            
            // Usar similar_text para porcentaje de coincidencia
            similar_text($normalizedExcelName, $normalizedInscriptionName, $percent);
            
            Log::info('Comparando nombres:', [
                'excel' => $fullName,
                'normalized_excel' => $normalizedExcelName,
                'inscription_id' => $inscription->id,
                'inscription_name' => $inscriptionName,
                'normalized_inscription' => $normalizedInscriptionName,
                'similarity' => $percent
            ]);
            
            // Considerar match si es mayor al 80% y es el mejor encontrado
            if ($percent > $highestSimilarity && $percent > 80) {
                $highestSimilarity = $percent;
                $bestMatch = $inscription;
                
                // Si encontramos un match perfecto, salir del bucle
                if ($percent == 100) {
                    break;
                }
            }
        }

        if ($bestMatch) {
            Log::info('Mejor coincidencia encontrada:', [
                'excel_name' => $fullName,
                'inscription_id' => $bestMatch->id,
                'inscription_name' => $bestMatch->full_name,
                'similarity' => $highestSimilarity
            ]);
        } else {
            Log::warning('No se encontró coincidencia para:', [
                'excel_name' => $fullName,
                'normalized' => $normalizedExcelName
            ]);
        }

        return $bestMatch;
    }

    /**
     * Mostrar el resumen de calificaciones para un módulo.
     */
    public function summary(Program $program, Module $module)
    {
        $grades = Grade::where('module_id', $module->id)
            ->orderBy('last_name')
            ->orderBy('name')
            ->get();

        $approvedCount = $grades->where('approved', true)->count();
        $totalCount = $grades->count();
        $approvalRate = $totalCount > 0 ? ($approvedCount / $totalCount) * 100 : 0;

        return view('grades.summary', compact('program', 'module', 'grades', 'approvedCount', 'totalCount', 'approvalRate'));
    }

    /**
     * Mostrar el resumen de calificaciones para un programa.
     */
    public function programSummary(Program $program)
    {
        // Cargar solo los módulos seleccionados (tabla pivot)
        $modules = $program->selectedModules()->with(['grades', 'teacher'])->get();
        
        $moduleStats = [];
        foreach ($modules as $module) {
            $grades = $module->grades;
            $approvedCount = $grades->where('approved', true)->count();
            $totalCount = $grades->count();
            $approvalRate = $totalCount > 0 ? ($approvedCount / $totalCount) * 100 : 0;
            $averageGrade = $totalCount > 0 ? $grades->avg('grade') : 0;
            
            $moduleStats[$module->id] = [
                'module' => $module,
                'approvedCount' => $approvedCount,
                'totalCount' => $totalCount,
                'approvalRate' => $approvalRate,
                'averageGrade' => $averageGrade,
            ];
        }

        // Cargar participantes e inscripciones (excluyendo Preinscrito)
        $inscriptions = $program->inscriptions()->where('external_inscription_status', '!=', 'Preinscrito')->get();
        $totalParticipants = $inscriptions->count();
        $activeParticipants = $inscriptions->where('participant_status', 'VIGENTE')->count();
        
        // Contar requisitos completos basándose en los campos individuales
        // Requisitos de Inscripción: has_degree_title, has_academic_diploma, has_identity_card, has_birth_certificate
        $inscriptionRequirementsMet = $inscriptions->filter(function($inscription) {
            return ($inscription->has_degree_title ?? false) &&
                   ($inscription->has_academic_diploma ?? false) &&
                   ($inscription->has_identity_card ?? false) &&
                   ($inscription->has_birth_certificate ?? false);
        })->count();
        
        // Requisitos de Titulación: has_legalized_degree_title, has_legalized_academic_diploma, has_identity_card_graduation, has_birth_certificate_original, has_photos
        $graduationRequirementsMet = $inscriptions->filter(function($inscription) {
            return ($inscription->has_legalized_degree_title ?? false) &&
                   ($inscription->has_legalized_academic_diploma ?? false) &&
                   ($inscription->has_identity_card_graduation ?? false) &&
                   ($inscription->has_birth_certificate_original ?? false) &&
                   ($inscription->has_photos ?? false);
        })->count();

        // Cargar datos de participantes desde inscripciones
        $participants = $inscriptions->map(function($inscription) {
            // Primero verificar si los campos ya están separados
            if ($inscription->name || $inscription->paternal_surname || $inscription->maternal_surname) {
                $name = $inscription->name ?? '';
                $paternal = $inscription->paternal_surname ?? '';
                $maternal = $inscription->maternal_surname ?? '';
                $fullName = trim($inscription->full_name ?? '');
            } else {
                // Si no, hacer el parsing automático
                // Obtener nombre completo original
                $fullNameRaw = trim($inscription->full_name ?? '');
                
                // Mejorar preg_replace: agregar espacios en múltiples patrones
                // 1. Entre minúscula y mayúscula: "SoledadArias" -> "Soledad Arias"
                // 2. Múltiples espacios -> un espacio
                $fullName = preg_replace('/([a-z])([A-Z])/', '$1 $2', $fullNameRaw);
                $fullName = preg_replace('/\s+/', ' ', trim($fullName)); // Normalizar espacios
                
                // Separar por espacios
                $nameParts = array_values(array_filter(explode(' ', $fullName)));
                
                $name = '';
                $paternal = '';
                $maternal = '';
                
                $count = count($nameParts);
                
                // Estrategia: Los últimos 2 elementos son típicamente apellidos
                // El resto son nombres
                if ($count === 0) {
                    $name = '';
                } elseif ($count === 1) {
                    // Una sola palabra - es el nombre
                    $name = $nameParts[0];
                } elseif ($count === 2) {
                    // Dos palabras - primera es nombre, segunda es apellido paterno
                    $name = $nameParts[0];
                    $paternal = $nameParts[1];
                } else {
                    // 3 o más palabras - los últimos 2 son apellidos, el resto es nombre
                    $maternal = $nameParts[$count - 1];
                    $paternal = $nameParts[$count - 2];
                    $name = implode(' ', array_slice($nameParts, 0, $count - 2));
                }
            }
            
            return (object)[
                'id' => $inscription->id,
                'full_name' => $fullName,
                'name' => trim($name) ?: $fullName, // Si el parsing resulta vacío, usar completo
                'ci' => $inscription->ci ?? '',
                'location' => $inscription->location ?? '',
                'paternal_surname' => trim($paternal),
                'maternal_surname' => $maternal,
                'phone' => $inscription->phone,
                'email' => $inscription->email,
                'gender' => $inscription->gender,
                'university' => $inscription->university ? ($inscription->university->initials . ' - ' . $inscription->university->name) : '-',
                'profession' => $inscription->profession?->name ?? '-',
                'birth_date' => $inscription->birth_date,
                'residence' => $inscription->residence,
                'advisor' => $inscription->external_advisor_name ?? $inscription->advisor ?? '-',
                'has_degree_title' => $inscription->has_degree_title ?? false,
                'has_academic_diploma' => $inscription->has_academic_diploma ?? false,
                'has_identity_card' => $inscription->has_identity_card ?? false,
                'has_birth_certificate' => $inscription->has_birth_certificate ?? false,
                'has_legalized_degree_title' => $inscription->has_legalized_degree_title ?? false,
                'has_legalized_academic_diploma' => $inscription->has_legalized_academic_diploma ?? false,
                'has_identity_card_graduation' => $inscription->has_identity_card_graduation ?? false,
                'has_birth_certificate_original' => $inscription->has_birth_certificate_original ?? false,
                'has_photos' => $inscription->has_photos ?? false,
                'graduation_procedure_type' => $inscription->graduation_procedure_type ?? '',
                'has_monograph_elaboration' => $inscription->has_monograph_elaboration ?? false,
                'has_monograph_received' => $inscription->has_monograph_received ?? false,
                'has_graduation_procedure' => $inscription->has_graduation_procedure ?? false,
                'has_graduation_received' => $inscription->has_graduation_received ?? false,
                'has_documents_delivered' => $inscription->has_documents_delivered ?? false,
                'has_diplomas_delivered' => $inscription->has_diplomas_delivered ?? false,
                'participant_status' => $inscription->participant_status ?? '',
                'participant_justification' => $inscription->participant_justification ?? '',
                'payment_plan' => $inscription->payment_plan ?? '',
                'internal_accounting_billing_status' => $inscription->internal_accounting_billing_status ?? '',
                'internal_accounting_amount_due' => $inscription->internal_accounting_amount_due ?? 0,
                'internal_accounting_graduation_payment' => $inscription->internal_accounting_graduation_payment ?? '',
                'external_accounting_registration' => $inscription->external_accounting_registration ?? '',
                'external_accounting_enrollment' => $inscription->external_accounting_enrollment ?? '',
                'external_accounting_tuition' => $inscription->external_accounting_tuition ?? '',
                'external_accounting_degrees' => $inscription->external_accounting_degrees ?? '',
            ];
        })->all();

        // Cargar todos los módulos disponibles (que no estén en la tabla pivot)
        $selectedModuleIds = $program->selectedModules()->pluck('modules.id')->toArray();
        $availableModules = $program->modules()->whereNotIn('id', $selectedModuleIds)->get();

        // Cargar calificaciones de cada participante por módulo
        $participantGrades = [];
        foreach ($inscriptions as $inscription) {
            $participantGrades[$inscription->id] = [];
            foreach ($modules as $module) {
                $grade = Grade::where('inscription_id', $inscription->id)
                             ->where('module_id', $module->id)
                             ->first();
                $participantGrades[$inscription->id][$module->id] = $grade?->grade ?? '-';
            }
        }

        return view('grades.program_summary', compact('program', 'modules', 'moduleStats', 'totalParticipants', 'activeParticipants', 'inscriptionRequirementsMet', 'graduationRequirementsMet', 'availableModules', 'participants', 'participantGrades'));
    }

    /**
     * Actualizar una calificación individual
     */
    public function updateGrade(Request $request, Program $program, Module $module, Grade $grade)
    {
        $validated = $request->validate([
            'grade' => 'required|numeric|min:0|max:100',
            'approval_type' => 'required|in:regular,recuperatorio,tutoria'
        ]);

        $grade->update([
            'grade' => $validated['grade'],
            'approval_type' => $validated['approval_type'],
            'approved' => $validated['grade'] >= 71
        ]);

        // Detectar si es una petición AJAX
        if ($request->ajax() || $request->wantsJson()) {
            return response()->json([
                'success' => true,
                'message' => 'Calificación actualizada correctamente.',
                'data' => [
                    'grade' => $grade->grade,
                    'approval_type' => $grade->approval_type,
                    'approved' => $grade->approved
                ]
            ]);
        }

        return redirect()->back()->with('success', 'Calificación actualizada correctamente.');
    }

    
    /**
     * Agregar un módulo a la tabla del programa
     */
    public function addModuleToProgram(Request $request, Program $program)
    {
        $validated = $request->validate([
            'module_id' => 'required|exists:modules,id',
        ]);

        // Obtener el siguiente orden
        $maxOrder = $program->selectedModules()->max('program_module.order') ?? -1;
        
        $program->selectedModules()->attach($validated['module_id'], [
            'order' => $maxOrder + 1,
        ]);

        return redirect()->back()->with('success', 'Módulo agregado correctamente.');
    }

    /**
     * Remover un módulo de la tabla del programa
     */
    public function removeModuleFromProgram(Program $program, Module $module)
    {
        $program->selectedModules()->detach($module->id);

        return redirect()->back()->with('success', 'Módulo removido correctamente.');
    }

    /**
     * Reordenar módulos en la tabla del programa
     */
    public function reorderProgramModules(Request $request, Program $program)
    {
        $validated = $request->validate([
            'module_id' => 'required|exists:modules,id',
            'direction' => 'required|in:up,down',
        ]);

        $currentModule = $program->selectedModules()->find($validated['module_id']);
        
        if (!$currentModule) {
            return redirect()->back()->with('error', 'Módulo no encontrado.');
        }

        $currentOrder = $currentModule->pivot->order;
        $allModules = $program->selectedModules()->orderByPivot('order')->get();
        
        // Encontrar el índice actual en la colección
        $currentIndex = $allModules->search(function($m) use ($validated) {
            return $m->id === (int)$validated['module_id'];
        });

        if ($validated['direction'] === 'up' && $currentIndex > 0) {
            // Swap con el módulo anterior
            $previousModule = $allModules[$currentIndex - 1];
            $tempOrder = $currentModule->pivot->order;
            
            $program->selectedModules()->updateExistingPivot($currentModule->id, ['order' => $previousModule->pivot->order]);
            $program->selectedModules()->updateExistingPivot($previousModule->id, ['order' => $tempOrder]);
            
        } elseif ($validated['direction'] === 'down' && $currentIndex < count($allModules) - 1) {
            // Swap con el módulo siguiente
            $nextModule = $allModules[$currentIndex + 1];
            $tempOrder = $currentModule->pivot->order;
            
            $program->selectedModules()->updateExistingPivot($currentModule->id, ['order' => $nextModule->pivot->order]);
            $program->selectedModules()->updateExistingPivot($nextModule->id, ['order' => $tempOrder]);
        }

        return redirect()->back()->with('success', 'Orden actualizado correctamente.');
    }

    /**
     * Actualizar nota de un módulo para un participante
     */
    public function updateModuleGrade(Request $request, Program $program, Inscription $inscription)
    {
        try {
            $validated = $request->validate([
                'module_id' => 'required|integer|exists:modules,id',
                'grade' => 'required|numeric|min:0|max:100'
            ]);

            $grade = Grade::where('inscription_id', $inscription->id)
                ->where('module_id', $validated['module_id'])
                ->first();

            if (!$grade) {
                return response()->json([
                    'success' => false,
                    'message' => 'Nota no encontrada'
                ], 404);
            }

            $grade->update([
                'grade' => $validated['grade'],
                'approved' => $validated['grade'] >= 71
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Nota actualizada correctamente',
                'data' => [
                    'grade' => $grade->grade,
                    'approved' => $grade->approved
                ]
            ]);
        } catch (\Exception $e) {
            Log::error('Error al actualizar nota: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Error al actualizar la nota'
            ], 500);
        }
    }

    /**
     * Actualizar carga horaria de un módulo
     */
    public function updateModuleHours(Request $request, Program $program, Module $module)
    {
        $validated = $request->validate([
            'field' => 'required|in:presential_hours,non_presential_hours',
            'value' => 'required|numeric|min:0',
        ]);

        $field = $validated['field'];
        $value = $validated['value'];

        $module->$field = $value;
        $module->save();

        return response()->json(['success' => true, 'message' => 'Horas actualizadas correctamente']);
    }

    /**
     * Actualizar link de Moodle del programa
     */
    public function updateMoodleLink(Request $request, Program $program)
    {
        $validated = $request->validate([
            'moodle_link' => 'nullable|string|max:500',
        ]);

        $program->moodle_link = $validated['moodle_link'] ?? null;
        $program->save();

        return response()->json(['success' => true, 'message' => 'Link de Moodle actualizado correctamente']);
    }

    /**
     * Actualizar ciudad de residencia de un participante
     */
    public function updateParticipantResidence(Request $request, Program $program, Inscription $inscription)
    {
        $validated = $request->validate([
            'residence' => 'nullable|string|max:255',
        ]);

        $inscription->residence = $validated['residence'] ?? null;
        $inscription->save();

        return response()->json(['success' => true, 'message' => 'Residencia actualizada correctamente']);
    }

    /**
     * Actualizar nombre o apellidos de un participante
     */
    public function updateParticipantNameField(Request $request, Program $program, Inscription $inscription)
    {
        $validated = $request->validate([
            'field_type' => 'required|in:name,paternal_surname,maternal_surname',
            'value' => 'required_if:field_type,name|string|max:255',
        ]);

        $fieldType = $validated['field_type'];
        $value = trim($validated['value'] ?? '');

        // Actualizar directamente en la BD
        $inscription->{$fieldType} = $value;
        $inscription->save();

        return response()->json(['success' => true, 'message' => 'Campo actualizado correctamente']);
    }

    /**
     * Actualizar un requisito de inscripción del participante
     */
    public function updateParticipantRequirement(Request $request, Program $program, Inscription $inscription)
    {
        $requirement = $request->input('requirement');
        
        // Campos de texto (no booleanos)
        $textRequirements = [
            'participant_status', 'participant_justification', 'payment_plan',
            'internal_accounting_billing_status', 'internal_accounting_amount_due',
            'internal_accounting_graduation_payment',
            'external_accounting_registration', 'external_accounting_enrollment',
            'external_accounting_tuition', 'external_accounting_degrees',
            'has_pre_defense', 'has_defense', 'has_defense_accounting_status',
            'graduation_procedure_type'
        ];

        if (in_array($requirement, $textRequirements)) {
            $validated = $request->validate([
                'requirement' => 'required|in:participant_status,participant_justification,payment_plan,internal_accounting_billing_status,internal_accounting_amount_due,internal_accounting_graduation_payment,external_accounting_registration,external_accounting_enrollment,external_accounting_tuition,external_accounting_degrees,has_pre_defense,has_defense,has_defense_accounting_status,graduation_procedure_type',
                'value' => 'nullable|string|max:255',
            ]);
            $inscription->{$validated['requirement']} = $validated['value'];
        } else {
            $validated = $request->validate([
                'requirement' => 'required|in:has_degree_title,has_academic_diploma,has_identity_card,has_birth_certificate,has_legalized_degree_title,has_legalized_academic_diploma,has_identity_card_graduation,has_birth_certificate_original,has_photos,has_monograph_elaboration,has_monograph_received,has_degree_work_presentation,has_tutor_approval_report,has_graduation_procedure,has_graduation_received,has_documents_delivered,has_diplomas_delivered',
                'value' => 'required',
            ]);
            // Convertir valores a boolean (puede ser "true", "false", 1, 0, true, false)
            $boolValue = filter_var($validated['value'], FILTER_VALIDATE_BOOLEAN, FILTER_NULL_ON_FAILURE);
            if ($boolValue === null) {
                return response()->json(['success' => false, 'message' => 'Valor booleano inválido'], 400);
            }
            $inscription->{$validated['requirement']} = $boolValue;
        }

        $inscription->save();

        return response()->json(['success' => true, 'message' => 'Requisito actualizado correctamente']);
    }

    /**
     * Generar reporte XLSX del resumen del programa
     */
    public function programSummaryReport(Program $program)
    {
        // Cargar datos igual que en programSummary
        $modules = $program->selectedModules()->with(['grades', 'teacher'])->get();
        
        // Cargar participantes e inscripciones
        $inscriptions = $program->inscriptions()->where('external_inscription_status', '!=', 'Preinscrito')->get();
        $participantGrades = [];
        foreach ($inscriptions as $inscription) {
            $participantGrades[$inscription->id] = [];
            foreach ($modules as $module) {
                $grade = Grade::where('inscription_id', $inscription->id)
                             ->where('module_id', $module->id)
                             ->first();
                $participantGrades[$inscription->id][$module->id] = $grade?->grade ?? '-';
            }
        }

        $participants = $inscriptions->map(function($inscription) {
            // Primero verificar si los campos ya están separados
            if ($inscription->name || $inscription->paternal_surname || $inscription->maternal_surname) {
                $name = $inscription->name ?? '';
                $paternal = $inscription->paternal_surname ?? '';
                $maternal = $inscription->maternal_surname ?? '';
                $fullName = trim($inscription->full_name ?? '');
            } else {
                // Si no, hacer el parsing automático
                // Obtener nombre completo original
                $fullNameRaw = trim($inscription->full_name ?? '');
                
                // Mejorar preg_replace: agregar espacios en múltiples patrones
                // 1. Entre minúscula y mayúscula: "SoledadArias" -> "Soledad Arias"
                // 2. Múltiples espacios -> un espacio
                $fullName = preg_replace('/([a-z])([A-Z])/', '$1 $2', $fullNameRaw);
                $fullName = preg_replace('/\s+/', ' ', trim($fullName)); // Normalizar espacios
                
                // Separar por espacios
                $nameParts = array_values(array_filter(explode(' ', $fullName)));
                
                $name = '';
                $paternal = '';
                $maternal = '';
                
                $count = count($nameParts);
                
                // Log para debugging
                Log::info('Parsing nombre', [
                    'full_name' => $inscription->full_name,
                    'cleaned_full_name' => $fullName,
                    'name_parts' => $nameParts,
                    'count' => $count,
                ]);
                
                // Estrategia: Los últimos 2 elementos son típicamente apellidos
                // El resto son nombres
                if ($count === 0) {
                    $name = '';
                } elseif ($count === 1) {
                    // Una sola palabra - es el nombre
                    $name = $nameParts[0];
                } elseif ($count === 2) {
                    // Dos palabras - primera es nombre, segunda es apellido paterno
                    $name = $nameParts[0];
                    $paternal = $nameParts[1];
                } else {
                    // 3 o más palabras - los últimos 2 son apellidos, el resto es nombre
                    $maternal = $nameParts[$count - 1];
                    $paternal = $nameParts[$count - 2];
                    $name = implode(' ', array_slice($nameParts, 0, $count - 2));
                }
            }
            
            return (object)[
                'id' => $inscription->id,
                'full_name' => $fullName,
                'name' => trim($name) ?: $fullName,
                'ci' => $inscription->ci ?? '',
                'location' => $inscription->location ?? '',
                'paternal_surname' => trim($paternal),
                'maternal_surname' => $maternal,
                'phone' => $inscription->phone,
                'email' => $inscription->email,
                'gender' => $inscription->gender,
                'university' => $inscription->university ? ($inscription->university->initials . ' - ' . $inscription->university->name) : '-',
                'profession' => $inscription->profession?->name ?? '-',
                'birth_date' => $inscription->birth_date,
                'residence' => $inscription->residence,
                'advisor' => $inscription->external_advisor_name ?? $inscription->advisor ?? '-',
                'has_degree_title' => $inscription->has_degree_title ?? false,
                'has_academic_diploma' => $inscription->has_academic_diploma ?? false,
                'has_identity_card' => $inscription->has_identity_card ?? false,
                'has_birth_certificate' => $inscription->has_birth_certificate ?? false,
                'has_legalized_degree_title' => $inscription->has_legalized_degree_title ?? false,
                'has_legalized_academic_diploma' => $inscription->has_legalized_academic_diploma ?? false,
                'has_identity_card_graduation' => $inscription->has_identity_card_graduation ?? false,
                'has_birth_certificate_original' => $inscription->has_birth_certificate_original ?? false,
                'has_photos' => $inscription->has_photos ?? false,
                'graduation_procedure_type' => $inscription->graduation_procedure_type ?? '',
                'has_monograph_elaboration' => $inscription->has_monograph_elaboration ?? false,
                'has_monograph_received' => $inscription->has_monograph_received ?? false,
                'has_degree_work_presentation' => $inscription->has_degree_work_presentation ?? false,
                'has_tutor_approval_report' => $inscription->has_tutor_approval_report ?? false,
                'has_pre_defense' => $inscription->has_pre_defense ?? '',
                'has_defense' => $inscription->has_defense ?? '',
                'has_defense_accounting_status' => $inscription->has_defense_accounting_status ?? '',
                'has_graduation_procedure' => $inscription->has_graduation_procedure ?? false,
                'has_graduation_received' => $inscription->has_graduation_received ?? false,
                'has_documents_delivered' => $inscription->has_documents_delivered ?? false,
                'has_diplomas_delivered' => $inscription->has_diplomas_delivered ?? false,
                'participant_status' => $inscription->participant_status ?? '',
                'participant_justification' => $inscription->participant_justification ?? '',
                'payment_plan' => $inscription->payment_plan ?? '',
                'internal_accounting_billing_status' => $inscription->internal_accounting_billing_status ?? '',
                'internal_accounting_amount_due' => $inscription->internal_accounting_amount_due ?? 0,
                'internal_accounting_graduation_payment' => $inscription->internal_accounting_graduation_payment ?? '',
                'external_accounting_registration' => $inscription->external_accounting_registration ?? '',
                'external_accounting_enrollment' => $inscription->external_accounting_enrollment ?? '',
                'external_accounting_tuition' => $inscription->external_accounting_tuition ?? '',
                'external_accounting_degrees' => $inscription->external_accounting_degrees ?? '',
            ];
        })->all();

        // Crear spreadsheet
        $spreadsheet = new \PhpOffice\PhpSpreadsheet\Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();
        $sheet->setTitle('Resumen Programa');

        // Calcular totales
        $totalParticipants = $inscriptions->count();
        $totalActive = $inscriptions->where('participant_status', 'Activo')->count();
        $totalCompleteInscription = $inscriptions->where('has_degree_title', true)
            ->where('has_legalized_degree_title', true)
            ->where('has_identity_card', true)
            ->where('has_birth_certificate', true)->count();
        $totalCompleteDegree = $inscriptions->where('has_identity_card_graduation', true)
            ->where('has_birth_certificate_original', true)
            ->where('has_photos', true)->count();
        
        // Determinar tipo basado en el nombre del programa
        $programType = 'Diplomado';
        if (stripos($program->name, 'MAESTRÍA') === 0 || stripos($program->name, 'MAESTRIA') === 0) {
            $programType = 'Maestría';
        }
        
        // Obtener área de ExternalPostgraduate
        $programArea = 'No disponible';
        try {
            if ($program->postgraduate_id) {
                $postgrad = \App\Models\External\ExternalPostgraduate::where('id_posgrado', $program->postgraduate_id)->first();
                if ($postgrad && $postgrad->area_posgrado) {
                    $programArea = $postgrad->area_posgrado;
                }
            }
        } catch (\Exception $e) {
            Log::error("Error obteniendo área para programa {$program->code}: " . $e->getMessage());
        }
        
        // Fila 2: Nombre programa (A-D merged) | ID PROGRAMA (E) | ID (F)
        $sheet->setCellValueByColumnAndRow(1, 2, $program->name);
        $sheet->mergeCellsByColumnAndRow(1, 2, 4, 2);
        $sheet->getStyleByColumnAndRow(1, 2)->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_LEFT);
        
        $sheet->setCellValueByColumnAndRow(5, 2, 'ID PROGRAMA');
        $sheet->getStyleByColumnAndRow(5, 2)->getFont()->setBold(true);
        
        // Build program code with prefix based on type
        $programCode = $program->code;
        if (str_starts_with($program->name, 'Diplomado')) {
            $programCode = 'D-' . $program->code;
        } elseif (str_starts_with($program->name, 'Maestría')) {
            $programCode = 'M-' . $program->code;
        } elseif (str_starts_with($program->name, 'Curso')) {
            $programCode = 'C-' . $program->code;
        }
        $sheet->setCellValueByColumnAndRow(6, 2, $programCode);
        
        // Fila 3: Tipo (A) | [tipo] (B)
        $sheet->setCellValueByColumnAndRow(1, 3, 'Tipo');
        $sheet->getStyleByColumnAndRow(1, 3)->getFont()->setBold(true);
        
        $sheet->setCellValueByColumnAndRow(2, 3, $programType);
        
        // Fila 4: AREA (A) | [area] (B)
        $sheet->setCellValueByColumnAndRow(1, 4, 'AREA');
        $sheet->getStyleByColumnAndRow(1, 4)->getFont()->setBold(true);
        
        $sheet->setCellValueByColumnAndRow(2, 4, $programArea);
        
        // Fila 5: Fechas y Link Moodle
        $sheet->setCellValueByColumnAndRow(2, 5, 'Fecha de Inicio del programa');
        $sheet->getStyleByColumnAndRow(2, 5)->getFont()->setBold(true);
        
        $sheet->setCellValueByColumnAndRow(3, 5, $program->start_date ? \Carbon\Carbon::parse($program->start_date)->format('d/m/Y') : '');
        
        $sheet->setCellValueByColumnAndRow(4, 5, 'Fecha de finalizacion del programa');
        $sheet->getStyleByColumnAndRow(4, 5)->getFont()->setBold(true);
        
        $sheet->setCellValueByColumnAndRow(5, 5, $program->finalization_date ? \Carbon\Carbon::parse($program->finalization_date)->format('d/m/Y') : '');
        $sheet->mergeCellsByColumnAndRow(5, 5, 6, 5);
        
        $sheet->setCellValueByColumnAndRow(7, 5, 'Link Moodle');
        $sheet->getStyleByColumnAndRow(7, 5)->getFont()->setBold(true);
        
        $sheet->setCellValueByColumnAndRow(8, 5, $program->moodle_link ?? '');
        $sheet->mergeCellsByColumnAndRow(8, 5, 10, 5);
        if ($program->moodle_link) {
            $sheet->getHyperlink('H5')->setUrl($program->moodle_link);
            $sheet->getStyleByColumnAndRow(8, 5)->getFont()->setUnderline(\PhpOffice\PhpSpreadsheet\Style\Font::UNDERLINE_SINGLE);
        }
        
        // Fila 6: Headers de módulos
        $sheet->setCellValueByColumnAndRow(2, 6, 'Módulo');
        $sheet->getStyleByColumnAndRow(2, 6)->getFont()->setBold(true);
        
        $sheet->setCellValueByColumnAndRow(3, 6, 'Nombre de Módulo o Asignatura');
        $sheet->getStyleByColumnAndRow(3, 6)->getFont()->setBold(true);
        
        $sheet->setCellValueByColumnAndRow(4, 6, 'Carga Horaria Presencial');
        $sheet->getStyleByColumnAndRow(4, 6)->getFont()->setBold(true);
        
        $sheet->setCellValueByColumnAndRow(5, 6, 'Carga Horaria No Presencial');
        $sheet->getStyleByColumnAndRow(5, 6)->getFont()->setBold(true);
        
        $sheet->setCellValueByColumnAndRow(6, 6, 'Nombre Docente');
        $sheet->getStyleByColumnAndRow(6, 6)->getFont()->setBold(true);
        
        $sheet->setCellValueByColumnAndRow(7, 6, 'Profesión');
        $sheet->getStyleByColumnAndRow(7, 6)->getFont()->setBold(true);
        
        $sheet->setCellValueByColumnAndRow(8, 6, 'Fechas Inicio y Fin del Módulo');
        $sheet->getStyleByColumnAndRow(8, 6)->getFont()->setBold(true);
        
        $sheet->setCellValueByColumnAndRow(9, 6, 'Hoja de Vida');
        $sheet->getStyleByColumnAndRow(9, 6)->getFont()->setBold(true);
        
        $sheet->setCellValueByColumnAndRow(10, 6, 'Curriculum Documentado');
        $sheet->getStyleByColumnAndRow(10, 6)->getFont()->setBold(true);
        
        // Columna K (11) en blanco
        
        $sheet->setCellValueByColumnAndRow(12, 6, 'Total Participantes');
        $sheet->getStyleByColumnAndRow(12, 6)->getFont()->setBold(true);
        
        $sheet->setCellValueByColumnAndRow(13, 6, 'Participantes Vigentes');
        $sheet->getStyleByColumnAndRow(13, 6)->getFont()->setBold(true);
        
        $sheet->setCellValueByColumnAndRow(14, 6, 'Requisitos de Inscripción Completos');
        $sheet->getStyleByColumnAndRow(14, 6)->getFont()->setBold(true);
        
        $sheet->setCellValueByColumnAndRow(15, 6, 'Requisitos de Titulación Completos');
        $sheet->getStyleByColumnAndRow(15, 6)->getFont()->setBold(true);
        
        // Aplicar color gris a celdas específicas
        $grayColor = 'FFCCCCCC';
        
        // E2: ID PROGRAMA label
        $sheet->getStyleByColumnAndRow(5, 2)->getFill()->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)->getStartColor()->setARGB($grayColor);
        
        // A3: Tipo label
        $sheet->getStyleByColumnAndRow(1, 3)->getFill()->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)->getStartColor()->setARGB($grayColor);
        
        // A4: AREA label
        $sheet->getStyleByColumnAndRow(1, 4)->getFill()->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)->getStartColor()->setARGB($grayColor);
        
        // B5, D5, G5: labels de Fila 5
        $sheet->getStyleByColumnAndRow(2, 5)->getFill()->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)->getStartColor()->setARGB($grayColor);
        $sheet->getStyleByColumnAndRow(4, 5)->getFill()->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)->getStartColor()->setARGB($grayColor);
        $sheet->getStyleByColumnAndRow(7, 5)->getFill()->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)->getStartColor()->setARGB($grayColor);
        
        // B6-I6: Headers de módulos
        for ($col = 2; $col <= 9; $col++) {
            $sheet->getStyleByColumnAndRow($col, 6)->getFill()->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)->getStartColor()->setARGB($grayColor);
        }
        
        // L6-O6: Headers de totales
        for ($col = 12; $col <= 15; $col++) {
            $sheet->getStyleByColumnAndRow($col, 6)->getFill()->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)->getStartColor()->setARGB($grayColor);
        }
        
        // Filas de módulos (7 en adelante)
        $row = 7;
        
        // Datos de totales solo en fila 7
        // Columna L: total participantes
        $sheet->setCellValueByColumnAndRow(12, $row, $totalParticipants);
        
        // Columna M: participantes vigentes
        $sheet->setCellValueByColumnAndRow(13, $row, $totalActive);
        
        // Columna N: requisitos de inscripción completos
        $sheet->setCellValueByColumnAndRow(14, $row, $totalCompleteInscription);
        
        // Columna O: requisitos de titulación completos
        $sheet->setCellValueByColumnAndRow(15, $row, $totalCompleteDegree);
        
        // Filas de módulos
        foreach ($modules as $index => $module) {
            // Columna B: número de módulo
            $sheet->setCellValueByColumnAndRow(2, $row, ($index + 1));
            
            // Columna C: nombre del módulo
            $sheet->setCellValueByColumnAndRow(3, $row, $module->name ?? '');
            
            // Columna D: carga horaria presencial
            $sheet->setCellValueByColumnAndRow(4, $row, $module->presential_hours ?? '-');
            
            // Columna E: carga horaria no presencial
            $sheet->setCellValueByColumnAndRow(5, $row, $module->non_presential_hours ?? '-');
            
            // Columna F: nombre docente
            $sheet->setCellValueByColumnAndRow(6, $row, $module->teacher?->name ?? '-');
            
            // Columna G: profesión
            $sheet->setCellValueByColumnAndRow(7, $row, $module->teacher?->profession ?? '-');
            
            // Columna H: fechas de inicio - fin del módulo
            $startDate = $module->start_date ? \Carbon\Carbon::parse($module->start_date)->format('d/m/Y') : '';
            $endDate = $module->finalization_date ? \Carbon\Carbon::parse($module->finalization_date)->format('d/m/Y') : '';
            $dateRange = $startDate && $endDate ? "$startDate - $endDate" : '';
            $sheet->setCellValueByColumnAndRow(8, $row, $dateRange);
            
            // Columna I: hoja de vida (vacío)
            
            // Columna J: curriculum documentado (vacío)
            
            // Alineación a izquierda sin color de fondo
            for ($col = 1; $col <= 14; $col++) {
                $sheet->getStyleByColumnAndRow($col, $row)->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_LEFT);
            }
            
            $row++;
        }
        
        // Tabla de participantes
        $row += 1;
        
        // Encabezados principales
        $colStart = 1;
        $mainHeaders = [
            ['name' => 'Datos Personales', 'colspan' => 15],
            ['name' => 'Notas', 'colspan' => count($modules) + 1],
            ['name' => 'Estado Participantes', 'colspan' => 3],
            ['name' => 'Requisitos de Inscripción', 'colspan' => 4],
            ['name' => 'Requisitos de Titulación', 'colspan' => 3],
            ['name' => 'Trabajo Final', 'colspan' => 7],
            ['name' => 'Estado de Titulación', 'colspan' => 4],
            ['name' => 'Contable Interno', 'colspan' => 4],
            ['name' => 'Contable Externo', 'colspan' => 4]
        ];
        
        foreach ($mainHeaders as $header) {
            $endCol = $colStart + $header['colspan'] - 1;
            $sheet->setCellValueByColumnAndRow($colStart, $row, $header['name']);
            $sheet->getStyleByColumnAndRow($colStart, $row)->getFont()->setBold(true);
            $sheet->getStyleByColumnAndRow($colStart, $row)->getFill()->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)
                ->getStartColor()->setARGB('FFCCCCCC');
            $sheet->getStyleByColumnAndRow($colStart, $row)->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER);
            
            if ($header['colspan'] > 1) {
                $sheet->mergeCellsByColumnAndRow($colStart, $row, $endCol, $row);
            }
            $colStart = $endCol + 1;
        }
        
        // Sub-encabezados (fila 5)
        $row++;
        $subHeaders = [
            'N°', 'Carnet', 'Extensión', 'Nombre', 'Ap. Paterno', 'Ap. Materno', 
            'Celular', 'Email', 'Género', 'Universidad', 'Profesión', 'Fecha Nacimiento', 'Edad',
            'Residencia', 'Asesor'
        ];
        
        // Agregar sub-encabezados de módulos
        foreach ($modules as $index => $module) {
            $subHeaders[] = 'Mod. ' . ($index + 1);
        }
        $subHeaders[] = 'Promedio';
        
        // Agregar sub-encabezados de requisitos
        $subHeaders = array_merge($subHeaders, [
            'Estado', 'Observación', 'Tipo Trámite',
            'Titulo', 'Titulo Legalizado', 'CI', 'Cert. Nacimiento',
            'CI Graduación', 'Cert. Original', 'Fotos',
            'Monografía Elaboración', 'Monografía Recibida', 'Pres. Trabajo Grado', 'Informe Tutor', 'Predefensa', 'Defensa', 'Est. Contable Defensa',
            'Tramite', 'Titulados', 'Entrega Documentos', 'Entrega Diplomas',
            'Plan', 'Est. Cobranzas', 'Saldo por Cobrar', 'Pago Titulación',
            'Inscripción', 'Matrícula', 'Colegiatura', 'Titulaciones'
        ]);

        // Escribir sub-encabezados
        foreach ($subHeaders as $index => $header) {
            $sheet->setCellValueByColumnAndRow($index + 1, $row, $header);
            $sheet->getStyleByColumnAndRow($index + 1, $row)->getFont()->setBold(true);
            $sheet->getStyleByColumnAndRow($index + 1, $row)->getFill()->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)
                ->getStartColor()->setARGB('FFD3D3D3');
            $sheet->getStyleByColumnAndRow($index + 1, $row)->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER);
        }

        // Escribir datos
        $row++;
        foreach ($participants as $index => $participant) {
            $colIndex = 1;
            
            // Datos básicos
            $sheet->setCellValueByColumnAndRow($colIndex++, $row, $index + 1);
            $sheet->setCellValueByColumnAndRow($colIndex++, $row, $participant->ci);
            $sheet->setCellValueByColumnAndRow($colIndex++, $row, $participant->location);
            $sheet->setCellValueByColumnAndRow($colIndex++, $row, $participant->name);
            $sheet->setCellValueByColumnAndRow($colIndex++, $row, $participant->paternal_surname);
            $sheet->setCellValueByColumnAndRow($colIndex++, $row, $participant->maternal_surname);
            $sheet->setCellValueByColumnAndRow($colIndex++, $row, $participant->phone);
            $sheet->setCellValueByColumnAndRow($colIndex++, $row, $participant->email);
            $sheet->setCellValueByColumnAndRow($colIndex++, $row, $participant->gender);
            $sheet->setCellValueByColumnAndRow($colIndex++, $row, $participant->university);
            $sheet->setCellValueByColumnAndRow($colIndex++, $row, $participant->profession);
            $sheet->setCellValueByColumnAndRow($colIndex++, $row, $participant->birth_date ? $participant->birth_date->format('d/m/Y') : '');
            
            // Calcular edad
            $age = '';
            if ($participant->birth_date) {
                $age = \Carbon\Carbon::parse($participant->birth_date)->age;
            }
            $sheet->setCellValueByColumnAndRow($colIndex++, $row, $age);
            
            $sheet->setCellValueByColumnAndRow($colIndex++, $row, $participant->residence);
            $sheet->setCellValueByColumnAndRow($colIndex++, $row, $participant->advisor);

            // Notas de módulos
            foreach ($modules as $module) {
                $sheet->setCellValueByColumnAndRow($colIndex++, $row, $participantGrades[$participant->id][$module->id]);
            }

            // Promedio
            $grades = [];
            foreach ($modules as $module) {
                $grade = $participantGrades[$participant->id][$module->id];
                if ($grade !== '-' && is_numeric($grade)) {
                    $grades[] = (float)$grade;
                }
            }
            $average = count($grades) > 0 ? array_sum($grades) / count($grades) : '-';
            $sheet->setCellValueByColumnAndRow($colIndex++, $row, $average !== '-' ? round($average, 2) : '-');

            // Requisitos
            $sheet->setCellValueByColumnAndRow($colIndex++, $row, $participant->participant_status);
            $sheet->setCellValueByColumnAndRow($colIndex++, $row, $participant->participant_justification);
            $sheet->setCellValueByColumnAndRow($colIndex++, $row, $participant->graduation_procedure_type);
            $sheet->setCellValueByColumnAndRow($colIndex++, $row, $participant->has_degree_title ? 'Sí' : 'No');
            $sheet->setCellValueByColumnAndRow($colIndex++, $row, $participant->has_legalized_degree_title ? 'Sí' : 'No');
            $sheet->setCellValueByColumnAndRow($colIndex++, $row, $participant->has_identity_card ? 'Sí' : 'No');
            $sheet->setCellValueByColumnAndRow($colIndex++, $row, $participant->has_birth_certificate ? 'Sí' : 'No');
            $sheet->setCellValueByColumnAndRow($colIndex++, $row, $participant->has_identity_card_graduation ? 'Sí' : 'No');
            $sheet->setCellValueByColumnAndRow($colIndex++, $row, $participant->has_birth_certificate_original ? 'Sí' : 'No');
            $sheet->setCellValueByColumnAndRow($colIndex++, $row, $participant->has_photos ? 'Sí' : 'No');
            $sheet->setCellValueByColumnAndRow($colIndex++, $row, $participant->has_monograph_elaboration ? 'Sí' : 'No');
            $sheet->setCellValueByColumnAndRow($colIndex++, $row, $participant->has_monograph_received ? 'Sí' : 'No');
            $sheet->setCellValueByColumnAndRow($colIndex++, $row, $participant->has_degree_work_presentation ? 'Sí' : 'No');
            $sheet->setCellValueByColumnAndRow($colIndex++, $row, $participant->has_tutor_approval_report ? 'Sí' : 'No');
            $sheet->setCellValueByColumnAndRow($colIndex++, $row, $participant->has_pre_defense);
            $sheet->setCellValueByColumnAndRow($colIndex++, $row, $participant->has_defense);
            $sheet->setCellValueByColumnAndRow($colIndex++, $row, $participant->has_defense_accounting_status);
            $sheet->setCellValueByColumnAndRow($colIndex++, $row, $participant->has_graduation_procedure ? 'Sí' : 'No');
            $sheet->setCellValueByColumnAndRow($colIndex++, $row, $participant->has_graduation_received ? 'Sí' : 'No');
            $sheet->setCellValueByColumnAndRow($colIndex++, $row, $participant->has_documents_delivered ? 'Sí' : 'No');
            $sheet->setCellValueByColumnAndRow($colIndex++, $row, $participant->has_diplomas_delivered ? 'Sí' : 'No');
            $sheet->setCellValueByColumnAndRow($colIndex++, $row, $participant->payment_plan);
            $sheet->setCellValueByColumnAndRow($colIndex++, $row, $participant->internal_accounting_billing_status);
            $sheet->setCellValueByColumnAndRow($colIndex++, $row, $participant->internal_accounting_amount_due);
            $sheet->setCellValueByColumnAndRow($colIndex++, $row, $participant->internal_accounting_graduation_payment);
            $sheet->setCellValueByColumnAndRow($colIndex++, $row, $participant->external_accounting_registration);
            $sheet->setCellValueByColumnAndRow($colIndex++, $row, $participant->external_accounting_enrollment);
            $sheet->setCellValueByColumnAndRow($colIndex++, $row, $participant->external_accounting_tuition);
            $sheet->setCellValueByColumnAndRow($colIndex++, $row, $participant->external_accounting_degrees);

            $row++;
        }

        // Ajustar ancho de columnas
        foreach (range(1, $colIndex - 1) as $col) {
            $sheet->getColumnDimensionByColumn($col)->setAutoSize(true);
        }

        // Descargar archivo
        $writer = new \PhpOffice\PhpSpreadsheet\Writer\Xlsx($spreadsheet);
        $fileName = 'Reporte_' . Str::slug($program->name) . '_' . now()->format('Y-m-d_H-i-s') . '.xlsx';
        
        return response()->streamDownload(function() use ($writer) {
            $writer->save('php://output');
        }, $fileName, [
            'Content-Type' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
        ]);
    }
}

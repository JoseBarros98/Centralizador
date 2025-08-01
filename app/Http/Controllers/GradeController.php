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
            $spreadsheet = IOFactory::load($file->getPathname());
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
            $inscriptionName = $inscription->first_name . ' ' . 
                               $inscription->paternal_surname . ' ' . 
                               ($inscription->maternal_surname ?? '');
            
            $normalizedInscriptionName = NameMatcher::normalizeName($inscriptionName);
            
            // Usar similar_text para porcentaje de coincidencia
            similar_text($normalizedExcelName, $normalizedInscriptionName, $percent);
            
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
        $modules = $program->modules()->with('grades')->get();
        
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

        return view('grades.program_summary', compact('program', 'moduleStats'));
    }
}

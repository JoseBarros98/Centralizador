<?php

namespace App\Http\Controllers;

use App\Models\Program;
use App\Models\Participant;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use PhpOffice\PhpSpreadsheet\IOFactory;
use Illuminate\Support\Str;

class ParticipantController extends Controller
{
    public function index(Program $program)
    {
        $participants = $program->participants()->paginate(15);
        return view('participants.index', compact('program', 'participants'));
    }

    public function upload(Program $program)
    {
        return view('participants.upload', compact('program'));
    }

    public function process(Request $request, Program $program)
    {
        $request->validate([
            'excel_file' => 'required|file|mimes:xlsx,xls',
        ]);

        try {
            // Obtener el archivo
            $file = $request->file('excel_file');
            
            // Leer el archivo directamente sin guardarlo
            $spreadsheet = IOFactory::load($file->getPathname());
            $worksheet = $spreadsheet->getActiveSheet();
            $rows = $worksheet->toArray();
            
            // Verificar que hay datos
            if (count($rows) <= 1) {
                return back()->with('error', 'El archivo no contiene datos suficientes.');
            }
            
            // Obtener los encabezados
            $headers = array_shift($rows);
            
            // Mapear las columnas
            $columnMap = $this->mapColumns($headers);
            
            // Verificar que se encontraron las columnas requeridas
            if (!isset($columnMap['estudiante']) || !isset($columnMap['documento'])) {
                return back()->with('error', 'El archivo no contiene las columnas requeridas (Estudiante, Documento).');
            }
            
            // Procesar los datos
            $count = 0;
            $errors = [];
            
            foreach ($rows as $row) {
                // Verificar que la fila tiene datos
                if (empty($row[$columnMap['estudiante']]) || empty($row[$columnMap['documento']])) {
                    continue;
                }
                
                try {
                    // Crear o actualizar el participante
                    $participant = Participant::updateOrCreate(
                        [
                            'program_id' => $program->id,
                            'document' => $row[$columnMap['documento']],
                        ],
                        [
                            'name' => strtoupper($row[$columnMap['estudiante']]),
                            'profession' => isset($columnMap['profesiones']) ? $row[$columnMap['profesiones']] : null,
                            'phone' => isset($columnMap['telefono']) ? $row[$columnMap['telefono']] : null,
                            'university' => isset($columnMap['universidad']) ? $row[$columnMap['universidad']] : null,
                        ]
                    );
                    
                    $count++;
                } catch (\Exception $e) {
                    $errors[] = "Error en la fila " . ($count + 2) . ": " . $e->getMessage();
                }
            }
            
            // Mostrar resultados
            if ($count > 0) {
                $message = "Se han procesado $count participantes correctamente.";
                if (count($errors) > 0) {
                    $message .= " Se encontraron " . count($errors) . " errores.";
                }
                return redirect()->route('participants.index', $program->id)->with('success', $message);
            } else {
                return back()->with('error', 'No se pudo procesar ningún participante. ' . implode(' ', $errors));
            }
            
        } catch (\Exception $e) {
            Log::error('Error al procesar archivo de participantes: ' . $e->getMessage());
            return back()->with('error', 'Error al procesar el archivo: ' . $e->getMessage());
        }
    }
    
    private function mapColumns($headers)
    {
        $columnMap = [];
        
        foreach ($headers as $index => $header) {
            $header = trim(strtolower($header));
            
            if (in_array($header, ['estudiante', 'alumno', 'nombre', 'nombre completo', 'participante'])) {
                $columnMap['estudiante'] = $index;
            } elseif (in_array($header, ['documento', 'ci', 'cédula', 'cedula', 'dni', 'id'])) {
                $columnMap['documento'] = $index;
            } elseif (in_array($header, ['profesión', 'profesion', 'profesiones', 'carrera'])) {
                $columnMap['profesiones'] = $index;
            } elseif (in_array($header, ['teléfono', 'telefono', 'tel', 'celular', 'móvil', 'movil'])) {
                $columnMap['telefono'] = $index;
            } elseif (in_array($header, ['universidad', 'institución', 'institucion', 'centro'])) {
                $columnMap['universidad'] = $index;
            }
        }
        
        return $columnMap;
    }
}

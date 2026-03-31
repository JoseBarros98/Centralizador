<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Storage;
use Illuminate\Routing\Controller;

class LogController extends Controller
{
    public function index(Request $request)
    {
        $logFiles = $this->getLogFiles();
        $selectedFile = $request->get('file', 'laravel.log');
        $lines = $request->get('lines', 100);
        $level = $request->get('level', 'all');
        
        $logContent = $this->getLogContent($selectedFile, $lines, $level);
        
        return view('logs.index', compact('logFiles', 'selectedFile', 'lines', 'level', 'logContent'));
    }

    public function download(Request $request)
    {
        $file = $request->get('file', 'laravel.log');
        $logPath = storage_path('logs/' . $file);
        
        if (!File::exists($logPath)) {
            abort(404, 'Archivo de log no encontrado');
        }
        
        return response()->download($logPath);
    }

    public function clear(Request $request)
    {
        $file = $request->get('file', 'laravel.log');
        $logPath = storage_path('logs/' . $file);
        
        if (File::exists($logPath)) {
            File::put($logPath, '');
            return redirect()->back()->with('success', "Log {$file} limpiado exitosamente");
        }
        
        return redirect()->back()->with('error', 'Archivo de log no encontrado');
    }

    private function getLogFiles()
    {
        $logPath = storage_path('logs');
        $files = [];
        
        if (File::isDirectory($logPath)) {
            $files = File::files($logPath);
            $files = array_map(function($file) {
                return $file->getFilename();
            }, $files);
            
            // Filtrar solo archivos .log
            $files = array_filter($files, function($file) {
                return pathinfo($file, PATHINFO_EXTENSION) === 'log';
            });
            
            // Ordenar por fecha de modificación (más reciente primero)
            usort($files, function($a, $b) {
                $timeA = File::lastModified(storage_path('logs/' . $a));
                $timeB = File::lastModified(storage_path('logs/' . $b));
                return $timeB - $timeA;
            });
        }
        
        return $files;
    }

    private function getLogContent($file, $lines, $level)
    {
        $logPath = storage_path('logs/' . $file);
        
        if (!File::exists($logPath)) {
            return ['error' => 'Archivo de log no encontrado'];
        }
        
        $content = File::get($logPath);
        
        if (empty($content)) {
            return ['entries' => [], 'total_lines' => 0];
        }
        
        // Dividir en líneas
        $allLines = explode("\n", $content);
        $totalLines = count($allLines);
        
        // Tomar las últimas N líneas
        $recentLines = array_slice($allLines, -$lines);
        
        // Parsear las entradas del log
        $entries = $this->parseLogEntries($recentLines, $level);
        
        return [
            'entries' => $entries,
            'total_lines' => $totalLines,
            'showing_lines' => count($recentLines)
        ];
    }

    private function parseLogEntries($lines, $filterLevel)
    {
        $entries = [];
        $currentEntry = null;
        
        foreach ($lines as $line) {
            if (empty(trim($line))) continue;
            
            // Detectar inicio de nueva entrada de log
            if (preg_match('/^\[(\d{4}-\d{2}-\d{2} \d{2}:\d{2}:\d{2})\] \w+\.(\w+): (.+)/', $line, $matches)) {
                // Guardar entrada anterior si existe
                if ($currentEntry !== null) {
                    if ($this->shouldIncludeEntry($currentEntry, $filterLevel)) {
                        $entries[] = $currentEntry;
                    }
                }
                
                // Crear nueva entrada
                $currentEntry = [
                    'timestamp' => $matches[1],
                    'level' => strtoupper($matches[2]),
                    'message' => $matches[3],
                    'full_message' => $line,
                    'context' => []
                ];
            } else if ($currentEntry !== null) {
                // Línea de continuación (stack trace, contexto, etc.)
                $currentEntry['context'][] = $line;
                $currentEntry['full_message'] .= "\n" . $line;
            }
        }
        
        // Agregar la última entrada
        if ($currentEntry !== null && $this->shouldIncludeEntry($currentEntry, $filterLevel)) {
            $entries[] = $currentEntry;
        }
        
        return array_reverse($entries); // Mostrar más recientes primero
    }

    private function shouldIncludeEntry($entry, $filterLevel)
    {
        if ($filterLevel === 'all') {
            return true;
        }
        
        return strtolower($entry['level']) === strtolower($filterLevel);
    }

    public function getLevelColor($level)
    {
        switch (strtolower($level)) {
            case 'emergency':
            case 'alert':
            case 'critical':
            case 'error':
                return 'text-red-600 bg-red-100';
            case 'warning':
                return 'text-yellow-600 bg-yellow-100';
            case 'notice':
            case 'info':
                return 'text-blue-600 bg-blue-100';
            case 'debug':
                return 'text-gray-600 bg-gray-100';
            default:
                return 'text-gray-600 bg-gray-100';
        }
    }
}

<?php

namespace App\Services;

use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Http;

class AcademicInfoExtractor
{
    /**
     * Extrae información académica de un archivo usando IA
     */
    public function extractFromFile($filePath, $fileName)
    {
        try {
            Log::info('Iniciando extracción de información académica', [
                'file' => $fileName,
                'path' => $filePath
            ]);

            // 1. Primero intentar con IA directamente
            $academicInfo = $this->extractWithAI($filePath, $fileName);
            
            // 2. Si la IA no funciona, usar método tradicional
            if (empty($academicInfo)) {
                Log::info('IA no retornó resultados, usando método tradicional', ['file' => $fileName]);
                $text = $this->extractTextFromFile($filePath, $fileName);
                
                if (!empty($text)) {
                    $academicInfo = $this->findAcademicInfo($text);
                }
            }
            
            // 3. Limpiar y normalizar resultados
            if (!empty($academicInfo)) {
                $academicInfo = $this->cleanAndNormalizeResults($academicInfo);
                
                Log::info('Información académica extraída exitosamente', [
                    'file' => $fileName,
                    'count' => count($academicInfo),
                    'items' => array_column($academicInfo, 'title')
                ]);
            }

            return $academicInfo;

        } catch (\Exception $e) {
            Log::error('Error en extracción académica', [
                'file' => $fileName,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            return null;
        }
    }

    /**
     * Extrae información usando IA (Groq, OpenAI o Claude)
     */
    private function extractWithAI($filePath, $fileName)
    {
        // Intentar con diferentes APIs en orden de preferencia
        $apis = [
            'anthropic' => env('ANTHROPIC_API_KEY'),
            'openai' => env('OPENAI_API_KEY'),
            'groq' => env('GROQ_API_KEY'),
        ];

        foreach ($apis as $provider => $apiKey) {
            if (!empty($apiKey)) {
                Log::info("Intentando extracción con {$provider}");
                
                $result = $this->extractWithProvider($provider, $apiKey, $filePath, $fileName);
                
                if (!empty($result)) {
                    Log::info("Extracción exitosa con {$provider}", [
                        'items_found' => count($result)
                    ]);
                    return $result;
                }
            }
        }

        return null;
    }

    /**
     * Extrae usando un proveedor específico de IA
     */
    private function extractWithProvider($provider, $apiKey, $filePath, $fileName)
    {
        $extension = strtolower(pathinfo($fileName, PATHINFO_EXTENSION));
        
        // Para PDFs e imágenes, usar análisis visual si está disponible
        if (in_array($extension, ['pdf', 'jpg', 'jpeg', 'png']) && in_array($provider, ['openai', 'anthropic'])) {
            return $this->extractWithVision($provider, $apiKey, $filePath);
        }
        
        // Para otros casos, extraer texto primero
        $text = $this->extractTextFromFile($filePath, $fileName);
        
        if (empty($text)) {
            return null;
        }

        return $this->extractWithTextAPI($provider, $apiKey, $text);
    }

    /**
     * Extrae usando API con capacidad de visión
     */
    private function extractWithVision($provider, $apiKey, $filePath)
    {
        try {
            // Convertir PDF a imagen si es necesario
            $imagePath = $filePath;
            $isTemporary = false;
            
            if (strtolower(pathinfo($filePath, PATHINFO_EXTENSION)) === 'pdf') {
                $imagePath = $this->convertPdfToImage($filePath);
                $isTemporary = true;
                
                if (!$imagePath) {
                    return null;
                }
            }

            // Verificar que el archivo existe
            if (!file_exists($imagePath)) {
                Log::warning('Archivo de imagen no encontrado', ['path' => $imagePath]);
                return null;
            }

            // Leer y codificar imagen
            $imageData = file_get_contents($imagePath);
            $base64Image = base64_encode($imageData);
            $mimeType = $this->getMimeType($imagePath);
            
            // Limpiar archivo temporal
            if ($isTemporary && file_exists($imagePath)) {
                unlink($imagePath);
            }

            $prompt = $this->getExtractionPrompt();

            // Llamar a la API según el proveedor
            if ($provider === 'openai') {
                return $this->callOpenAIVision($apiKey, $prompt, $base64Image, $mimeType);
            } elseif ($provider === 'anthropic') {
                return $this->callClaudeVision($apiKey, $prompt, $base64Image, $mimeType);
            }

        } catch (\Exception $e) {
            Log::error("Error en extracción con visión ({$provider})", [
                'error' => $e->getMessage()
            ]);
        }

        return null;
    }

    /**
     * Extrae usando API de texto
     */
    private function extractWithTextAPI($provider, $apiKey, $text)
    {
        try {
            // Limitar texto para no exceder límites
            $textSample = mb_substr($text, 0, 8000);
            $prompt = $this->getExtractionPrompt() . "\n\nTEXTO DEL CV:\n" . $textSample;

            if ($provider === 'openai') {
                return $this->callOpenAIText($apiKey, $prompt);
            } elseif ($provider === 'anthropic') {
                return $this->callClaudeText($apiKey, $prompt);
            } elseif ($provider === 'groq') {
                return $this->callGroqText($apiKey, $prompt);
            }

        } catch (\Exception $e) {
            Log::error("Error en extracción con texto ({$provider})", [
                'error' => $e->getMessage()
            ]);
        }

        return null;
    }

    /**
     * Genera el prompt para extracción
     */
    private function getExtractionPrompt()
    {
        return <<<PROMPT
Extrae ÚNICAMENTE la formación académica de este CV. Debes ser MUY SELECTIVO y solo incluir estudios formales.

**INCLUIR (Solo estos):**
- Doctorados (PhD, Doctorado)
- Maestrías (Maestría, Magíster, Master, M.Sc., M.A.)
- Especialidades (Especialización, Especialidad)
- Licenciaturas (Licenciatura, Grado, Bachelor, Ingeniería)
- Diplomados (Diplomado, Certificación de Postgrado)
- Educación Básica (Primaria, Secundaria) - solo si está explícitamente en sección de educación
- Idiomas - solo si están en sección de estudios/educación
- Conocimientos técnicos - solo si son certificaciones formales

**EXCLUIR COMPLETAMENTE:**
❌ Experiencia laboral (cualquier puesto, cargo, trabajo)
❌ Actividades como docente, profesor, instructor
❌ Tutorías, asesorías, consultorías
❌ Participación en eventos como expositor/conferencista
❌ Publicaciones, artículos, investigaciones realizadas
❌ Cursos que dictó (solo cursos que recibió)
❌ Certificados de reconocimiento laboral
❌ Membresías profesionales
❌ Referencias personales
❌ Ubicaciones sin contexto académico
❌ Responsabilidades laborales
❌ "Programa de Gestión" sin indicar es estudio (podría ser experiencia)

**INSTRUCCIONES PARA PARSEAR DOCUMENTOS DESORDENADOS:**
Si encuentras un formato como:
```
FORMACIÓN ACADÉMICA
Universidad Privada Bolivia UPB
2021 - 2022
Diplomado Psicología Organizacional (Post grado)
```

Parsea así:
- Busca el nombre del grado/programa (Diplomado, Licenciatura, Maestría, etc.)
- Si es un "Programa de..." es SOLO formación si dice explícitamente "Programa de Estudio", "Programa de Capacitación", "Programa Académico"
- Si dice "Programa de Gestión" sin contexto académico claro, NO incluir
- Extrae la institución (Universidad, Centro, Instituto, Escuela)
- Extrae años/fechas (2021-2022, 2021, etc.)
- Extrae ubicación si está clara

**FORMATO JSON REQUERIDO:**
```json
[
  {
    "type": "Diplomado",
    "title": "Diplomado en Psicología Organizacional",
    "institution": "Universidad Privada Bolivia UPB",
    "year": "2021-2022",
    "location": "Bolivia"
  },
  {
    "type": "Licenciatura",
    "title": "Licenciado en Administración de Empresas",
    "institution": "Universidad Católica Boliviana",
    "year": "1992-1996",
    "location": "Bolivia"
  }
]
```

**REGLAS CRÍTICAS:**
1. Si dice "Docente de...", "Profesor de...", "Instructor de..." → NO INCLUIR (es experiencia laboral)
2. Si dice "Asesor en...", "Consultor en...", "Coordinador de..." → NO INCLUIR (es experiencia laboral)
3. Si dice "Expositor", "Conferencista", "Ponente" → NO INCLUIR (es actividad profesional)
4. "Programa de Gestión" SIN más contexto académico → NO INCLUIR
5. "Programa de Gestión y Liderazgo" con descripción de módulos NO es formación académica formal → NO INCLUIR
6. Solo incluir si claramente indica que la persona RECIBIÓ la formación
7. Preservar tildes exactamente: á, é, í, ó, ú, ñ
8. Para "Año", captura el rango (ej: "2021-2022") o un solo año (ej: "2021")
9. Si no hay información académica válida, devolver: []

Responde SOLO con el JSON, sin texto adicional.
PROMPT;
    }

    /**
     * Llama a OpenAI GPT-4 Vision
     */
    private function callOpenAIVision($apiKey, $prompt, $base64Image, $mimeType)
    {
        try {
            $response = Http::withHeaders([
                'Authorization' => "Bearer {$apiKey}",
                'Content-Type' => 'application/json',
            ])->timeout(60)->post('https://api.openai.com/v1/chat/completions', [
                'model' => 'gpt-4o-mini',
                'messages' => [
                    [
                        'role' => 'user',
                        'content' => [
                            ['type' => 'text', 'text' => $prompt],
                            [
                                'type' => 'image_url',
                                'image_url' => [
                                    'url' => "data:{$mimeType};base64,{$base64Image}"
                                ]
                            ]
                        ]
                    ]
                ],
                'temperature' => 0.1,
                'max_tokens' => 2000
            ]);

            if ($response->successful()) {
                $content = $response->json('choices.0.message.content');
                return $this->parseJSONResponse($content);
            }

            Log::error('Error en OpenAI Vision', [
                'status' => $response->status(),
                'body' => $response->body()
            ]);

        } catch (\Exception $e) {
            Log::error('Excepción en OpenAI Vision', ['error' => $e->getMessage()]);
        }

        return null;
    }

    /**
     * Llama a Claude (Anthropic) con visión
     */
    private function callClaudeVision($apiKey, $prompt, $base64Image, $mimeType)
    {
        try {
            $response = Http::withHeaders([
                'x-api-key' => $apiKey,
                'anthropic-version' => '2023-06-01',
                'content-type' => 'application/json',
            ])->timeout(60)->post('https://api.anthropic.com/v1/messages', [
                'model' => 'claude-3-5-sonnet-20241022',
                'max_tokens' => 2000,
                'messages' => [
                    [
                        'role' => 'user',
                        'content' => [
                            [
                                'type' => 'image',
                                'source' => [
                                    'type' => 'base64',
                                    'media_type' => $mimeType,
                                    'data' => $base64Image
                                ]
                            ],
                            ['type' => 'text', 'text' => $prompt]
                        ]
                    ]
                ]
            ]);

            if ($response->successful()) {
                $content = $response->json('content.0.text');
                return $this->parseJSONResponse($content);
            }

            Log::error('Error en Claude Vision', [
                'status' => $response->status(),
                'body' => $response->body()
            ]);

        } catch (\Exception $e) {
            Log::error('Excepción en Claude Vision', ['error' => $e->getMessage()]);
        }

        return null;
    }

    /**
     * Llama a OpenAI con texto
     */
    private function callOpenAIText($apiKey, $prompt)
    {
        try {
            $response = Http::withHeaders([
                'Authorization' => "Bearer {$apiKey}",
                'Content-Type' => 'application/json',
            ])->timeout(30)->post('https://api.openai.com/v1/chat/completions', [
                'model' => 'gpt-4o-mini',
                'messages' => [
                    ['role' => 'user', 'content' => $prompt]
                ],
                'temperature' => 0.1,
                'max_tokens' => 2000
            ]);

            if ($response->successful()) {
                $content = $response->json('choices.0.message.content');
                return $this->parseJSONResponse($content);
            }

        } catch (\Exception $e) {
            Log::error('Excepción en OpenAI Text', ['error' => $e->getMessage()]);
        }

        return null;
    }

    /**
     * Llama a Claude con texto
     */
    private function callClaudeText($apiKey, $prompt)
    {
        try {
            $response = Http::withHeaders([
                'x-api-key' => $apiKey,
                'anthropic-version' => '2023-06-01',
                'content-type' => 'application/json',
            ])->timeout(30)->post('https://api.anthropic.com/v1/messages', [
                'model' => 'claude-3-5-sonnet-20241022',
                'max_tokens' => 2000,
                'messages' => [
                    ['role' => 'user', 'content' => $prompt]
                ]
            ]);

            if ($response->successful()) {
                $content = $response->json('content.0.text');
                return $this->parseJSONResponse($content);
            }

        } catch (\Exception $e) {
            Log::error('Excepción en Claude Text', ['error' => $e->getMessage()]);
        }

        return null;
    }

    /**
     * Llama a Groq con texto
     */
    private function callGroqText($apiKey, $prompt)
    {
        try {
            $response = Http::withHeaders([
                'Authorization' => "Bearer {$apiKey}",
                'Content-Type' => 'application/json',
            ])->timeout(30)->post('https://api.groq.com/openai/v1/chat/completions', [
                'model' => 'llama-3.3-70b-versatile',
                'messages' => [
                    ['role' => 'user', 'content' => $prompt]
                ],
                'temperature' => 0.1,
                'max_tokens' => 2000
            ]);

            if ($response->successful()) {
                $content = $response->json('choices.0.message.content');
                return $this->parseJSONResponse($content);
            }

        } catch (\Exception $e) {
            Log::error('Excepción en Groq Text', ['error' => $e->getMessage()]);
        }

        return null;
    }

    /**
     * Parsea la respuesta JSON de la IA
     */
    private function parseJSONResponse($content)
    {
        if (empty($content)) {
            return null;
        }

        // Limpiar markdown si existe
        $content = preg_replace('/```json\s*/', '', $content);
        $content = preg_replace('/```\s*$/', '', $content);
        $content = trim($content);

        // Intentar decodificar
        $decoded = json_decode($content, true);

        if (json_last_error() === JSON_ERROR_NONE && is_array($decoded)) {
            return $decoded;
        }

        Log::warning('Respuesta no es JSON válido', [
            'content' => substr($content, 0, 500),
            'error' => json_last_error_msg()
        ]);

        return null;
    }

    /**
     * Convierte PDF a imagen
     */
    private function convertPdfToImage($pdfPath)
    {
        // Validar que el archivo existe
        if (!file_exists($pdfPath)) {
            Log::warning('Archivo PDF no existe', ['path' => $pdfPath]);
            return null;
        }

        $pdftoppmPath = $this->findCommand('pdftoppm');
        
        if (!$pdftoppmPath) {
            Log::warning('pdftoppm no disponible');
            return null;
        }

        try {
            $outputDir = sys_get_temp_dir();
            $outputPrefix = 'pdf_' . uniqid();
            
            $command = sprintf(
                '%s -png -f 1 -l 1 -r 150 -singlefile %s %s 2>&1',
                escapeshellarg($pdftoppmPath),
                escapeshellarg($pdfPath),
                escapeshellarg($outputDir . '/' . $outputPrefix)
            );

            exec($command, $output, $returnCode);
            
            if ($returnCode === 0) {
                $imagePath = $outputDir . '/' . $outputPrefix . '.png';
                
                if (file_exists($imagePath)) {
                    return $imagePath;
                }
            }

            Log::warning('Error al convertir PDF a imagen', [
                'return_code' => $returnCode,
                'output' => implode("\n", $output)
            ]);

        } catch (\Exception $e) {
            Log::error('Excepción en conversión PDF', ['error' => $e->getMessage()]);
        }

        return null;
    }

    /**
     * Extrae texto de archivos
     */
    private function extractTextFromFile($filePath, $fileName)
    {
        // Validar que el archivo existe
        if (!file_exists($filePath)) {
            Log::warning('Archivo no existe', ['path' => $filePath]);
            return null;
        }

        $extension = strtolower(pathinfo($fileName, PATHINFO_EXTENSION));
        
        switch ($extension) {
            case 'pdf':
                return $this->extractFromPDF($filePath);
            case 'docx':
                return $this->extractFromDocx($filePath);
            case 'txt':
                return file_get_contents($filePath);
            default:
                Log::info('Tipo de archivo no soportado', ['extension' => $extension]);
                return null;
        }
    }

    /**
     * Extrae texto de PDF
     */
    private function extractFromPDF($filePath)
    {
        $pdfToTextPath = $this->findCommand('pdftotext');
        
        if (!$pdfToTextPath) {
            Log::warning('pdftotext no disponible');
            return null;
        }

        try {
            $tempFile = tempnam(sys_get_temp_dir(), 'pdf_text');
            
            $command = sprintf(
                '%s -enc UTF-8 -nopgbrk %s %s 2>&1',
                escapeshellarg($pdfToTextPath),
                escapeshellarg($filePath),
                escapeshellarg($tempFile)
            );
            
            exec($command, $output, $returnCode);
            
            if ($returnCode === 0 && file_exists($tempFile)) {
                $text = file_get_contents($tempFile);
                unlink($tempFile);
                return $this->cleanUTF8($text);
            }

        } catch (\Exception $e) {
            Log::error('Error extrayendo texto de PDF', ['error' => $e->getMessage()]);
        }

        return null;
    }

    /**
     * Extrae texto de DOCX
     */
    private function extractFromDocx($filePath)
    {
        try {
            $zip = new \ZipArchive();
            
            if ($zip->open($filePath) === TRUE) {
                $xmlString = $zip->getFromName('word/document.xml');
                $zip->close();
                
                if ($xmlString) {
                    return strip_tags($xmlString);
                }
            }

        } catch (\Exception $e) {
            Log::error('Error extrayendo texto de DOCX', ['error' => $e->getMessage()]);
        }

        return null;
    }

    /**
     * Método tradicional de extracción con regex (fallback)
     */
    private function findAcademicInfo($text)
    {
        $patterns = [
            'Doctorado' => '/\b(?:doctorado|phd|ph\.d)\s+(?:en|de)\s+([^.\n]{10,150})/i',
            'Maestría' => '/\b(?:maestr[ií]a|master|magíster|m\.?\s*sc\.?)\s+(?:en|de)\s+([^.\n]{10,150})/i',
            'Especialidad' => '/\b(?:especialidad|especialización)\s+(?:en|de)\s+([^.\n]{10,150})/i',
            'Licenciatura' => '/\b(?:licenciatura|licenciad[oa]|grado)\s+(?:en|de)\s+([^.\n]{10,150})/i',
            'Diplomado' => '/\b(?:diplomado)\s+(?:en|de)\s+([^.\n]{10,150})/i',
        ];

        $found = [];

        foreach ($patterns as $type => $pattern) {
            if (preg_match_all($pattern, $text, $matches)) {
                foreach ($matches[1] as $title) {
                    $cleanTitle = $this->cleanTitle($title);
                    
                    if (strlen($cleanTitle) > 15 && !$this->isWorkExperience($cleanTitle)) {
                        $found[] = [
                            'type' => $type,
                            'title' => $cleanTitle
                        ];
                    }
                }
            }
        }

        return $this->removeDuplicates($found);
    }

    /**
     * Limpia títulos
     */
    private function cleanTitle($title)
    {
        // Remover saltos de línea y espacios extra
        $title = preg_replace('/[\r\n]+/', ' ', $title);
        $title = preg_replace('/\s+/', ' ', $title);
        $title = trim($title, ' .,;:()[]{}');
        
        // Remover corchetes y contenido
        $title = preg_replace('/\[.*?\]/', '', $title);
        
        // Remover ubicaciones (ej: ", Tarija" o ", Bolivia")
        $title = preg_replace('/,\s+[A-Z][a-záéíóúñ]+(?:\s+de\s+(?:la\s+)?[A-Z][a-záéíóúñ]+)*\s*$/i', '', $title);
        
        // Remover patrones de ciudad, país
        $title = preg_replace('/^[A-Z][a-záéíóúñ]+,\s*[A-Z][a-záéíóúñ]+\s*/i', '', $title);
        
        // Remover información de fechas
        $title = preg_replace('/\s*\(de\s+\w+\s+a\s+\w+\).*$/i', '', $title);
        $title = preg_replace('/\s*\(\s*\d+\s*-\s*\d+\s*\).*$/i', '', $title);
        
        $title = trim($title);
        
        // Si queda muy corto, es basura
        if (strlen($title) < 15) {
            return '';
        }
        
        return $this->smartCapitalize($title);
    }

    /**
     * Capitalización inteligente
     */
    private function smartCapitalize($text)
    {
        $text = strtolower($text);
        $smallWords = ['de', 'del', 'la', 'el', 'en', 'y', 'e', 'o', 'u', 'a', 'para', 'por', 'con'];
        
        $words = explode(' ', $text);
        $result = [];
        
        foreach ($words as $i => $word) {
            if ($i === 0) {
                $result[] = ucfirst($word);
            } elseif (in_array($word, $smallWords)) {
                $result[] = $word;
            } else {
                $result[] = ucfirst($word);
            }
        }
        
        return implode(' ', $result);
    }

    /**
     * Detecta si es experiencia laboral
     */
    private function isWorkExperience($title)
    {
        $workKeywords = [
            'jefe', 'director', 'gerente', 'supervisor', 'coordinador',
            'consultor', 'docente', 'profesor', 'instructor', 'asesor',
            'expositor', 'conferencista', 'ponente', 'tutor', 'empleado',
            'trabajador', 'operario', 'ayudante', 'aprendiz', 'becario'
        ];

        foreach ($workKeywords as $keyword) {
            if (stripos($title, $keyword) !== false) {
                return true;
            }
        }

        return false;
    }

    /**
     * Limpia UTF-8
     */
    private function cleanUTF8($text)
    {
        if (mb_check_encoding($text, 'UTF-8')) {
            return $text;
        }

        $encoding = mb_detect_encoding($text, ['UTF-8', 'ISO-8859-1', 'Windows-1252'], true);
        
        if ($encoding && $encoding !== 'UTF-8') {
            $text = mb_convert_encoding($text, 'UTF-8', $encoding);
        }

        return $text;
    }

    /**
     * Limpia y normaliza resultados finales
     */
    private function cleanAndNormalizeResults($results)
    {
        $cleaned = [];

        foreach ($results as $item) {
            // Validar campos mínimos
            if (!isset($item['type']) || !isset($item['title'])) {
                continue;
            }

            // Limpiar UTF-8
            $item['title'] = $this->cleanUTF8($item['title']);
            
            if (isset($item['institution'])) {
                $item['institution'] = $this->cleanUTF8($item['institution']);
            }

            // Verificar que no sea experiencia laboral
            if ($this->isWorkExperience($item['title'])) {
                continue;
            }

            // Normalizar tipo
            $item['type'] = $this->normalizeType($item['type']);

            $cleaned[] = $item;
        }

        return $this->removeDuplicates($cleaned);
    }

    /**
     * Normaliza tipos académicos
     */
    private function normalizeType($type)
    {
        $normalizations = [
            'maestria' => 'Maestría',
            'master' => 'Maestría',
            'magister' => 'Maestría',
            'doctorado' => 'Doctorado',
            'phd' => 'Doctorado',
            'especialidad' => 'Especialidad',
            'especialización' => 'Especialidad',
            'licenciatura' => 'Licenciatura',
            'diplomado' => 'Diplomado',
        ];

        $lowerType = strtolower($type);

        return $normalizations[$lowerType] ?? ucfirst($type);
    }

    /**
     * Remueve duplicados
     */
    private function removeDuplicates($items)
    {
        $unique = [];
        $seen = [];

        foreach ($items as $item) {
            $key = strtolower($item['type'] . '|' . $item['title']);
            
            if (!isset($seen[$key])) {
                $seen[$key] = true;
                $unique[] = $item;
            }
        }

        return $unique;
    }

    /**
     * Busca comando en el sistema con validación
     */
    private function findCommand($command)
    {
        $possiblePaths = [
            "/usr/bin/{$command}",
            "/usr/local/bin/{$command}",
            "/bin/{$command}",
            $command
        ];

        foreach ($possiblePaths as $path) {
            // Validar que el archivo existe y es ejecutable
            if (file_exists($path) && is_executable($path)) {
                return $path;
            }
            
            // Intentar con which
            $which = shell_exec(sprintf("which %s 2>/dev/null", escapeshellarg($path)));
            if (!empty($which)) {
                $whichPath = trim($which);
                if (file_exists($whichPath) && is_executable($whichPath)) {
                    return $whichPath;
                }
            }
        }

        return null;
    }

    /**
     * Obtiene el tipo MIME de un archivo
     */
    private function getMimeType($filePath)
    {
        $extension = strtolower(pathinfo($filePath, PATHINFO_EXTENSION));
        
        $mimeTypes = [
            'jpg' => 'image/jpeg',
            'jpeg' => 'image/jpeg',
            'png' => 'image/png',
            'gif' => 'image/gif',
            'webp' => 'image/webp'
        ];
        
        return $mimeTypes[$extension] ?? 'image/png';
    }
}

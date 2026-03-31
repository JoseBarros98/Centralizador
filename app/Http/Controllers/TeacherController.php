<?php

namespace App\Http\Controllers;

use App\Models\Teacher;
use App\Models\TeacherFile;
use App\Models\Module;
use App\Services\GoogleDriveService;
use App\Services\AcademicInfoExtractor;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Log;
use Illuminate\Routing\Controller;

class TeacherController extends Controller
{
    // public function __construct()
    // {
    //     $this->middleware(['permission:teacher.view'])->only(['index', 'show']);
    //     $this->middleware(['permission:teacher.create'])->only(['create','store']);
    //     $this->middleware(['permission:teacher.edit'])->only(['edit', 'update']);
    //     $this->middleware(['teacher:teacher.delete'])->only(['destroy']);
    // }

    public function index( Request $request)
    {
        $query = Teacher::with(['modules.program', 'files' => function($query) {
            $query->whereNotNull('academic_info');
        }]);

        if ($request->has('search') && $request->search != ''){
            $search = trim($request->search);
            
            // Dividir el término de búsqueda en palabras individuales para búsqueda más flexible
            $searchWords = array_filter(explode(' ', $search));
            
            $query->where(function($q) use ($search, $searchWords){
                // Búsqueda exacta en campos básicos (case insensitive)
                $q->whereRaw('LOWER(name) LIKE ?', ['%' . strtolower($search) . '%'])
                    ->orWhereRaw('LOWER(ci) LIKE ?', ['%' . strtolower($search) . '%'])
                    ->orWhereRaw('LOWER(profession) LIKE ?', ['%' . strtolower($search) . '%'])
                    ->orWhereRaw('LOWER(academic_degree) LIKE ?', ['%' . strtolower($search) . '%']);

                // Búsqueda por palabras individuales en campos básicos
                foreach ($searchWords as $word) {
                    if (strlen($word) >= 2) { // Solo palabras de 2+ caracteres
                        $q->orWhereRaw('LOWER(name) LIKE ?', ['%' . strtolower($word) . '%'])
                          ->orWhereRaw('LOWER(profession) LIKE ?', ['%' . strtolower($word) . '%']);
                    }
                }
                    
                // Búsqueda en información académica extraída de archivos
                $q->orWhereHas('files', function($fileQuery) use ($search, $searchWords) {
                    $fileQuery->whereNotNull('academic_info')
                        ->where(function($academicQuery) use ($search, $searchWords) {
                            // Búsqueda exacta case insensitive en JSON
                            $academicQuery->whereRaw("LOWER(academic_info) LIKE ?", ['%' . strtolower($search) . '%'])
                                // Búsqueda específica en tipos (case insensitive)
                                ->orWhereRaw("LOWER(JSON_UNQUOTE(JSON_EXTRACT(academic_info, '$[*].type'))) LIKE ?", ['%' . strtolower($search) . '%'])
                                // Búsqueda específica en títulos (case insensitive)
                                ->orWhereRaw("LOWER(JSON_UNQUOTE(JSON_EXTRACT(academic_info, '$[*].title'))) LIKE ?", ['%' . strtolower($search) . '%']);
                            
                            // Búsqueda por palabras clave individuales en información académica
                            foreach ($searchWords as $word) {
                                if (strlen($word) >= 3) { // Para términos académicos, mínimo 3 caracteres
                                    $academicQuery->orWhereRaw("LOWER(JSON_UNQUOTE(JSON_EXTRACT(academic_info, '$[*].type'))) LIKE ?", ['%' . strtolower($word) . '%'])
                                        ->orWhereRaw("LOWER(JSON_UNQUOTE(JSON_EXTRACT(academic_info, '$[*].title'))) LIKE ?", ['%' . strtolower($word) . '%']);
                                }
                            }
                        });
                });
            });
        }
            
        $teachers = $query->orderBy('name', 'asc')->paginate(10);

        return view ('teachers.index', compact('teachers'));

    }

    public function create()
    {
        return view('teachers.create');
    }

    public function store(Request $request)
    {
        $degrees = ['Lic.', 'Ing.', 'Dr.', 'M.Sc.', 'Ph.D.', 'M.Sc. Ing.', 'M.Sc. Lic.', 'M.Sc. Dr.', 'Ph.D. Ing.', 'Ph.D. Lic.'];

        try {
            $validated = $request->validate([
                'name' => 'required|string|max:255',
                'email' => 'nullable|email|max:255|unique:teachers,email',
                'phone' => 'nullable|string|max:20',
                'bank' => 'nullable|string|max:255',
                'account_number' => 'nullable|string|max:255',
                'bill' => 'nullable|in:Si,No',
                'esam_worker' => 'nullable|in:Si,No',
                'address' => 'nullable|string|max:255',
                'birth_date' => 'nullable|date',
                'profession' => 'nullable|string|max:255',
                'ci' => 'nullable|string|max:20|unique:teachers,ci',
                'academic_degree' => 'nullable|in:' . implode(',', $degrees),
            ]);

            $teacher = new Teacher([
                'name' => $validated['name'],
                'email' => $validated['email'] ?? null,
                'phone' => $validated['phone'] ?? null,
                'address' => $validated['address'] ?? null,
                'bank' => $validated['bank'] ?? null,
                'account_number' => $validated['account_number'] ?? null,
                'bill' => $validated['bill'] ?? 'No',
                'esam_worker' => $validated['esam_worker'] ?? 'No',
                'birth_date' => $validated['birth_date'] ?? null,
                'profession' => $validated['profession'] ?? null,
                'ci' => $validated['ci'] ?? null,
                'academic_degree' => $validated['academic_degree'] ?? '',
            ]);

            $teacher->created_by = Auth::id();
            $teacher->save();

        // Procesar archivos si existen - SOLO GOOGLE DRIVE
        if($request->hasFile('files')){
            $googleDriveService = new GoogleDriveService();
            $teacherFullName = trim($teacher->name . ' ' . $teacher->paternal_surname . ' ' . $teacher->maternal_surname);
            $folderId = $googleDriveService->createHierarchicalFolder('Docentes', $teacherFullName);
            
            $files = $request->file('files');
            $descriptions = $request->input('file_descriptions', []);
            foreach ($files as $index => $file){
                try {
                    $driveFileResult = $googleDriveService->uploadFile(
                        $file->getRealPath(),
                        $file->getClientOriginalName(),
                        $file->getClientMimeType(),
                        $descriptions[$index] ?? null,
                        $folderId
                    );
                    
                    $teacherFile = new TeacherFile([
                        'teacher_id' => $teacher->id,
                        'file_path' => '',
                        'file_name' => $file->getClientOriginalName(),
                        'file_type' => $file->getClientMimeType(),
                        'description' => $descriptions[$index] ?? null,
                        'stored_in_drive' => true,
                        'google_drive_id' => $driveFileResult['id'],
                    ]);

                    $teacherFile->created_by = Auth::id();
                    $teacherFile->save();
                } catch (\Exception $e) {
                    Log::error('Error al subir archivo en store Teacher: ' . $e->getMessage());
                    // Continuar con otros archivos si hay error en uno
                }
            }
        }

        // Si es una petición AJAX, devolver JSON
        if ($request->expectsJson() || $request->ajax()) {
            return response()->json([
                'success' => true,
                'message' => 'Docente creado exitosamente',
                'teacher' => [
                    'id' => $teacher->id,
                    'name' => $teacher->name,
                    'academic_degree' => $teacher->academic_degree,
                    'profession' => $teacher->profession,
                    'email' => $teacher->email,
                ]
            ]);
        }

        return redirect()->route('teachers.show', $teacher)
            ->with('success', 'Docente creado exitosamente.');
        
        } catch (\Illuminate\Validation\ValidationException $e) {
            if ($request->expectsJson() || $request->ajax()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Error de validación: ' . implode(', ', $e->validator->errors()->all())
                ], 422);
            }
            throw $e;
        } catch (\Exception $e) {
            Log::error('Error al crear docente: ' . $e->getMessage());
            if ($request->expectsJson() || $request->ajax()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Error al crear el docente: ' . $e->getMessage()
                ], 500);
            }
            return redirect()->back()
                ->withInput()
                ->with('error', 'Error al crear el docente: ' . $e->getMessage());
        }
    }

    public function show(Teacher $teacher)
    {
        $teacher->load('files', 'creator', 'updater', 'modules.program');

        return view('teachers.show', compact('teacher'));
    }

    public function edit(Teacher $teacher)
    {
        $teacher->load('files');

        return view('teachers.edit', compact('teacher'));
    }

    public function update(Request $request, Teacher $teacher)
    {
        $degrees = ['Lic.', 'Ing.', 'Dr.', 'M.Sc.', 'Ph.D.', 'M.Sc. Ing.', 'M.Sc. Lic.', 'M.Sc. Dr.', 'Ph.D. Ing.', 'Ph.D. Lic.'];
        try{
            $validated = $request->validate([
                'name'=>'required|string|max:255',
                'email' => 'nullable|email|max:255|unique:teachers,email,' . $teacher->id,
                'phone' => 'nullable|string|max:20',
                'address' => 'nullable|string|max:255',
                'birth_date' => 'nullable|date',
                'bank' => 'nullable|string|max:255',
                'account_number' => 'nullable|string|max:255',
                'bill' => 'nullable|in:Si,No',
                'esam_worker' => 'nullable|in:Si,No',
                'profession' => 'nullable|string|max:255',
                'ci' => 'required|string|max:20|unique:teachers,ci,' . $teacher->id,
                'academic_degree' => 'required|in:' . implode(',', $degrees),
            ]);

            $teacher->name = $validated['name'];
            $teacher->email = $validated['email'];
            $teacher->phone = $validated['phone'];
            $teacher->address = $validated['address'];
            $teacher->birth_date = $validated['birth_date'];
            $teacher->bank = $validated['bank'];
            $teacher->account_number = $validated['account_number'];
            $teacher->bill = $validated['bill'] ?? 'No';
            $teacher->esam_worker = $validated['esam_worker'] ?? 'No';
            $teacher->profession = $validated['profession'];
            $teacher->ci = $validated['ci'];
            $teacher->academic_degree = $validated['academic_degree'];
            $teacher->updated_by = Auth::id();
            $teacher->save();

            if($request->hasFile('files')){
                $files = $request->file('files');
                $descriptions = $request->input('file_descriptions', []);
                foreach ($files as $index => $file){
                    try {
                        // Subir a Google Drive
                        $googleDriveService = new GoogleDriveService();
                        
                        // Crear estructura jerárquica: "Docentes" -> nombre completo del docente
                        $googleDriveService = new GoogleDriveService();
                        $teacherFullName = trim($teacher->name . ' ' . $teacher->paternal_surname . ' ' . $teacher->maternal_surname);
                        $folderId = $googleDriveService->createHierarchicalFolder('Docentes', $teacherFullName);
                        
                        $driveFileResult = $googleDriveService->uploadFile(
                            $file->getRealPath(),
                            $file->getClientOriginalName(),
                            $file->getClientMimeType(),
                            $descriptions[$index] ?? null,
                            $folderId
                        );

                        $teacherFile = new TeacherFile([
                            'teacher_id' => $teacher->id,
                            'file_path' => '',
                            'file_name' => $file->getClientOriginalName(),
                            'stored_in_drive' => true,
                            'google_drive_id' => $driveFileResult['id'],
                            'file_type' => $file->getClientMimeType(),
                            'description' => $descriptions[$index] ?? null,
                        ]);
                    } catch (\Exception $e) {
                        Log::error('Error subiendo archivo a Google Drive: ' . $e->getMessage());
                        continue; // Saltar este archivo si hay error
                    }

                    $teacherFile->created_by = Auth::id();
                    $teacherFile->save();
                }
            }

            return redirect()->route('teachers.show', $teacher)
                ->with('success', 'Docente actualizado exitosamente.');
        } catch (\Exception $e){
            Log::error('Error al actualizar el docente: ' . $e->getMessage());

            return redirect()->back()
                ->withInput()
                ->withErrors(['error' => 'Ocurrió un error al actualizar el docente. Por favor, inténtelo de nuevo.' . $e->getMessage()]);
        }
    }

    public function destroy(Teacher $teacher)
    {
        try {
            // Eliminar archivos de Google Drive
            $googleDriveService = new GoogleDriveService();
            
            foreach ($teacher->files as $file){
                if ($file->stored_in_drive && $file->google_drive_id) {
                    try {
                        $googleDriveService->deleteFile($file->google_drive_id);
                    } catch (\Exception $e) {
                        Log::error('Error al eliminar archivo de Drive: ' . $e->getMessage());
                    }
                }
            }

            $teacher->delete();

            return redirect()->route('teachers.index')
                ->with('success', 'Docente eliminado exitosamente.');
                
        } catch (\Exception $e) {
            Log::error('Error al eliminar docente: ' . $e->getMessage());
            return redirect()->back()
                ->withErrors(['error' => 'Error al eliminar el docente: ' . $e->getMessage()]);
        }
    }

    public function uploadFile(Request $request, Teacher $teacher)
    {
        $validated = $request->validate([
            'file' => 'required|file|mimes:pdf,doc,docx,jpg,jpeg,png|max:2048',
            'description' => 'nullable|string|max:255',
        ]);

        try{
            $file = $request->file('file');
            
            // Subir a Google Drive
            $googleDriveService = new GoogleDriveService();
            
            // Crear estructura jerárquica: "Docentes" -> nombre completo del docente
            $googleDriveService = new GoogleDriveService();
            $teacherFullName = trim($teacher->name . ' ' . $teacher->paternal_surname . ' ' . $teacher->maternal_surname);
            $folderId = $googleDriveService->createHierarchicalFolder('Docentes', $teacherFullName);
            
            $driveFileResult = $googleDriveService->uploadFile(
                $file->getRealPath(),
                $file->getClientOriginalName(),
                $file->getClientMimeType(),
                $validated['description'] ?? null,
                $folderId
            );

            // Extraer información académica del archivo antes de eliminarlo del servidor
            $academicInfo = null;
            try {
                $academicExtractor = new AcademicInfoExtractor();
                $academicInfo = $academicExtractor->extractFromFile(
                    $file->getRealPath(),
                    $file->getClientOriginalName()
                );
            } catch (\Exception $e) {
                // Log del error pero continúa con la subida del archivo
                Log::warning('No se pudo extraer información académica', [
                    'file' => $file->getClientOriginalName(),
                    'error' => $e->getMessage()
                ]);
            }

            $teacherFile = new TeacherFile([
                'teacher_id' => $teacher->id,
                'file_path' => '', // Ya no necesitamos path local
                'file_name' => $file->getClientOriginalName(),
                'stored_in_drive' => true,
                'google_drive_id' => $driveFileResult['id'],
                'file_type' => $file->getClientMimeType(),
                'description' => $validated['description'] ?? null,
                'academic_info' => $academicInfo,
            ]);

            $teacherFile->created_by = Auth::id();
            $teacherFile->save();

            if($request->wantsJson()){
                return response()->json([
                    'success' => true,
                    'message' => 'Archivo subido exitosamente.',
                    'file' => $teacherFile,
                ]);
            }

            return redirect()->route('teachers.show', $teacher)
                ->with('success', 'Archivo subido exitosamente.');
        } catch(\Exception $e){
            Log::error('Error al subir el archivo del docente: ' . $e->getMessage());

            if($request->wantsJson()){
                return response()->json([
                    'success' => false,
                    'message' => 'Ocurrió un error al subir el archivo del docente. Por favor, inténtelo de nuevo.' . $e->getMessage()
                ], 500);
            }

            return redirect()->back()
                ->withInput()
                ->withErrors(['file' => 'Error al subir el archivo del docente. Por favor, inténtelo de nuevo.' . $e->getMessage()]);
        }
    }

    public function deleteFile(TeacherFile $file)
    {
        try{
            $teacher = $file->teacher;

            // Eliminar SOLO de Google Drive
            if ($file->stored_in_drive && $file->google_drive_id) {
                $googleDriveService = new GoogleDriveService();
                $googleDriveService->deleteFile($file->google_drive_id);
            } else {
                Log::warning('Archivo no tiene ID de Google Drive', [
                    'file_id' => $file->id,
                    'stored_in_drive' => $file->stored_in_drive,
                    'google_drive_id' => $file->google_drive_id
                ]);
            }

            $file->delete();

            return redirect()->route('teachers.show', $teacher)
                ->with('success', 'Archivo eliminado exitosamente.');
        }catch(\Exception $e){
            Log::error('Error al eliminar el archivo del docente: ' . $e->getMessage());

            return redirect()->back()
                ->withErrors(['file' => 'Error al eliminar el archivo del docente. Por favor, inténtelo de nuevo.' . $e->getMessage()]);
        }
    }

    public function serveFile(TeacherFile $file)
    {
        try{
            if ($file->stored_in_drive && $file->google_drive_id) {
                $googleDriveService = new GoogleDriveService();
                $fileContent = $googleDriveService->downloadFile($file->google_drive_id);
                
                $headers = [
                    'Content-Type' => $file->file_type,
                    'Content-Disposition' => 'inline; filename="' . $file->file_name . '"',
                ];
                
                return response($fileContent, 200, $headers);
            } else {
                Log::error('Archivo no disponible en Drive', [
                    'file_id' => $file->id,
                    'stored_in_drive' => $file->stored_in_drive,
                    'google_drive_id' => $file->google_drive_id
                ]);
                abort(404, 'Archivo no encontrado en Google Drive');
            }
        }catch(\Exception $e){
            Log::error('Error al servir el archivo del docente: ' . $e->getMessage());
            abort(500, 'Error al servir el archivo del docente. Por favor, inténtelo de nuevo.' . $e->getMessage());
        }
    }

    public function downloadFile(TeacherFile $file)
    {
        try{
            if ($file->stored_in_drive && $file->google_drive_id) {
                $googleDriveService = new GoogleDriveService();
                $fileContent = $googleDriveService->downloadFile($file->google_drive_id);
                
                $headers = [
                    'Content-Type' => $file->file_type,
                    'Content-Disposition' => 'attachment; filename="' . $file->file_name . '"',
                ];
                
                return response($fileContent, 200, $headers);
            } else {
                Log::error('Archivo no disponible en Drive para descarga', [
                    'file_id' => $file->id,
                    'stored_in_drive' => $file->stored_in_drive,
                    'google_drive_id' => $file->google_drive_id
                ]);
                abort(404, 'Archivo no encontrado en Google Drive');
            }
        }catch(\Exception $e){
            Log::error('Error al descargar el archivo del docente: ' . $e->getMessage());
            abort(500, 'Error al descargar el archivo del docente. Por favor, inténtelo de nuevo.' . $e->getMessage());
        }
    }

    /**
     * Calificar el desempeño de un docente en un módulo específico
     */
    public function rateModule(Request $request, Teacher $teacher, $moduleId)
    {
        try {
            // Validar la entrada
            $request->validate([
                'rating' => 'required|integer|min:1|max:5'
            ]);

            // Buscar el módulo y verificar que pertenezca al docente
            $module = $teacher->modules()->findOrFail($moduleId);

            // Verificar que el módulo esté finalizado
            if ($module->status !== 'CONCLUIDO') {
                return response()->json([
                    'success' => false,
                    'message' => 'Solo se pueden calificar módulos finalizados'
                ], 400);
            }

            // Actualizar la calificación
            $module->update([
                'teacher_rating' => $request->rating
            ]);

            Log::info('Módulo calificado exitosamente', [
                'module_id' => $module->id,
                'teacher_id' => $teacher->id,
                'rating' => $request->rating,
                'user_id' => Auth::id()
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Valoración guardada correctamente',
                'rating' => $request->rating
            ]);

        } catch (\Exception $e) {
            Log::error('Error al calificar módulo: ' . $e->getMessage(), [
                'module_id' => $moduleId,
                'teacher_id' => $teacher->id,
                'request_data' => $request->all()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Error al guardar la valoración'
            ], 500);
        }
    }

    /**
     * Buscar docentes para autocompletado
     */
    public function search(Request $request)
    {
        $query = $request->input('query');
        
        if (empty($query) || strlen($query) < 2) {
            return response()->json([]);
        }

        $teachers = Teacher::where(function($q) use ($query) {
            $q->where('name', 'LIKE', "%{$query}%")
              ->orWhere('profession', 'LIKE', "%{$query}%")
              ->orWhere('email', 'LIKE', "%{$query}%");
        })
        ->select('id', 'name', 'academic_degree', 'profession', 'email')
        ->limit(10)
        ->get();

        return response()->json($teachers);
    }
}

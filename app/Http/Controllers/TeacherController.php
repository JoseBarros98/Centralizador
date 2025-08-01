<?php

namespace App\Http\Controllers;

use App\Models\Teacher;
use App\Models\TeacherFile;
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
        $query = Teacher::query();

        if ($request->has('search') && $request->search != ''){
            $search = $request->search;
            $query->where(function($q) use ($search){
                $q->where('name', 'like', "%{$search}%")
                    ->orWhere('paternal_surname', 'like', "%{$search}%")
                    ->orWhere('maternal_surname', 'like', "%{$search}%");
            });
        }
            
        $teachers = Teacher::orderby('name', 'asc')->paginate(10);

        return view ('teachers.index', compact('teachers'));

    }

    public function create()
    {
        return view('teachers.create');
    }

    public function store(Request $request)
    {
        $degrees = ['Lic.', 'Ing.', 'Dr.', 'M.Sc.', 'Ph.D.', 'M.Sc. Ing.', 'M.Sc. Lic.', 'M.Sc. Dr.', 'Ph.D. Ing.', 'Ph.D. Lic.'];

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'paternal_surname' => 'nullable|string|max:255',
            'maternal_surname' => 'nullable|string|max:255',
            'email' => 'nullable|email|max:255',
            'phone' => 'nullable|string|max:20',
            'address' => 'nullable|string|max:255',
            'birth_date' => 'nullable|date',
            'profession' => 'nullable|string|max:255',
            'ci' => 'required|string|max:20',
            'academic_degree' => 'required|in:' . implode(',', $degrees),
        ]);

        $teacher = new Teacher([
            'name' => $validated['name'],
            'paternal_surname' => $validated['paternal_surname'],
            'maternal_surname' => $validated['maternal_surname'],
            'email' => $validated['email'],
            'phone' => $validated['phone'],
            'address' => $validated['address'],
            'birth_date' => $validated['birth_date'],
            'profession' => $validated['profession'],
            'ci' => $validated['ci'],
            'academic_degree' => $validated['academic_degree'],
        ]);

        $teacher->created_by = Auth::id();
        $teacher->save();

        // Procesar archivos si existen
        if($request->hasFile('files')){
            $files = $request->file('files');
            $descriptions = $request->input('file_descriptions', []);
            foreach ($files as $index => $file){
                $path = $file->store('teachers', 'public');

                $teacherFile = new TeacherFile([
                    'teacher_id' => $teacher->id,
                    'file_path' => $path,
                    'file_name' => $file->getClientOriginalName(),
                    'file_type' => $file->getClientMimeType(),
                    'description' => $descriptions[$index] ?? null,
                ]);

                $teacherFile->created_by = Auth::id();
                $teacherFile->save();
            }
        }

        return redirect()->route('teachers.show', $teacher)
            ->with('success', 'Docente creado exitosamente.');
    }

    public function show(Teacher $teacher)
    {
        $teacher ->load('files', 'creator', 'updater');

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
                'paternal_surname' => 'nullable|string|max:255',
                'maternal_surname' => 'nullable|string|max:255',
                'email' => 'nullable|email|max:255',
                'phone' => 'nullable|string|max:20',
                'address' => 'nullable|string|max:255',
                'birth_date' => 'nullable|date',
                'profession' => 'nullable|string|max:255',
                'ci' => 'required|string|max:20',
                'academic_degree' => 'required|in:' . implode(',', $degrees),
            ]);

            $teacher->name = $validated['name'];
            $teacher->paternal_surname = $validated['paternal_surname'];
            $teacher->maternal_surname = $validated['maternal_surname'];
            $teacher->email = $validated['email'];
            $teacher->phone = $validated['phone'];
            $teacher->address = $validated['address'];
            $teacher->birth_date = $validated['birth_date'];
            $teacher->profession = $validated['profession'];
            $teacher->ci = $validated['ci'];
            $teacher->academic_degree = $validated['academic_degree'];
            $teacher->updated_by = Auth::id();
            $teacher->save();

            if($request->hasFile('files')){
                $files = $request->file('files');
                $descriptions = $request->input('file_descriptions', []);
                foreach ($files as $index => $file){
                    $path = $file->store('teachers', 'public');

                    $teacherFile = new TeacherFile([
                        'teacher_id' => $teacher->id,
                        'file_path' => $path,
                        'file_name' => $file->getClientOriginalName(),
                        'file_type' => $file->getClientMimeType(),
                        'description' => $descriptions[$index] ?? null,
                    ]);

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
        foreach ($teacher->files as $file){
            Storage::disk('public')->delete($file->file_path);
        }

        $teacher->delete();

        return redirect()->route('teachers.index')
            ->with('success', 'Docente eliminado exitosamente.');
    }

    public function uploadFile(Request $request, Teacher $teacher)
    {
        $validated = $request->validate([
            'file' => 'required|file|mimes:pdf,doc,docx,jpg,jpeg,png|max:2048',
            'description' => 'nullable|string|max:255',
        ]);

        try{
            $file = $request->file('file');
            $path = $file->store('teachers', 'public');

            $teacherFile = new TeacherFile([
                'teacher_id' => $teacher->id,
                'file_path' => $path,
                'file_name' => $file->getClientOriginalName(),
                'file_type' => $file->getClientMimeType(),
                'description' => $validated['description'] ?? null,
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

            Storage::disk('public')->delete($file->file_path);

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
            $path = Storage::disk('public')->path($file->file_path);

            if(!file_exists($path)){
                abort(404, 'Archivo no encontrado.');
            }

            $headers = [
                'Content-Type' => $file->file_type,
                'Content-Disposition' => 'inline; filename="' . $file->file_name . '"',
            ];

            return response()->file($path, $headers);
        }catch(\Exception $e){
            Log::error('Error al servir el archivo del docente: ' . $e->getMessage());
            abort(500, 'Error al servir el archivo del docente. Por favor, inténtelo de nuevo.' . $e->getMessage());
        }
    }

    public function downloadFile(TeacherFile $file)
    {
        try{
            $path = Storage::disk('public')->path($file->file_path);

            if(!file_exists($path)){
                abort(404, 'Archivo no encontrado.');
            }

            return response()->download($path, $file->file_name);
        }catch(\Exception $e){
            Log::error('Error al descargar el archivo del docente: ' . $e->getMessage());
            abort(500, 'Error al descargar el archivo del docente. Por favor, inténtelo de nuevo.' . $e->getMessage());
        }
    }
}
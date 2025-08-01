<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Inscription;
use App\Models\Program;
use App\Models\Receipt;
use App\Models\Document;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Log;
use Illuminate\Routing\Controller;
use Illuminate\Validation\Rule;
use Spatie\Permission\Models\Role;
use Symfony\Component\HttpFoundation\StreamedResponse;

class InscriptionController extends Controller
{
    public function __construct()
    {
        $this->middleware(['permission:inscription.view'])->only(['index', 'show', 'serveCommitmentLetter']);
        $this->middleware(['permission:inscription.create'])->only(['create', 'store']);
        $this->middleware(['permission:inscription.edit'])->only(['edit', 'update', 'updateDocuments', 'uploadCommitmentLetter', 'deleteCommitmentLetter', 'updateDocumentObservations']);
        $this->middleware(['permission:inscription.delete'])->only(['destroy']);
    }

    public function index(Request $request)
    {
        // Construir la consulta base
        $baseQuery = Inscription::with(['program', 'creator', 'updater']);
        
        // Filtros de fecha - Ahora con opción para mostrar todos los meses
        if ($request->has('month') && $request->month != 'all' && $request->has('year') && $request->year != 'all') {
            $startDate = Carbon::createFromDate($request->year, $request->month, 1)->startOfMonth();
            $endDate = $startDate->copy()->endOfMonth();
            $baseQuery->whereBetween('inscription_date', [$startDate, $endDate]);
        } 
        // Solo filtrar por año si se especifica
        elseif ($request->has('year') && $request->year != 'all') {
            $startDate = Carbon::createFromDate($request->year, 1, 1)->startOfYear();
            $endDate = $startDate->copy()->endOfYear();
            $baseQuery->whereBetween('inscription_date', [$startDate, $endDate]);
        }
        // Solo filtrar por mes si se especifica (usando el año actual)
        elseif ($request->has('month') && $request->month != 'all') {
            $startDate = Carbon::createFromDate(Carbon::now()->year, $request->month, 1)->startOfMonth();
            $endDate = $startDate->copy()->endOfMonth();
            $baseQuery->whereBetween('inscription_date', [$startDate, $endDate]);
        }
        // Si no se especifica ningún filtro de fecha o se selecciona 'all' para ambos, mostrar el mes actual
        elseif (!$request->has('month') || !$request->has('year')) {
            // Por defecto, mostrar el mes actual
            $startDate = Carbon::now()->startOfMonth();
            $endDate = Carbon::now()->endOfMonth();
            $baseQuery->whereBetween('inscription_date', [$startDate, $endDate]);
        }
        
        // Filtro por estado
        if ($request->has('status') && $request->status != '') {
            $baseQuery->where('status', $request->status);
        }
        
        //Filtro por programa
        if ($request->has('program_id') && $request->program_id != '') {
            $baseQuery->where('program_id', $request->program_id);
        }
        
        // Filtro por usuario que realizó la inscripción (created_by)
        if ($request->has('created_by') && $request->created_by != '') {
            $baseQuery->where('created_by', $request->created_by);
        }
        
        // Campo de búsqueda
        if ($request->has('search') && $request->search != '') {
            $search = $request->search;
            $baseQuery->where(function($q) use ($search) {
                $q->where('code', 'like', "%{$search}%")
                  ->orWhere('first_name', 'like', "%{$search}%")
                  ->orWhere('paternal_surname', 'like', "%{$search}%")
                  ->orWhere('maternal_surname', 'like', "%{$search}%")
                  ->orWhere('ci', 'like', "%{$search}%");
            });
        }
        
        // Clonar la consulta para estadísticas
        $statsQuery = clone $baseQuery;
        
        // Obtener inscripciones paginadas para la tabla
        $inscriptions = $baseQuery->orderBy('inscription_date', 'desc')->paginate(15)->withQueryString();
        
        // Obtener programas activos para el filtro
        $programs = Program::where('active', true)->pluck('name', 'id');
        
        // Obtener usuarios que han creado inscripciones
        $creators = User::role('marketing')
                        ->whereIn('id', Inscription::select('created_by')->distinct()->pluck('created_by'))
                        ->where('active', true)
                        ->pluck('name', 'id');
        
        // Calcular estadísticas
        $totalCount = $statsQuery->count();
        $completoCount = (clone $statsQuery)->where('status', 'Completo')->count();
        $completandoCount = (clone $statsQuery)->where('status', 'Completando')->count();
        $adelantoCount = (clone $statsQuery)->where('status', 'Adelanto')->count();
        $totalPaid = (clone $statsQuery)->sum('total_paid');
        
        $stats = [
            'total' => $totalCount,
            'completo' => $completoCount,
            'completando' => $completandoCount,
            'adelanto' => $adelantoCount,
            'total_paid' => $totalPaid,
        ];
        
        // Determinar el título de las estadísticas
        $statsTitle = 'Estadísticas';
        if ($request->has('month') && $request->month != 'all' && $request->has('year') && $request->year != 'all') {
            $monthName = Carbon::createFromDate(null, $request->month, 1)->translatedFormat('F');
            $statsTitle = "Estadísticas de {$monthName} {$request->year}";
        } elseif ($request->has('year') && $request->year != 'all') {
            $statsTitle = "Estadísticas del Año {$request->year}";
        } elseif ($request->has('month') && $request->month != 'all') {
            $monthName = Carbon::createFromDate(null, $request->month, 1)->translatedFormat('F');
            $statsTitle = "Estadísticas de {$monthName}";
        } elseif ($request->has('program_id') && $request->program_id != '') {
            $program = Program::find($request->program_id);
            $statsTitle = "Estadísticas del Programa: {$program->name}";
        }
        
        return view('inscriptions.index', compact('inscriptions', 'programs', 'creators', 'stats', 'statsTitle'));
    }

    public function create()
    {
        $programs = Program::where('active', true)->pluck('name', 'id');
        
        return view('inscriptions.create', compact('programs'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'first_name' => 'required|string|max:255',
            'paternal_surname' => 'nullable|string|max:255',
            'maternal_surname' => 'nullable|string|max:255',
            'ci' => 'required|string|max:20',
            'phone' => 'required|string|max:20',
            'program_id' => 'required|exists:programs,id',
            'payment_plan' => 'required|in:credito,contado',
            'payment_method' => 'required|in:QR,efectivo,deposito',
            'enrollment_fee' => 'required|numeric|min:0',
            'first_installment' => 'required|numeric|min:0',
            'total_paid' => 'required|numeric|min:0',
            'status' => 'required|in:Completo,Completando,Adelanto',
            'profession' => 'required|string|max:255',
            'residence' => 'required|string|max:255',
            'location' => 'required|string|max:255',
            'inscription_date' => 'required|date',
            'notes' => 'nullable|string',
            'certification' => 'nullable|string',
            'receipt_file' => 'nullable|file|mimes:pdf,jpg,jpeg,png|max:5120',
            'document_types' => 'nullable|array',
            'document_types.*' => 'nullable|string|max:255',
            'document_files' => 'nullable|array',
            'document_files.*' => 'nullable|file|mimes:pdf,jpg,jpeg,png,doc,docx,xls,xlsx|max:10240',
            'document_descriptions' => 'nullable|array',
            'document_descriptions.*' => 'nullable|string|max:255',
            'gender' => 'required|in:Masculino,Femenino',
        ]);
        
        // Verificar si ya existe una inscripción con el mismo CI
        $existingInscription = Inscription::where('ci', $validated['ci'])->first();
        
        if ($existingInscription) {
            // Obtener el mes y año de la inscripción existente y la nueva
            $existingDate = Carbon::parse($existingInscription->inscription_date);
            $newDate = Carbon::parse($validated['inscription_date']);
            
            $sameMonth = $existingDate->month == $newDate->month && $existingDate->year == $newDate->year;
            
            // Caso 1: Mismo mes - Adelanto a Completando = Completo
            if ($sameMonth && $existingInscription->status == 'Adelanto' && $validated['status'] == 'Completando') {
                // Actualizar a Completo
                $existingInscription->status = 'Completo';
                $existingInscription->total_paid += $validated['total_paid'];
                $existingInscription->updated_by = Auth::id();
                $existingInscription->save();
                
                // Si hay un archivo de recibo, guardarlo
                if ($request->hasFile('receipt_file')) {
                    $path = $request->file('receipt_file')->store('receipts', 'public');
                    
                    $receipt = new Receipt();
                    $receipt->inscription_id = $existingInscription->id;
                    $receipt->file_path = $path;
                    $receipt->created_by = Auth::id();
                    $receipt->save();
                }
                
            // Guardar múltiples documentos
            $documentTypes = $request->input('document_types', []);
            $documentDescriptions = $request->input('document_descriptions', []);
            $documentFiles = $request->file('document_files', []);

            foreach ($documentTypes as $index => $type) {
                if (isset($documentFiles[$index]) && $documentFiles[$index]) {
                    $file = $documentFiles[$index];
                    $path = $file->store('documents', 'public');

                    $document = new Document();
                    $document->inscription_id = $existingInscription->id;
                    $document->file_path = $path;
                    $document->file_name = $file->getClientOriginalName();
                    $document->file_type = $file->getClientMimeType();
                    $document->document_type = $type;
                    $document->description = $documentDescriptions[$index] ?? null;
                    $document->created_by = Auth::id();
                    $document->save();
                }
            }
                
                return redirect()->route('inscriptions.index')
                    ->with('success', 'Inscripción actualizada a estado Completo correctamente.');
            } 
            // Caso 2: Mes diferente - Mantener registros separados
            else if (!$sameMonth) {
                // Generar código único
                $code = Inscription::generateCode($validated['first_name'], $validated['ci']);
                
                // Crear nueva inscripción para el mes actual
                $inscription = new Inscription($validated);
                $inscription->code = $code;
                $inscription->created_by = Auth::id();
                $inscription->save();
                
                // Si hay un archivo de recibo, guardarlo
                if ($request->hasFile('receipt_file')) {
                    $path = $request->file('receipt_file')->store('receipts', 'public');
                    
                    $receipt = new Receipt();
                    $receipt->inscription_id = $inscription->id;
                    $receipt->file_path = $path;
                    $receipt->created_by = Auth::id();
                    $receipt->save();
                }
                
                // Guardar múltiples documentos
                $documentTypes = $request->input('document_types', []);
                $documentDescriptions = $request->input('document_descriptions', []);
                $documentFiles = $request->file('document_files', []);

                foreach ($documentTypes as $index => $type) {
                    if (isset($documentFiles[$index]) && $documentFiles[$index]) {
                        $file = $documentFiles[$index];
                        $path = $file->store('documents', 'public');

                        $document = new Document();
                        $document->inscription_id = $inscription->id;
                        $document->file_path = $path;
                        $document->file_name = $file->getClientOriginalName();
                        $document->file_type = $file->getClientMimeType();
                        $document->document_type = $type;
                        $document->description = $documentDescriptions[$index] ?? null;
                        $document->created_by = Auth::id();
                        $document->save();
                    }
                }
                
                return redirect()->route('inscriptions.index')
                    ->with('success', 'Nueva inscripción registrada correctamente.');
            }
            else {
                // Si no cumple con los casos especiales, mostrar error de CI duplicado
                return redirect()->back()
                    ->withInput()
                    ->withErrors(['ci' => 'Ya existe una inscripción con este CI en el mismo mes.']);
            }
        }
        
        // Si no existe una inscripción previa, crear una nueva
        $code = Inscription::generateCode($validated['first_name'], $validated['ci']);
        
        // Crear nueva inscripción
        $inscription = new Inscription($validated);
        $inscription->code = $code;
        $inscription->created_by = Auth::id();
        $inscription->save();
        
        // Si hay un archivo de recibo, guardarlo
        if ($request->hasFile('receipt_file')) {
            $path = $request->file('receipt_file')->store('receipts', 'public');
            $receipt = new Receipt();
            $receipt->inscription_id = $inscription->id;
            $receipt->file_path = $path;
            $receipt->created_by = Auth::id();
            $receipt->save();
        }

        // Guardar múltiples documentos
        $documentTypes = $request->input('document_types', []);
        $documentDescriptions = $request->input('document_descriptions', []);
        $documentFiles = $request->file('document_files', []);

        foreach ($documentTypes as $index => $type) {
            if (isset($documentFiles[$index]) && $documentFiles[$index]) {
                $file = $documentFiles[$index];
                $path = $file->store('documents', 'public');

                $document = new Document();
                $document->inscription_id = $inscription->id;
                $document->file_path = $path;
                $document->file_name = $file->getClientOriginalName();
                $document->file_type = $file->getClientMimeType();
                $document->document_type = $type;
                $document->description = $documentDescriptions[$index] ?? null;
                $document->created_by = Auth::id();
                $document->save();
            }
        }
        
        return redirect()->route('inscriptions.index')
            ->with('success', 'Inscripción creada correctamente.');
    }

    public function show(Inscription $inscription)
    {
        // Cargar los recibos relacionados
        $inscription->load('receipts');
        
        return view('inscriptions.show', compact('inscription'));
    }

    public function edit(Inscription $inscription)
    {
        $programs = Program::where('active', true)->pluck('name', 'id');
        
        // Cargar los recibos y documentos relacionados
        $inscription->load('receipts', 'documents');
        
        return view('inscriptions.edit', compact('inscription', 'programs'));
    }

    public function update(Request $request, Inscription $inscription)
    {
        $validated = $request->validate([
            'first_name' => 'required|string|max:255',
            'paternal_surname' => 'nullable|string|max:255',
            'maternal_surname' => 'nullable|string|max:255',
            'ci' => 'required|string|max:20',
            'phone' => 'required|string|max:20',
            'program_id' => 'required|exists:programs,id',
            'payment_plan' => 'required|in:credito,contado',
            'payment_method' => 'required|in:QR,efectivo,deposito',
            'enrollment_fee' => 'required|numeric|min:0',
            'first_installment' => 'required|numeric|min:0',
            'total_paid' => 'required|numeric|min:0',
            'status' => 'required|in:Completo,Completando,Adelanto',
            'profession' => 'required|string|max:255',
            'residence' => 'required|string|max:255',
            'location' => 'required|string|max:255',
            'inscription_date' => 'required|date',
            'notes' => 'nullable|string',
            'certification' => 'nullable|string',
            'receipt_file' => 'nullable|file|mimes:pdf,jpg,jpeg,png|max:5120',
            'document_types' => 'nullable|array',
            'document_types.*' => 'nullable|string|max:255',
            'document_files' => 'nullable|array',
            'document_files.*' => 'nullable|file|mimes:pdf,jpg,jpeg,png,doc,docx,xls,xlsx|max:10240',
            'document_descriptions' => 'nullable|array',
            'document_descriptions.*' => 'nullable|string|max:255',
            'gender' => 'required|in:Masculino,Femenino',
        ]);
        
        // Actualizar inscripción
        $inscription->update($validated);
        $inscription->updated_by = Auth::id();
        $inscription->save();
        
        // Si hay un archivo de recibo, guardarlo
        if ($request->hasFile('receipt_file')) {
            $path = $request->file('receipt_file')->store('receipts', 'public');
            
            $receipt = new Receipt();
            $receipt->inscription_id = $inscription->id;
            $receipt->file_path = $path;
            $receipt->created_by = Auth::id();
            $receipt->save();
        }
        
        // Guardar múltiples documentos
        $documentTypes = $request->input('document_types', []);
        $documentDescriptions = $request->input('document_descriptions', []);
        $documentFiles = $request->file('document_files', []);

        foreach ($documentTypes as $index => $type) {
            if (isset($documentFiles[$index]) && $documentFiles[$index]) {
                $file = $documentFiles[$index];
                $path = $file->store('documents', 'public');

                $document = new Document();
                $document->inscription_id = $inscription->id;
                $document->file_path = $path;
                $document->file_name = $file->getClientOriginalName();
                $document->file_type = $file->getClientMimeType();
                $document->document_type = $type;
                $document->description = $documentDescriptions[$index] ?? null;
                $document->created_by = Auth::id();
                $document->save();
            }
        }
        
        return redirect()->route('inscriptions.index')
            ->with('success', 'Inscripción actualizada correctamente.');
    }

    public function destroy(Inscription $inscription)
    {
        // Eliminar los recibos asociados
        foreach ($inscription->receipts as $receipt) {
            // Eliminar el archivo físico
            Storage::disk('public')->delete($receipt->file_path);
            // Eliminar el registro
            $receipt->delete();
        }
        
        // Eliminar la inscripción
        $inscription->delete();
        
        return redirect()->route('inscriptions.index')
            ->with('success', 'Inscripción eliminada correctamente.');
    }

    public function updateDocuments(Request $request, Inscription $inscription)
    {
        try {
            $validated = $request->validate([
                'field' => ['required', Rule::in([
                    'has_identity_card',
                    'has_degree_title',
                    'has_academic_diploma',
                    'has_birth_certificate',
                    'has_commitment_letter'
                ])],
                'value' => 'required|boolean',
            ]);

            $field = $validated['field'];
            $value = $validated['value'];

            $inscription->$field = $value;
            $inscription->updated_by = Auth::id();
            $inscription->save();

            return response()->json([
                'success' => true,
                'message' => 'Documento actualizado correctamente',
                'field' => $field,
                'value' => $value
            ]);
        } catch (\Exception $e) {
            Log::error("Error al actualizar documento: " . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Error al actualizar el documento: ' . $e->getMessage()
            ], 500);
        }
    }
    
    public function updateAccess(Request $request, Inscription $inscription)
    {
        try{
            $validated = $request->validate([
                'field' => ['required', Rule::in([
                    'was_added_to_the_group',
                    'accesses_were_sent',
                    'mail_was_sent',
                ])],
                'value' => 'required|boolean',
            ]);

            $field = $validated['field'];
            $value = $validated['value'];

            $inscription->$field = $value;
            $inscription->updated_by = Auth::id();
            $inscription->save();

            return response()->json([
                'success' => true,
                'message' => 'Accesos actualizado correctamente',
                'field' => $field,
                'value' => $value
            ]);
        } catch (\Exception $e){
            Log::error('Error al actualizar accesos: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Error al actualizar accesos: ' .$e->getMessage()
            ],500);
        }
    }

    public function updateAcademicStatus(Request $request, Inscription $inscription)
    {
        $validated = $request->validate([
            'academic_status' => ['required', Rule::in(['Activo', 'Retirado', 'Congelado', 'Cambio'])],
        ]);

        $inscription->academic_status = $validated['academic_status'];
        $inscription->updated_by = Auth::id();
        $inscription->save();

        return response()->json([
            'success' => true,
            'message' => 'Estado académico actualizado correctamente',
            'value' => $inscription->academic_status
        ]);
    }

    public function uploadCommitmentLetter(Request $request, Inscription $inscription)
    {
        try {
            $request->validate([
                'commitment_letter' => 'required|file|mimes:pdf,jpg,jpeg,png,doc,docx,xls,xlsx|max:10240',
                'description' => 'nullable|string|max:255',
            ]);

            // Eliminar documento anterior de tipo compromiso si existe
            $oldDocument = $inscription->documents()->where('document_type', 'compromiso')->first();
            if ($oldDocument) {
                Storage::disk('public')->delete($oldDocument->file_path);
                $oldDocument->delete();
            }

            $file = $request->file('commitment_letter');
            $fileName = time() . '_' . $file->getClientOriginalName();
            $filePath = $file->storeAs('documents', $fileName, 'public');

            // Crear registro en Document
            $document = new Document();
            $document->inscription_id = $inscription->id;
            $document->file_path = $filePath;
            $document->file_name = $file->getClientOriginalName();
            $document->file_type = $file->getClientMimeType();
            $document->document_type = 'compromiso';
            $document->description = $request->input('description');
            $document->created_by = Auth::id();
            $document->save();



            if ($request->ajax() || $request->wantsJson()) {
                return response()->json([
                    'success' => true,
                    'message' => 'Carta de compromiso subida correctamente',
                    'file_url' => route('documents.serve', $document),
                    'file_name' => $fileName
                ]);
            }

            return back()->with('success', 'Carta de compromiso subida correctamente');
        } catch (\Exception $e) {
            Log::error("Error al subir carta de compromiso: " . $e->getMessage());

            if ($request->ajax() || $request->wantsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Error al subir la carta de compromiso: ' . $e->getMessage()
                ], 500);
            }

            return back()->with('error', 'Error al subir la carta de compromiso: ' . $e->getMessage());
        }
    }

    public function deleteCommitmentLetter(Inscription $inscription, $documentId)
    {
        try {
            $document = $inscription->documents()->where('id', $documentId)->first();
            if ($document) {
                Storage::disk('public')->delete($document->file_path);
                $document->delete();

                if (request()->ajax() || request()->wantsJson()) {
                    return response()->json([
                        'success' => true,
                        'message' => 'Documento eliminado correctamente'
                    ]);
                }
                return back()->with('success', 'Documento eliminado correctamente');
            }

            if (request()->ajax() || request()->wantsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'No se encontró el documento para eliminar'
                ], 404);
            }
            return back()->with('error', 'No se encontró el documento para eliminar');
        } catch (\Exception $e) {
            Log::error("Error al eliminar documento: " . $e->getMessage());
            if (request()->ajax() || request()->wantsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Error al eliminar el documento: ' . $e->getMessage()
                ], 500);
            }
            return back()->with('error', 'Error al eliminar el documento: ' . $e->getMessage());
        }
    }

    public function serveCommitmentLetter(Inscription $inscription, $documentId)
    {
        $document = $inscription->documents()->where('id', $documentId)->first();
        if (!$document) {
            abort(404, 'Archivo no encontrado');
        }
        try {
            if (!Storage::disk('public')->exists($document->file_path)) {
                abort(404, 'Archivo no encontrado en el disco');
            }
            $fullPath = Storage::disk('public')->path($document->file_path);
            $mimeType = mime_content_type($fullPath);
            $fileContent = Storage::disk('public')->get($document->file_path);
            $fileName = basename($document->file_path);
            return response($fileContent)
                ->header('Content-Type', $mimeType)
                ->header('Content-Disposition', 'inline; filename="' . $fileName . '"');
        } catch (\Exception $e) {
            Log::error("Error al servir documento: " . $e->getMessage());
            abort(500, 'Error al procesar el archivo');
        }
    }

    public function updateDocumentObservations(Request $request, Inscription $inscription)
    {
        try {
            $validated = $request->validate([
                'document_observations' => 'nullable|string',
            ]);

            $inscription->document_observations = $validated['document_observations'];
            $inscription->updated_by = Auth::id();
            $inscription->save();

            if ($request->ajax() || $request->wantsJson()) {
                return response()->json([
                    'success' => true,
                    'message' => 'Observaciones actualizadas correctamente'
                ]);
            }

            return back()->with('success', 'Observaciones actualizadas correctamente');
        } catch (\Exception $e) {
            Log::error("Error al actualizar observaciones: " . $e->getMessage());
            
            if ($request->ajax() || $request->wantsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Error al actualizar las observaciones: ' . $e->getMessage()
                ], 500);
            }
            
            return back()->with('error', 'Error al actualizar las observaciones: ' . $e->getMessage());
        }
    }

    public function monthlyReport(Request $request)
    {
        $year = $request->input('year', Carbon::now()->year);
        $month = $request->input('month', Carbon::now()->month);
        
        $startDate = Carbon::createFromDate($year, $month, 1)->startOfMonth();
        $endDate = $startDate->copy()->endOfMonth();
        
        $query = Inscription::with(['program', 'creator', 'location']);
        
        $inscriptions = $query->whereBetween('inscription_date', [$startDate, $endDate])->get();
            
        $stats = [
            'total' => $inscriptions->count(),
            'completo' => $inscriptions->where('status', 'Completo')->count(),
            'completando' => $inscriptions->where('status', 'Completando')->count(),
            'adelanto' => $inscriptions->where('status', 'Adelanto')->count(),
            'total_paid' => $inscriptions->sum('total_paid'),
        ];
        
        $programStats = $inscriptions->groupBy('program_id')
            ->map(function ($items, $key) {
                $program = Program::find($key);
                return [
                    'name' => $program ? $program->name : 'Desconocido',
                    'count' => $items->count(),
                    'total_paid' => $items->sum('total_paid'),
                ];
            });
            
        $advisorStats = $inscriptions->groupBy('created_by')
            ->map(function ($items, $key) {
                $user = User::find($key);
                return [
                    'name' => $user ? $user->name : 'Desconocido',
                    'count' => $items->count(),
                    'total_paid' => $items->sum('total_paid'),
                ];
            });
            
        return view('reports.monthly', compact('inscriptions', 'stats', 'programStats', 'advisorStats', 'year', 'month'));
    }

    public function yearlyReport(Request $request)
    {
        $year = $request->input('year', Carbon::now()->year);
        
        $startDate = Carbon::createFromDate($year, 1, 1)->startOfYear();
        $endDate = $startDate->copy()->endOfYear();
        
        $query = Inscription::with(['program', 'creator', 'location']);
        
        $inscriptions = $query->whereBetween('inscription_date', [$startDate, $endDate])->get();
        
        // Agrupar inscripciones por CI para identificar casos especiales
        $inscriptionsByCi = $inscriptions->groupBy('ci');
        
        // Crear una copia de las inscripciones para modificar los estados en el reporte anual
        $adjustedInscriptions = $inscriptions->map(function ($inscription) use ($inscriptionsByCi) {
            $inscriptionCopy = clone $inscription;
            
            // Si hay más de una inscripción con el mismo CI
            if ($inscriptionsByCi[$inscription->ci]->count() > 1) {
                $ciInscriptions = $inscriptionsByCi[$inscription->ci]->sortBy('inscription_date');
                
                // Verificar si hay un "Adelanto" previo y este es "Completando"
                $hasAdelantoBefore = false;
                foreach ($ciInscriptions as $ciInscription) {
                    if ($ciInscription->id == $inscription->id) {
                        // Si este es "Completando" y hubo un "Adelanto" previo, mostrarlo como "Completo"
                        if ($inscription->status == 'Completando' && $hasAdelantoBefore) {
                            $inscriptionCopy->status = 'Completo';
                        }
                        break;
                    }
                    
                    if ($ciInscription->status == 'Adelanto') {
                        $hasAdelantoBefore = true;
                    }
                }
            }
            
            return $inscriptionCopy;
        });
            
        $monthlyStats = [];
        
        for ($month = 1; $month <= 12; $month++) {
            $monthStart = Carbon::createFromDate($year, $month, 1)->startOfMonth();
            $monthEnd = $monthStart->copy()->endOfMonth();
            
            $monthInscriptions = $adjustedInscriptions->filter(function ($inscription) use ($monthStart, $monthEnd) {
                return $inscription->inscription_date->between($monthStart, $monthEnd);
            });
            
            $monthlyStats[$month] = [
                'month' => $monthStart->format('F'),
                'total' => $monthInscriptions->count(),
                'completo' => $monthInscriptions->where('status', 'Completo')->count(),
                'completando' => $monthInscriptions->where('status', 'Completando')->count(),
                'adelanto' => $monthInscriptions->where('status', 'Adelanto')->count(),
                'total_paid' => $monthInscriptions->sum('total_paid'),
            ];
        }
        
        $programStats = $inscriptions->groupBy('program_id')
            ->map(function ($items, $key) {
                $program = Program::find($key);
                return [
                    'name' => $program ? $program->name : 'Desconocido',
                    'count' => $items->count(),
                    'total_paid' => $items->sum('total_paid'),
                ];
            });
            
        return view('reports.yearly', compact('inscriptions', 'monthlyStats', 'programStats', 'year'));
    }

    public function showForProgram(Program $program, Inscription $inscription)
    {
        // Cargar relaciones necesarias
        $inscription->load('documents', 'receipts');
        return view('programs.inscription_show', compact('program', 'inscription'));
    }

    public function uploadDocumentForProgram(Request $request, Program $program, Inscription $inscription)
    {
        $request->validate([
            'document_types' => 'required|array',
            'document_types.*' => 'required|string|max:255',
            'document_files' => 'required|array',
            'document_files.*' => 'required|file|mimes:pdf,jpg,jpeg,png,doc,docx,xls,xlsx|max:10240',
            'document_descriptions' => 'nullable|array',
            'document_descriptions.*' => 'nullable|string|max:255',
        ]);

        $documentTypes = $request->input('document_types', []);
        $documentDescriptions = $request->input('document_descriptions', []);
        $documentFiles = $request->file('document_files', []);

        foreach ($documentTypes as $index => $type) {
            if (isset($documentFiles[$index]) && $documentFiles[$index]) {
                $file = $documentFiles[$index];
                $path = $file->store('documents', 'public');

                $document = new \App\Models\Document();
                $document->inscription_id = $inscription->id;
                $document->file_path = $path;
                $document->file_name = $file->getClientOriginalName();
                $document->file_type = $file->getClientMimeType();
                $document->document_type = $type;
                $document->description = $documentDescriptions[$index] ?? null;
                $document->created_by = Auth::id();
                $document->save();
            }
        }

        return redirect()->route('programs.inscription_show', ['program' => $program->id, 'inscription' => $inscription->id])
            ->with('success', 'Archivo(s) subido(s) correctamente.');
    }
}

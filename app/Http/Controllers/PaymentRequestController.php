<?php

namespace App\Http\Controllers;

use App\Models\PaymentRequest;
use App\Models\Program;
use App\Models\Module;
use App\Models\Teacher;
use App\Models\Inscription;
use App\Exports\PaymentRequestsExport;
use Illuminate\Http\Request;
use Maatwebsite\Excel\Facades\Excel;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Routing\Controller;
use PhpOffice\PhpSpreadsheet\IOFactory;

class PaymentRequestController extends Controller
{
    public function __construct()
    {
        $this->middleware('permission:payment_request.view')->only(['index', 'show']);
        $this->middleware('permission:payment_request.create')->only(['create', 'store']);
        $this->middleware(function ($request, $next) {
            $user = $request->user();
            if ($user && ($user->can('payment_request.edit') || $user->can('payment_request.edit_own'))) {
                return $next($request);
            }
            abort(403);
        })->only(['edit', 'update']);
        $this->middleware(function ($request, $next) {
            $user = $request->user();
            if ($user && ($user->can('payment_request.delete') || $user->can('payment_request.delete_own'))) {
                return $next($request);
            }
            abort(403);
        })->only(['destroy']);
    }

    // Mostrar todas las solicitudes de pago
    public function index(Request $request)
    {
        $query = PaymentRequest::with(['module.program', 'module.teacher', 'tutoringTeacher']);

        // Obtener todos los años disponibles en la base de datos
        $availableYears = PaymentRequest::selectRaw('YEAR(request_date) as year')
            ->distinct()
            ->whereNotNull('request_date')
            ->orderBy('year', 'desc')
            ->pluck('year')
            ->toArray();
        
        // Si no hay años, usar el actual
        if (empty($availableYears)) {
            $availableYears = [now()->format('Y')];
        }

        // Búsqueda por número de planilla
        if ($request->filled('payroll_number')) {
            $query->where('payroll_number', 'like', "%{$request->payroll_number}%");
        }

        // Búsqueda por docente (nombre del profesor del módulo o tutor)
        if ($request->filled('teacher')) {
            $search = $request->teacher;
            $query->where(function($q) use ($search) {
                $q->whereHas('module.teacher', function($q) use ($search) {
                    $q->where('first_name', 'like', "%{$search}%")
                      ->orWhere('last_name', 'like', "%{$search}%");
                })
                ->orWhereHas('tutoringTeacher', function($q) use ($search) {
                    $q->where('first_name', 'like', "%{$search}%")
                      ->orWhere('last_name', 'like', "%{$search}%");
                });
            });
        }

        // Búsqueda por programa
        if ($request->filled('program')) {
            $query->whereHas('module.program', function($q) use ($request) {
                $q->where('name', 'like', "%{$request->program}%");
            });
        }

        // Búsqueda por módulo
        if ($request->filled('module')) {
            $query->whereHas('module', function($q) use ($request) {
                $q->where('name', 'like', "%{$request->module}%");
            });
        }

        // Filtro por estado
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        // Filtro por tipo de solicitud
        if ($request->filled('request_type')) {
            $query->where('request_type', $request->request_type);
        }

        // Filtro por año y mes
        $currentYear = now()->format('Y');
        $currentMonth = now()->format('m');
        
        // Aplicar filtro de año
        if ($request->filled('year')) {
            $year = $request->year;
            $query->whereRaw("YEAR(request_date) = ?", [$year]);
        } else {
            // Si no hay año especificado, filtrar por el año actual por defecto
            $query->whereRaw("YEAR(request_date) = ?", [$currentYear]);
        }
        
        // Aplicar filtro de mes
        if ($request->filled('month')) {
            $month = str_pad($request->month, 2, '0', STR_PAD_LEFT);
            $query->whereRaw("MONTH(request_date) = ?", [$month]);
        } elseif (!$request->has('month')) {
            // Solo aplicar mes actual por defecto si el parámetro no vino en la URL
            if (!$request->filled('year') || $request->year == $currentYear) {
                $query->whereRaw("MONTH(request_date) = ?", [$currentMonth]);
            }
        }
        // Si month está presente pero vacío (="Todos"), no aplicar filtro de mes

        $paymentRequests = $query->orderBy('created_at', 'desc')->paginate(10)->appends(request()->query());
        return view('payment_requests.index', compact('paymentRequests', 'availableYears'));
    }

    // Actualizar número de estudiantes
    public function updateStudents(Request $request, PaymentRequest $paymentRequest)
    {
        $request->validate([
            'students_count' => 'required|integer|min:0',
        ]);

        try {
            // Actualizar el campo total_active_students en payment_requests
            $paymentRequest->update([
                'total_active_students' => $request->students_count
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Número de estudiantes actualizado correctamente'
            ]);
        } catch (\Exception $e) {
            Log::error("Error actualizando estudiantes: " . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Error al actualizar'
            ], 500);
        }
    }

    // Mostrar el formulario para crear una nueva solicitud de pago
    public function create(Request $request)
    {
        $programs = Program::all();
        $modules = Module::with(['program', 'teacher'])->get();
        $teachers = Teacher::all();

        // Si viene un module_id desde la URL, cargar ese módulo específico
        $selectedModule = null;
        if ($request->has('module_id')) {
            $selectedModule = Module::with(['program', 'teacher'])->find($request->module_id);
        }

        return view('payment_requests.create', compact('programs', 'modules', 'teachers', 'selectedModule'));
    }

    public function buscarPorPrograma(Request $request)
    {
        $request->validate([
            'accounting_code' => 'required|string|max:255',
        ]);

        $program = Program::where('accounting_code', $request->accounting_code)
                            ->with(['modules'=> function($query){
                                    $query->with('teacher')
                                            ->where('status', 'Finalizado')
                                            ->orderBy('start_date', 'desc');
        }])->first();

        if(!$program){
            return back()->with('error', 'No se encontró ningún programa con el código contable proporcionado.');
        }

        if($program->modules->isEmpty()){
            return back()->with('error', 'Este programa no tiene módulos finalizados.');
        }

        // Agregar variables necesarias para la vista
        $programs = Program::all();
        $modules = Module::with(['program', 'teacher'])->get();
        $teachers = Teacher::all();

        return view('payment_requests.create', compact('program', 'programs', 'modules', 'teachers'));
    }

    public function buscarPorTeacher(Request $request)
    {
        $request->validate([
            'ci' => 'required_without:nombre|string',
            'nombre' => 'required_without:ci|string'
        ], [
            'ci.required_without' => 'Debe ingresar CI o nombre del docente',
            'nombre.required_without' => 'Debe ingresar CI o nombre del docente'
        ]);

        $query = Teacher::query();
        
        if ($request->filled('ci')) {
            $query->where('ci', $request->ci);
        } else {
            $query->where('nombre', 'like', "%{$request->nombre}%");
        }

        $teacher = $query->with(['modules' => function($q) {
                    $q->with('program')
                    ->where('status', 'Finalizado')
                    ->orderBy('start_date', 'desc');
                }])
                ->first();
        
        if (!$teacher) {
            return back()->with('error', 'Docente no encontrado');
        }

        if ($teacher->modules->isEmpty()) {
            return back()->with('error', 'Este docente no tiene módulos finalizados');
        }

        // Calcular el total de estudiantes activos para cada módulo
        foreach ($teacher->modules as $module) {
            $module->total_active_students = Inscription::where('program_id', $module->program_id)
                                                    ->where('academic_status', 'Activo')
                                                    ->count();
        }

        // Agregar variables necesarias para la vista
        $programs = Program::all();
        $modules = Module::with(['program', 'teacher'])->get();
        $teachers = Teacher::all();

        return view('payment_requests.create', compact('teacher', 'programs', 'modules', 'teachers'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'module_id' => 'required|exists:modules,id',
            'request_type' => 'required|in:Modulo,Tutoria',
            'payroll_number' => 'nullable|string|max:255',
            'request_date' => 'required|date',
            'invoice_number' => 'nullable|string|max:255',
            'total_amount' => 'required|numeric|min:0',
            'observations' => 'nullable|string|max:1000',
            'status' => 'required|in:Pendiente,Aprobado,Rechazado,Realizado',
            // Campos específicos para Tutoría
            'tutoring_teacher_id' => 'nullable|exists:teachers,id',
            'tutoring_start_date' => 'nullable|required_if:request_type,Tutoria|date',
            'tutoring_end_date' => 'nullable|required_if:request_type,Tutoria|date|after_or_equal:tutoring_start_date',
            'tutoring_students_count' => 'nullable|integer|min:0',
        ]);

        // Verificar que el módulo existe y obtener información
        $module = Module::with(['program', 'teacher'])->findOrFail($validated['module_id']);

        // Calcular total de estudiantes activos para este programa (solo para tipo Módulo)
        $totalActiveStudents = 0;
        if ($validated['request_type'] === 'Modulo') {
            $totalActiveStudents = Inscription::where('program_id', $module->program_id)
                                             ->where('academic_status', 'Activo')
                                             ->count();
        }

        // Determinar el docente (el del módulo o el de tutoría si se especifica)
        $teacher = $module->teacher;
        if ($validated['request_type'] === 'Tutoria' && isset($validated['tutoring_teacher_id'])) {
            $teacher = Teacher::findOrFail($validated['tutoring_teacher_id']);
        }

        // Calcular retención según el tipo de docente (escalonada):
        // - Si es trabajador ESAM y factura: retención del 30%
        // - Si es trabajador ESAM y NO factura: retención del 30% + 16% del saldo (41.2% total)
        // - Si NO es trabajador ESAM y factura: sin retención (0%)
        // - Si NO es trabajador ESAM y NO factura: retención del 16%
        $totalAmount = $validated['total_amount'];
        $retentionAmount = 0;
        
        if ($teacher->esam_worker === 'Si' || $teacher->esam_worker === 'Sí') {
            // Primera retención: 30% por ser trabajador ESAM
            $retentionAmount = $totalAmount * 0.30;
            
            // Si además NO factura, aplicar 16% sobre el saldo (70%)
            if ($teacher->bill !== 'Si' && $teacher->bill !== 'Sí' && $teacher->bill !== 'si' && $teacher->bill !== 'sí') {
                $saldoDespuesEsam = $totalAmount - $retentionAmount; // 70% del total
                $retentionAmount += $saldoDespuesEsam * 0.16; // 16% del 70% = 11.2% adicional
                // Retención total: 30% + 11.2% = 41.2%
            }
        } elseif ($teacher->bill !== 'Si' && $teacher->bill !== 'Sí' && $teacher->bill !== 'si' && $teacher->bill !== 'sí') {
            // No es trabajador ESAM y no factura: retención del 16%
            $retentionAmount = $totalAmount * 0.16;
        }
        
        $netAmount = $totalAmount - $retentionAmount;

        // Crear solicitud
        $paymentRequest = PaymentRequest::create([
            'module_id' => $validated['module_id'],
            'request_type' => $validated['request_type'],
            'payroll_number' => $validated['payroll_number'] ?? null,
            'request_date' => $validated['request_date'],
            'invoice_number' => $validated['invoice_number'] ?? null,
            'total_amount' => $totalAmount,
            'retention_amount' => $retentionAmount,
            'net_amount' => $netAmount,
            'total_active_students' => $totalActiveStudents,
            'observations' => $validated['observations'] ?? null,
            'status' => $validated['status'] ?? 'Pendiente',
            // Campos de tutoría
            'tutoring_teacher_id' => $validated['request_type'] === 'Tutoria' ? ($validated['tutoring_teacher_id'] ?? null) : null,
            'tutoring_start_date' => $validated['request_type'] === 'Tutoria' ? ($validated['tutoring_start_date'] ?? null) : null,
            'tutoring_end_date' => $validated['request_type'] === 'Tutoria' ? ($validated['tutoring_end_date'] ?? null) : null,
            'tutoring_students_count' => $validated['request_type'] === 'Tutoria' ? ($validated['tutoring_students_count'] ?? null) : null,
            'created_by' => Auth::id(),
        ]);

        $tipoPago = $validated['request_type'] === 'Tutoria' ? 'tutoría' : 'módulo';
        return redirect()
            ->route('payment_requests.index')
            ->with('success', "Solicitud de pago por {$tipoPago} creada correctamente");
    }

    public function show($id)
    {
        $paymentRequest = PaymentRequest::with([
            'module.program',
            'module.teacher',
            'tutoringTeacher',
        ])->findOrFail($id);

        return view('payment_requests.show', compact('paymentRequest'));
    }

    public function edit($id)
    {
        $paymentRequest = PaymentRequest::with('module.program', 'module.teacher')
                                        ->findOrFail($id);

        $this->authorizeAccess($paymentRequest, 'edit');

        if ($paymentRequest->status !== 'Pendiente') {
            return back()->with('error', 'Solo se pueden editar solicitudes en estado pendiente');
        }

        return view('payment_requests.edit', compact('paymentRequest'));
    }

    public function update(Request $request, $id)
    {
        $paymentRequest = PaymentRequest::findOrFail($id);

        $this->authorizeAccess($paymentRequest, 'edit');

        // Verificar que esté en estado pendiente
        if ($paymentRequest->status !== 'Pendiente') {
            return back()->with('error', 'Solo se pueden editar solicitudes en estado pendiente');
        }
        
        $validated = $request->validate([
            'payroll_number' => 'nullable|string|max:255',
            'request_date' => 'required|date',
            'invoice_number' => 'nullable|string|max:255',
            'total_amount' => 'required|numeric|min:0',
            'observations' => 'nullable|string|max:1000',
            'status' => 'required|in:Pendiente,Aprobado,Rechazado,Realizado'
        ]);
        
        // Recalcular retención si se cambia el monto
        if (isset($validated['total_amount'])) {
            // Determinar el docente (el del módulo o el de tutoría si se especifica)
            $teacher = $paymentRequest->module->teacher;
            if ($paymentRequest->request_type === 'Tutoria' && $paymentRequest->tutoring_teacher_id) {
                $teacher = Teacher::findOrFail($paymentRequest->tutoring_teacher_id);
            }

            $totalAmount = $validated['total_amount'];
            $retentionAmount = 0;
            
            // Calcular retención según el tipo de docente (escalonada):
            // - Si es trabajador ESAM y factura: retención del 30%
            // - Si es trabajador ESAM y NO factura: retención del 30% + 16% del saldo (41.2% total)
            // - Si NO es trabajador ESAM y factura: sin retención (0%)
            // - Si NO es trabajador ESAM y NO factura: retención del 16%
            if ($teacher->esam_worker === 'Si' || $teacher->esam_worker === 'Sí') {
                // Primera retención: 30% por ser trabajador ESAM
                $retentionAmount = $totalAmount * 0.30;
                
                // Si además NO factura, aplicar 16% sobre el saldo (70%)
                if ($teacher->bill !== 'Si' && $teacher->bill !== 'Sí' && $teacher->bill !== 'si' && $teacher->bill !== 'sí') {
                    $saldoDespuesEsam = $totalAmount - $retentionAmount; // 70% del total
                    $retentionAmount += $saldoDespuesEsam * 0.16; // 16% del 70% = 11.2% adicional
                    // Retención total: 30% + 11.2% = 41.2%
                }
            } elseif ($teacher->bill !== 'Si' && $teacher->bill !== 'Sí' && $teacher->bill !== 'si' && $teacher->bill !== 'sí') {
                // No es trabajador ESAM y no factura: retención del 16%
                $retentionAmount = $totalAmount * 0.16;
            }
            
            $validated['retention_amount'] = $retentionAmount;
            $validated['net_amount'] = $totalAmount - $retentionAmount;
        }
        
        $paymentRequest->update($validated);
        
        return redirect()
            ->route('payment_requests.show', $paymentRequest->id)
            ->with('success', 'Solicitud actualizada exitosamente');
    }

    public function cambiarEstado(Request $request, $id)
    {
        $paymentRequest = PaymentRequest::findOrFail($id);
        
        $validated = $request->validate([
            'status' => 'required|in:Pendiente,Aprobado,Rechazado,Realizado',
            'observations' => 'nullable|string|max:500',
            'new_request_date' => 'nullable|required_if:status,Rechazado|date|after:today'
        ], [
            'new_request_date.required_if' => 'Debe especificar una fecha para la nueva solicitud',
            'new_request_date.after' => 'La nueva fecha debe ser posterior a hoy'
        ]);
        
        // Log para depuración
        Log::info('Cambiando estado de solicitud', [
            'id' => $id,
            'status_anterior' => $paymentRequest->status,
            'status_nuevo' => $validated['status'],
            'datos_validados' => $validated
        ]);
        
        // Actualizar directamente los campos
        $paymentRequest->status = $validated['status'];
        if (isset($validated['observations']) && !empty($validated['observations'])) {
            $paymentRequest->observations = $validated['observations'];
        }
        $paymentRequest->save();
        
        // Verificar que se guardó
        $paymentRequest->refresh();
        Log::info('Estado después de guardar', [
            'status_guardado' => $paymentRequest->status
        ]);
        
        // Si la solicitud es rechazada, crear una nueva solicitud con la fecha especificada
        if ($validated['status'] === 'Rechazado' && isset($validated['new_request_date'])) {
            // Calcular retención y monto neto para la nueva solicitud
            $teacher = $paymentRequest->request_type === 'Tutoria' && $paymentRequest->tutoringTeacher 
                ? $paymentRequest->tutoringTeacher 
                : $paymentRequest->module->teacher;
            
            $retentionAmount = $paymentRequest->calculateRetention(
                $paymentRequest->total_amount,
                $teacher->bill ?? 'No',
                $teacher->esam_worker ?? 'No'
            );
            $netAmount = $paymentRequest->calculateNetAmount($paymentRequest->total_amount, $retentionAmount);
            
            PaymentRequest::create([
                'module_id' => $paymentRequest->module_id,
                'request_type' => $paymentRequest->request_type,
                'payroll_number' => $paymentRequest->payroll_number,
                'request_date' => $validated['new_request_date'],
                'invoice_number' => $paymentRequest->invoice_number,
                'total_amount' => $paymentRequest->total_amount,
                'retention_amount' => $retentionAmount,
                'net_amount' => $netAmount,
                'total_active_students' => $paymentRequest->total_active_students,
                // Campos de tutoría
                'tutoring_teacher_id' => $paymentRequest->tutoring_teacher_id,
                'tutoring_start_date' => $paymentRequest->tutoring_start_date,
                'tutoring_end_date' => $paymentRequest->tutoring_end_date,
                'tutoring_students_count' => $paymentRequest->tutoring_students_count,
                'observations' => 'Solicitud reprogramada del ' . $paymentRequest->request_date->format('d/m/Y'),
                'status' => 'Pendiente',
                'created_by' => Auth::id(),
            ]);
            
            $newDate = \Carbon\Carbon::parse($validated['new_request_date'])->format('d/m/Y');
            return back()->with('success', "Solicitud rechazada y reprogramada para el {$newDate}");
        }
        
        return back()->with('success', "Solicitud marcada como {$validated['status']}");
    }

    /**
     * Eliminar solicitud
     */
    public function destroy($id)
    {
        $paymentRequest = PaymentRequest::findOrFail($id);

        $this->authorizeAccess($paymentRequest, 'delete');

        // Solo se puede eliminar si está pendiente
        if ($paymentRequest->status !== 'Pendiente') {
            return back()->with('error', 'Solo se pueden eliminar solicitudes en estado pendiente');
        }
        
        $paymentRequest->delete();
        
        return redirect()
            ->route('solicitudes.index')
            ->with('success', 'Solicitud eliminada exitosamente');
    }

    /**
     * Generar reporte de solicitudes
     */
    public function reporte(Request $request)
    {
        $query = PaymentRequest::with(['module.program', 'module.teacher']);
        
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }
        
        if ($request->filled('fecha_desde')) {
            $query->whereDate('created_at', '>=', $request->fecha_desde);
        }
        
        if ($request->filled('fecha_hasta')) {
            $query->whereDate('created_at', '<=', $request->fecha_hasta);
        }

        $paymentRequests = $query->orderBy('created_at', 'desc')->get();

        $totalMonto = $paymentRequests->sum('monto');

        return view('payment_requests.reporte', compact('paymentRequests', 'totalMonto'));
    }

    /**
     * Obtener módulos de un teacher (para AJAX)
     */
    public function modulosPorTeacher($teacherId)
    {
        $modulos = Module::where('teacher_id', $teacherId)
                         ->with('program')
                         ->orderBy('start_date', 'desc')
                         ->get();
        
        return response()->json($modulos);
    }

    /**
     * Obtener información completa de un módulo para la solicitud de pago (para AJAX)
     */
    public function getModuleInfo($moduleId)
    {
        $module = Module::with(['program', 'teacher'])->findOrFail($moduleId);
        
        // Calcular total de estudiantes activos para este programa
        $totalActiveStudents = Inscription::where('program_id', $module->program_id)
                                         ->where('academic_status', 'Activo')
                                         ->count();

        return response()->json([
            // Datos del programa
            'program' => [
                'accounting_code' => $module->program->accounting_code ?? 'N/A',
                'name' => $module->program->name ?? 'N/A',
                'area' => $module->program->area ?? 'N/A',
            ],
            // Datos del módulo
            'module' => [
                'name' => $module->name,
                'start_date' => $module->start_date ? $module->start_date->format('d/m/Y') : 'N/A',
                'finalization_date' => $module->finalization_date ? $module->finalization_date->format('d/m/Y') : 'N/A',
            ],
            // Datos del docente
            'teacher' => [
                'full_name' => $module->teacher->full_name ?? 'N/A',
                'ci' => $module->teacher->ci ?? 'N/A',
                'bill' => $module->teacher->bill ?? 'No',
                'bank' => $module->teacher->bank ?? 'N/A',
                'account_number' => $module->teacher->account_number ?? 'N/A',
            ],
            // Total de estudiantes activos
            'total_active_students' => $totalActiveStudents,
        ]);
    }

    /**
     * Exportar solicitudes de pago a Excel
     */
    public function export(Request $request)
    {
        $fileName = 'Solicitudes_Pago_' . now()->format('d_m_Y_His') . '.xlsx';
        
        return Excel::download(
            new PaymentRequestsExport(
                year: $request->input('year'),
                month: $request->input('month'),
                status: $request->input('status'),
                requestType: $request->input('request_type'),
                payrollNumber: $request->input('payroll_number'),
                teacher: $request->input('teacher'),
                program: $request->input('program'),
                module: $request->input('module')
            ),
            $fileName
        );
    }

    public function import(Request $request)
    {
        $request->validate([
            'excel_file' => 'required|file|mimes:xlsx,xls',
        ]);

        $file = $request->file('excel_file');

        try {
            $spreadsheet = IOFactory::load($file->getRealPath());
            $sheet = $spreadsheet->getActiveSheet();
            $rows = $sheet->toArray(null, true, true, false);
        } catch (\Exception $e) {
            return back()->with('error', 'No se pudo leer el archivo Excel: ' . $e->getMessage());
        }

        if (empty($rows)) {
            return back()->with('error', 'El archivo está vacío.');
        }

        // Normalizar cabeceras
        $headerRow = array_map(fn($v) => strtoupper(trim((string) ($v ?? ''))), $rows[0]);
        unset($rows[0]);

        Log::info('Import PaymentRequest - Headers detectados', ['headers' => $headerRow]);

        // Detectar índices de columnas
        $colMap = [];
        $colNames = [
            'sede'           => ['SEDE'],
            'mes'            => ['MES'],
            'payroll'        => ['N° PLANILLA', 'N PLANILLA', 'PLANILLA'],
            'fecha'          => ['FECHA SOLICITUD', 'FECHA'],
            'accounting'     => ['CODIGO CONTABLE PROGRAMA', 'CÓDIGO CONTABLE PROGRAMA', 'CODIGO CONTABLE', 'CÓDIGO CONTABLE'],
            'programa'       => ['PROGRAMA'],
            'modulo'         => ['MÓDULO', 'MODULO'],
            'fecha_inicio'   => ['FECHA DE INICIO MÓDULO', 'FECHA DE INICIO MODULO', 'FECHA INICIO'],
            'fecha_fin'      => ['FECHA DE FIN MÓDULO', 'FECHA DE FIN MODULO', 'FECHA FIN'],
            'estudiantes'    => ['CANTIDAD DE ESTUDIANTE', 'CANTIDAD DE ESTUDIANTES', 'CANTIDAD ESTUDIANTES'],
            'docente'        => ['APELLIDOS Y NOMBRES', 'APELLIDOS Y NOMBRE'],
            'ci'             => ['CARNET DE IDENTIDAD', 'CI'],
            'factura_sn'     => ['EMITE FACTURA S/NO', 'EMITE FACTURA'],
            'nro_factura'    => ['N° FACTURA', 'N FACTURA', 'NRO FACTURA'],
            'total'          => ['IMPTE.TOTAL', 'IMPORTE TOTAL', 'TOTAL'],
            'liquido'        => ['LIQUIDO PAGABLE DOCENTE (84,5%)', 'LIQUIDO PAGABLE DOCENTE', 'LÍQUIDO PAGABLE DOCENTE', 'LIQUIDO'],
            'observaciones'  => ['OBSERVACIONES'],
        ];

        foreach ($colNames as $key => $candidates) {
            foreach ($headerRow as $idx => $name) {
                if (in_array($name, $candidates)) {
                    $colMap[$key] = $idx;
                    break;
                }
            }
        }

        Log::info('Import PaymentRequest - ColMap detectado', ['colMap' => $colMap]);

        if (!isset($colMap['accounting']) || !isset($colMap['modulo'])) {
            return back()->with('error', 'No se encontraron las columnas requeridas (CODIGO CONTABLE PROGRAMA, MÓDULO). Verifica el formato del Excel. Headers encontrados: ' . implode(', ', array_filter($headerRow)));
        }

        $created   = 0;
        $skipped   = [];
        $notFound  = [];

        foreach ($rows as $rowIndex => $row) {
            $accountingCode = trim((string) ($row[$colMap['accounting']] ?? ''));
            // Normalizar: quitar saltos de línea internos que Excel puede incluir en celdas
            $moduleName     = trim(preg_replace('/[\r\n]+/', ' ', (string) ($row[$colMap['modulo']] ?? '')));

            if ($accountingCode === '' || $moduleName === '') {
                continue;
            }

            // Buscar programa por código contable
            Log::info("Import fila {$rowIndex}", ['accounting' => $accountingCode, 'modulo' => $moduleName]);
            $program = Program::where('accounting_code', $accountingCode)->first();
            if (!$program) {
                $notFound[] = "Fila " . ($rowIndex + 1) . ": código contable '{$accountingCode}' no encontrado";
                continue;
            }

            // El Excel trae "MÓDULO N: NOMBRE" o "TUTORÍA MÓDULO N: NOMBRE - Participantes: ..."
            // La DB guarda solo el nombre sin prefijo. Extraer la parte después de "MÓDULO N: "
            $moduleSearch = $moduleName;
            if (preg_match('/^(?:TUTORÍA\s+)?MÓDULO\s+\d+[:\.\-]\s*(.+?)(?:\s*-\s*Participantes?:.*)?$/iu', $moduleName, $matches)) {
                $moduleSearch = trim($matches[1]);
            }

            Log::info("Import fila {$rowIndex} moduleSearch", ['original' => $moduleName, 'search' => $moduleSearch, 'program_id' => $program->id]);

            // Buscar módulo por nombre (case-insensitive) dentro del programa
            $module = Module::where('program_id', $program->id)
                ->whereRaw('LOWER(name) = ?', [mb_strtolower($moduleSearch)])
                ->with('teacher')
                ->first();

            // Si no encuentra exacto, buscar con LIKE case-insensitive
            if (!$module) {
                $module = Module::where('program_id', $program->id)
                    ->whereRaw('LOWER(name) LIKE ?', ['%' . mb_strtolower($moduleSearch) . '%'])
                    ->with('teacher')
                    ->first();
            }

            // Fallback: buscar por palabras significativas (omite artículos/preposiciones y no-alfa)
            if (!$module) {
                $stopWords = ['de', 'del', 'la', 'el', 'en', 'y', 'a', 'e', 'los', 'las', 'un', 'una', 'iso', 'nts'];
                $rawWords  = explode(' ', mb_strtolower($moduleSearch));
                $sigWords  = array_values(array_filter(array_map(
                    fn($w) => preg_replace('/[^a-záéíóúüñ]/u', '', $w),
                    $rawWords
                ), fn($w) => mb_strlen($w) > 3 && !in_array($w, $stopWords)));

                // Primeras 2 palabras significativas
                if (count($sigWords) >= 2) {
                    $module = Module::where('program_id', $program->id)
                        ->whereRaw('LOWER(name) LIKE ?', ['%' . $sigWords[0] . '%'])
                        ->whereRaw('LOWER(name) LIKE ?', ['%' . $sigWords[1] . '%'])
                        ->with('teacher')
                        ->first();
                }

                // Primera + última palabra significativa (cubre casos con orden de palabras diferente)
                if (!$module && count($sigWords) >= 2) {
                    $last = end($sigWords);
                    if ($last !== $sigWords[0]) {
                        $module = Module::where('program_id', $program->id)
                            ->whereRaw('LOWER(name) LIKE ?', ['%' . $sigWords[0] . '%'])
                            ->whereRaw('LOWER(name) LIKE ?', ['%' . $last . '%'])
                            ->with('teacher')
                            ->first();
                    }
                }

                // Última opción: solo primera palabra significativa larga (>= 6 chars)
                if (!$module && count($sigWords) >= 1 && mb_strlen($sigWords[0]) >= 6) {
                    $module = Module::where('program_id', $program->id)
                        ->whereRaw('LOWER(name) LIKE ?', ['%' . $sigWords[0] . '%'])
                        ->with('teacher')
                        ->first();
                }
            }

            if (!$module) {
                $notFound[] = "Fila " . ($rowIndex + 1) . ": módulo '{$moduleName}' no encontrado en programa '{$program->name}'";
                continue;
            }

            // Calcular montos
            $rawTotal = $row[$colMap['total']] ?? 0;
            $totalAmount = (float) preg_replace('/[^\d.]/', '', (string) $rawTotal);
            Log::info("Import fila {$rowIndex} monto", ['raw' => $rawTotal, 'parsed' => $totalAmount, 'module' => $module->name]);
            if ($totalAmount <= 0) {
                $skipped[] = "Fila " . ($rowIndex + 1) . ": monto total inválido ('{$moduleName}')";
                continue;
            }

            // Calcular retención desde los datos del docente en DB
            $teacher = $module->teacher;
            $retentionAmount = 0;
            if ($teacher) {
                $bills      = in_array($teacher->bill, ['Si', 'Sí', 'si', 'sí']);
                $esamWorker = in_array($teacher->esam_worker, ['Si', 'Sí', 'si', 'sí']);

                if ($esamWorker) {
                    $retentionAmount = $totalAmount * 0.30;
                    if (!$bills) {
                        $retentionAmount += ($totalAmount - $retentionAmount) * 0.16;
                    }
                } elseif (!$bills) {
                    $retentionAmount = $totalAmount * 0.16;
                }
            }
            $netAmount = $totalAmount - $retentionAmount;

            $sede = trim((string) ($row[$colMap['sede']] ?? ''));
            if ($sede === '') $sede = 'ESAM LATAM ALAS';

            $observations = isset($colMap['observaciones']) ? (trim((string) ($row[$colMap['observaciones']] ?? '')) ?: null) : null;

            // Fecha solicitud — PhpSpreadsheet devuelve fechas como número serial de Excel
            $fechaRaw = isset($colMap['fecha']) ? ($row[$colMap['fecha']] ?? null) : null;
            $requestDate = now()->format('Y-m-d');
            if ($fechaRaw !== null && $fechaRaw !== '') {
                try {
                    if (is_numeric($fechaRaw)) {
                        // Número serial de Excel (ej: 46041 = 20/01/2026)
                        $requestDate = \PhpOffice\PhpSpreadsheet\Shared\Date::excelToDateTimeObject((float) $fechaRaw)->format('Y-m-d');
                    } else {
                        // String con formato DD/MM/YYYY
                        $requestDate = \Carbon\Carbon::createFromFormat('d/m/Y', trim((string) $fechaRaw))->format('Y-m-d');
                    }
                } catch (\Exception $e) {
                    $requestDate = now()->format('Y-m-d');
                }
            }

            $estudiantes = isset($colMap['estudiantes']) ? (int) ($row[$colMap['estudiantes']] ?? 0) : 0;
            $payroll     = isset($colMap['payroll'])     ? trim((string) ($row[$colMap['payroll']] ?? ''))     : null;
            $nroFactura  = isset($colMap['nro_factura']) ? trim((string) ($row[$colMap['nro_factura']] ?? '')) : null;

            PaymentRequest::create([
                'module_id'             => $module->id,
                'sede'                  => $sede,
                'request_type'          => stripos($moduleName, 'TUTORÍA') !== false ? 'Tutoria' : 'Modulo',
                'payroll_number'        => $payroll ?: null,
                'request_date'          => $requestDate,
                'invoice_number'        => $nroFactura ?: null,
                'total_amount'          => $totalAmount,
                'retention_amount'      => $retentionAmount,
                'net_amount'            => $netAmount,
                'total_active_students' => $estudiantes ?: 0,
                'observations'          => $observations,
                'status'                => 'Realizado',
                'created_by'            => Auth::id(),
            ]);

            $created++;
        }

        Log::info('Import PaymentRequest - Resultado', ['created' => $created, 'notFound_count' => count($notFound), 'skipped_count' => count($skipped)]);
        foreach ($notFound as $nf) Log::info('Import notFound: ' . $nf);
        foreach ($skipped as $sk) Log::info('Import skipped: ' . $sk);

        $message = "Se importaron {$created} solicitudes correctamente.";
        if (!empty($notFound)) {
            $message .= ' No encontrados: ' . implode('; ', $notFound) . '.';
        }
        if (!empty($skipped)) {
            $message .= ' Omitidos: ' . implode('; ', $skipped) . '.';
        }

        $type = (!empty($notFound) || !empty($skipped)) ? 'warning' : 'success';
        return back()->with($type, $message);
    }

    private function authorizeAccess(PaymentRequest $paymentRequest, string $ability): void
    {
        $user = Auth::user();
        if (!$user instanceof \App\Models\User) abort(403);
        $isOwner = (int) $paymentRequest->created_by === (int) $user->id;
        if ($ability === 'edit'   && $user->can('payment_request.edit'))               return;
        if ($ability === 'edit'   && $user->can('payment_request.edit_own')   && $isOwner) return;
        if ($ability === 'delete' && $user->can('payment_request.delete'))             return;
        if ($ability === 'delete' && $user->can('payment_request.delete_own') && $isOwner) return;
        abort(403, 'Solo puedes ' . ($ability === 'edit' ? 'editar' : 'eliminar') . ' solicitudes de pago que tú creaste.');
    }
}

<?php

namespace App\Http\Controllers;

use App\Models\Program;
use App\Models\Teacher;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Schema;

class ProgramController extends Controller
{
    public function __construct()
    {
        $this->middleware(['permission:program.view'])->only(['index', 'show']);
        $this->middleware(['permission:program.create'])->only(['create', 'store']);
        $this->middleware(['permission:program.edit'])->only(['edit', 'update']);
        $this->middleware(['permission:program.delete'])->only(['destroy']);
        // Middleware específico para cambio de estado - solo admin y academico
        $this->middleware(['role:admin|academico'])->only(['updateState']);
    }

    public function index(Request $request)
    {
        $query = Program::withCount('inscriptions');
        
        // Aplicar filtro de búsqueda si existe
        if ($request->has('search') && $request->search != '') {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('code', 'like', "%{$search}%")
                  ->orWhere('accounting_code', 'like', "%{$search}%");
            });
        }
        
        // Filtro por área
        if ($request->has('area_filter') && $request->area_filter != '') {
            $query->where('area', $request->area_filter);
        }
        
        // Filtro por estado
        if ($request->has('status_filter') && $request->status_filter != '') {
            $query->where('status', 'like', "%{$request->status_filter}%");
        }
        
        // Filtro por gestión (año) - por defecto el año actual si no se especifica
        $currentYear = date('Y');
        $yearFilter = $request->has('year_filter') ? $request->year_filter : $currentYear;
        
        // Si year_filter no está vacío, aplicar el filtro
        if ($yearFilter != '') {
            $query->where('year', 'like', "%{$yearFilter}%");
        }
        
        $programs = $query->orderBy('start_date', 'desc')->paginate(10);
        
        return view('programs.index', compact('programs', 'yearFilter'));
    }

    public function create()
    {
        // Los programas ya no se crean manualmente - se sincronizan desde la BD externa
        return redirect()->route('programs.index')
            ->with('info', 'Los programas se sincronizan automáticamente desde la base de datos externa. No es posible crear programas manualmente.');
    }

    public function store(Request $request)
    {
        // Los programas ya no se crean manualmente - se sincronizan desde la BD externa
        return redirect()->route('programs.index')
            ->with('info', 'Los programas se sincronizan automáticamente desde la base de datos externa. No es posible crear programas manualmente.');
    }

    public function show(Program $program)
    {
        $program->load(['modules' => function($query) {
            $query->with(['monitor', 'teacher'])
                ->orderBy('start_date', 'asc');
        }]);
        
        return view('programs.show', compact('program'));
    }

    public function edit(Program $program)
    {
        // Los programas se sincronizan desde la BD externa - solo se pueden editar academic_code y area
        return view('programs.edit', compact('program'));
    }

    public function update(Request $request, Program $program)
    {
        // Solo permitir editar los campos locales: academic_code y area
        $validated = $request->validate([
            'academic_code' => 'nullable|string|max:50',
            'area' => 'nullable|string|max:255',
        ]);

        $program->update($validated);

        return redirect()->route('programs.show', $program)
            ->with('success', 'Campos locales del programa actualizados correctamente.');
    }

    /**
     * Update the state of the specified resource.
     * @deprecated Los estados se sincronizan desde la BD externa
     */
    public function updateState(Request $request, Program $program)
    {
        return redirect()->route('programs.index')
            ->with('info', 'El estado del programa se sincroniza automáticamente desde la base de datos externa.');
    }

    public function destroy(Program $program)
    {
        // Los programas se sincronizan desde la BD externa - no se pueden eliminar manualmente
        return redirect()->route('programs.index')
            ->with('info', 'Los programas se sincronizan automáticamente desde la base de datos externa. No es posible eliminarlos manualmente.');
    }

    public function inscriptions(Request $request, Program $program)
    {
        $search = trim((string) $request->input('search', ''));
        $academicStatusColumn = Schema::hasColumn('inscriptions', 'estado_academico')
            ? 'estado_academico'
            : 'external_academic_status';

        $inscriptionsQuery = $program->inscriptions()
            ->with('documentFollowups', 'documents');

        if ($search !== '') {
            $inscriptionsQuery->where(function ($q) use ($search, $academicStatusColumn) {
                $q->where('full_name', 'like', "%{$search}%")
                    ->orWhere('ci', 'like', "%{$search}%")
                    ->orWhere('external_inscription_status', 'like', "%{$search}%")
                    ->orWhere($academicStatusColumn, 'like', "%{$search}%");
            });
        }

        // Estadisticas sobre todo el resultado filtrado
        $inscriptionsStats = (clone $inscriptionsQuery)->get();

        // Tabla paginada
        $inscriptions = $inscriptionsQuery
            ->orderBy('full_name')
            ->paginate(15)
            ->withQueryString();

        return view('programs.inscriptions', compact('program', 'inscriptions', 'inscriptionsStats', 'search'));
    }
}
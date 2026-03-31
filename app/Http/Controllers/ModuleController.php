<?php

namespace App\Http\Controllers;

use App\Models\Module;
use App\Models\Program;
use App\Models\User;
use App\Models\Teacher;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;

class ModuleController extends Controller
{
    public function __construct()
    {
        $this->middleware(['permission:program.view'])->only(['index', 'show']);
        $this->middleware(['permission:program.create'])->only(['create', 'store']);
        $this->middleware(['permission:program.edit'])->only(['edit', 'update', 'updateStatus']);
        $this->middleware(['permission:program.delete'])->only(['destroy']);
        $this->middleware(['role:admin|academico'])->only(['updateStatus']);
    }

    /**
     * Display a listing of the resource.
     */
    public function index(Request $request, Program $program)
    {
        $modules = $program->modules()
            ->with('monitor', 'teacher')
            ->orderBy('start_date', 'asc')
            ->orderBy('name')
            ->paginate(10);
            
        return view('modules.index', compact('program', 'modules'));
    }

    /**
     * Show the form for creating a new resource.
     * NOTA: Los módulos se sincronizan automáticamente desde la BD externa.
     */
    public function create(Program $program)
    {
        return redirect()->route('programs.show', $program)
            ->with('warning', 'Los módulos se sincronizan automáticamente desde la base de datos externa. No se pueden crear manualmente.');
    }

    /**
     * Store a newly created resource in storage.
     * NOTA: Los módulos se sincronizan automáticamente desde la BD externa.
     * Este método está deshabilitado. Usar solo la sincronización.
     */
    public function store(Request $request, Program $program)
    {
        return redirect()->route('programs.show', $program)
            ->with('warning', 'Los módulos se sincronizan automáticamente desde la base de datos externa. No se pueden crear manualmente.');
    }

    /**
     * Display the specified resource.
     */
    public function show(Program $program, Module $module)
    {
        $module->load(['monitor', 'teacher', 'classes' => function($query) {
            $query->orderBy('class_date')->orderBy('start_time');
        }]);
        
        return view('modules.show', compact('program', 'module'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Program $program, Module $module)
    {
        $monitorRoles = ['admin', 'marketing', 'academic', 'academico', 'operator'];

        $monitors = User::whereHas('roles', function ($query) use ($monitorRoles) {
                $query->whereIn('name', $monitorRoles);
            })
            ->where('active', true)
            ->orderBy('name')
            ->pluck('name', 'id');

        $teachers = Teacher::orderBy('name')
            ->get()
            ->pluck('id');

        return view('modules.edit_simple', compact('program', 'module', 'monitors', 'teachers'));
    }

    /**
     * Update the specified resource in storage.
     * Solo permite editar campos editables localmente
     */
    public function update(Request $request, Program $program, Module $module)
    {
        // Solo validar y actualizar campos editables localmente
        $validated = $request->validate([
            'teacher_id' => 'nullable|exists:teachers,id',
            'monitor_id' => 'nullable|exists:users,id',
            'recovery_start_date' => 'nullable|date',
            'recovery_end_date' => 'nullable|date|after_or_equal:recovery_start_date',
            'recovery_notes' => 'nullable|string',
            'teacher_rating' => 'nullable|integer|min:1|max:5',
        ]);

        $module->update($validated);

        return redirect()->route('programs.modules.show', ['program' => $program->id, 'module' => $module->id])
            ->with('success', 'Módulo actualizado correctamente.');
    }

    /**
     * Update the status of the specified resource.
     */
    public function updateStatus(Request $request, Module $module)
    {
        $validated = $request->validate([
            'status' => 'required|in:Pendiente,Desarrollo,Finalizado',
        ]);

        $oldStatus = $module->status;
        $module->update($validated);

        return redirect()->back()
            ->with('success', "Estado del módulo '{$module->name}' cambiado de '{$oldStatus}' a '{$module->status}' correctamente.");
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Program $program, Module $module)
    {
        $moduleName = $module->name;
        $module->delete();

        return redirect()->back()
            ->with('success', "Módulo '{$moduleName}' eliminado correctamente.");
    }

    /**
     * Cambiar el orden de un módulo.
     */
    public function reorder(Request $request, Program $program, Module $module)
    {
        $direction = $request->input('direction');
        $modules = $program->modules()->orderBy('order')->get();
        
        $currentIndex = $modules->search(function($m) use ($module) {
            return $m->id === $module->id;
        });

        if ($direction === 'up' && $currentIndex > 0) {
            $previousModule = $modules[$currentIndex - 1];
            $tempOrder = $module->order;
            $module->update(['order' => $previousModule->order]);
            $previousModule->update(['order' => $tempOrder]);
        } elseif ($direction === 'down' && $currentIndex < count($modules) - 1) {
            $nextModule = $modules[$currentIndex + 1];
            $tempOrder = $module->order;
            $module->update(['order' => $nextModule->order]);
            $nextModule->update(['order' => $tempOrder]);
        }

        return redirect()->back()
            ->with('success', 'Orden del módulo actualizado correctamente.');
    }

    /**
     * Eliminar un módulo del programa (alias para destroy).
     */
    public function destroyForProgram(Program $program, Module $module)
    {
        return $this->destroy($program, $module);
    }
}

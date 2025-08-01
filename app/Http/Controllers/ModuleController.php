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
        $this->middleware(['permission:program.edit'])->only(['edit', 'update']);
        $this->middleware(['permission:program.delete'])->only(['destroy']);
    }

    /**
     * Display a listing of the resource.
     */
    public function index(Request $request, Program $program)
    {
        $modules = $program->modules()
            ->with('monitor')
            ->orderBy('name')
            ->paginate(10);
            
        return view('modules.index', compact('program', 'modules'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create(Program $program)
    {
        $monitors = User::role(['admin', 'operator', 'marketing'])
            ->where('active', true)
            ->orderBy('name')
            ->pluck('name', 'id');

        $teachers = Teacher::orderBy('paternal_surname')
            ->orderBy('name')
            ->get()
            ->pluck('full_name', 'id');

        return view('modules.create', compact('program', 'monitors', 'teachers'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request, Program $program)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'teacher_id' => 'required|exists:teachers,id',
            'monitor_id' => 'required|exists:users,id',
            'class_count' => 'required|integer|min:1',
            'active' => 'boolean',
        ]);

        $validated['program_id'] = $program->id;

        $module = $program->modules()->create($validated);

        return redirect()->route('programs.modules.show', [$program->id, $module->id])
            ->with('success', 'Módulo creado correctamente.');
    }

    /**
     * Display the specified resource.
     */
    public function show(Program $program, Module $module)
    {
        $module->load(['monitor', 'classes' => function($query) {
            $query->orderBy('class_date')->orderBy('start_time');
        }]);
        
        return view('modules.show', compact('program', 'module'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Program $program, Module $module)
    {
        $monitors = User::role(['admin', 'operator', 'marketing'])
            ->where('active', true)
            ->orderBy('name')
            ->pluck('name', 'id');

        $teachers = Teacher::orderBy('paternal_surname')
            ->orderBy('name')
            ->get()
            ->pluck('full_name', 'id');

        return view('modules.edit', compact('program', 'module', 'monitors', 'teachers'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Program $program, Module $module)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'teacher_id' => 'required|exists:teachers,id',
            'monitor_id' => 'required|exists:users,id',
            'class_count' => 'required|integer|min:1',
            'active' => 'boolean',
        ]);
        $validated['program_id'] = $program->id;

        $module->update($validated);

        return redirect()->route('programs.modules.show', ['program' => $program->id, 'module' => $module->id])
            ->with('success', 'Módulo actualizado correctamente.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Program $program, Module $module)
    {
        $module->delete();

        return redirect()->route('programs.show',['program' => $program->id])
            ->with('success', 'Módulo eliminado correctamente.');
    }
}

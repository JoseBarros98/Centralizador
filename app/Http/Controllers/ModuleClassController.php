<?php

namespace App\Http\Controllers;

use App\Models\Module;
use App\Models\ModuleClass;
use App\Models\Program;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;

class ModuleClassController extends Controller
{
    public function __construct()
    {
        $this->middleware(['permission:program.view'])->only(['index', 'show']);
        $this->middleware(['permission:program.create'])->only(['create', 'store']);
        $this->middleware(['permission:program.edit'])->only(['edit', 'update']);
        $this->middleware(['permission:program.delete'])->only(['destroy']);
    }

    /**
     * Display the specified resource.
     */
    public function show(Program $program, Module $module, ModuleClass $class)
    {
        return view('module_classes.show', compact('program', 'module', 'class'));
    }
    /**
     * Show the form for creating a new resource.
     */
    public function create(Program $program, Module $module)
    {
        return view('module_classes.create', compact('program', 'module'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request, Program $program, Module $module)
    {
        $validated = $request->validate([
            'class_date' => 'required|date',
            'start_time' => 'required|date_format:H:i',
            'end_time' => 'required|date_format:H:i|after:start_time',
            'class_link' => 'nullable|url',
        ]);

        $module->classes()->create($validated);

        return redirect()->route('programs.modules.show', [$program->id, $module->id])
            ->with('success', 'Clase añadida correctamente.');
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Program $program, Module $module, ModuleClass $class)
    {
        return view('module_classes.edit', compact('program', 'module', 'class'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Program $program, Module $module, ModuleClass $class)
    {
        $validated = $request->validate([
            'class_date' => 'required|date',
            'start_time' => 'required|date_format:H:i',
            'end_time' => 'required|date_format:H:i|after:start_time',
            'class_link' => 'nullable|url',
        ]);

        $class->update($validated);

        return redirect()->route('programs.modules.show', [$program->id, $module->id])
            ->with('success', 'Clase actualizada correctamente.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Program $program, Module $module, ModuleClass $class)
    {
        $class->delete();

        return redirect()->route('programs.modules.show', [$program->id, $module->id])
            ->with('success', 'Clase eliminada correctamente.');
    }
}

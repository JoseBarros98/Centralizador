<?php

namespace App\Http\Controllers;

use App\Models\Module;
use App\Models\Program;
use Illuminate\Http\Request;

class ModuleRecoveryController extends Controller
{
    /**
     * Mostrar el formulario para editar la información de recuperatorio del módulo.
     */
    public function edit(Program $program, Module $module)
    {
        return view('modules.recovery', compact('program', 'module'));
    }

    /**
     * Actualizar la información de recuperatorio del módulo.
     */
    public function update(Request $request, Program $program, Module $module)
    {
        $validated = $request->validate([
            'recovery_start_date' => 'nullable|date',
            'recovery_end_date' => 'nullable|date|after_or_equal:recovery_start_date',
            'recovery_notes' => 'nullable|string|max:1000',
        ]);

        $module->update($validated);

        return redirect()->route('programs.modules.show', ['program' => $program->id, 'module' => $module->id])
            ->with('success', 'Información de recuperatorio actualizada correctamente.');
    }
}

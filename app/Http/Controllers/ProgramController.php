<?php

namespace App\Http\Controllers;

use App\Models\Program;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;

class ProgramController extends Controller
{
    public function __construct()
    {
        $this->middleware(['permission:program.view'])->only(['index', 'show']);
        $this->middleware(['permission:program.create'])->only(['create', 'store']);
        $this->middleware(['permission:program.edit'])->only(['edit', 'update']);
        $this->middleware(['permission:program.delete'])->only(['destroy', 'toggleActive']);
    }

    public function index(Request $request)
    {
        $query = Program::query();
        
        // Aplicar filtro de búsqueda si existe
        if ($request->has('search') && $request->search != '') {
            $search = $request->search;
            $query->where('name', 'like', "%{$search}%")
                  ->orWhere('description', 'like', "%{$search}%");
        }
        
        $programs = $query->orderBy('name')->paginate(15);
        return view('programs.index', compact('programs'));
    }

    public function create()
    {
        return view('programs.create');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255|unique:programs',
            'description' => 'nullable|string',
            'active' => 'boolean',
        ]);

        Program::create($validated);

        return redirect()->route('programs.index')
            ->with('success', 'Programa creado correctamente.');
    }

    public function show(Program $program)
    {
        $program->load(['modules' => function($query) {
            $query->with('monitor')->orderBy('name');
        }]);
        
        return view('programs.show', compact('program'));
    }

    public function inscriptions(Program $program)
    {
        $inscriptions = $program->inscriptions()->with(['documentFollowups'])->paginate(15);
        return view('programs.inscriptions', compact('program', 'inscriptions'));
    }

    public function edit(Program $program)
    {
        return view('programs.edit', compact('program'));
    }

    public function update(Request $request, Program $program)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255|unique:programs,name,' . $program->id,
            'description' => 'nullable|string',
            'active' => 'boolean',
        ]);

        $program->update($validated);

        return redirect()->route('programs.index')
            ->with('success', 'Programa actualizado correctamente.');
    }

    public function destroy(Program $program)
    {
        // Verificar si el programa tiene inscripciones asociadas y está intentando desactivarse
        if ($program->active && $program->inscriptions()->count() > 0) {
            return redirect()->route('programs.index')
                ->with('error', 'No se puede desactivar el programa porque tiene inscripciones asociadas.');
        }

        // Invertir el estado actual
        $program->active = !$program->active;
        $program->save();

        $message = $program->active 
            ? 'Programa activado correctamente.' 
            : 'Programa desactivado correctamente.';

        return redirect()->route('programs.index')
            ->with('success', $message);
    }
}

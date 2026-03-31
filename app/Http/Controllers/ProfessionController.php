<?php

namespace App\Http\Controllers;

use App\Models\Profession;
use Illuminate\Http\Request;

class ProfessionController extends Controller
{
    //Mostrar todas las profesiones
    public function index(Request $request)
    {
        $query = Profession::query();

        // Búsqueda por nombre
        if ($request->filled('search')) {
            $query->where('name', 'like', "%{$request->search}%");
        }

        $professions = $query->orderBy('name')->paginate(15);

        return view('professions.index', compact('professions'));
    }

    //Mostrar el formulario para crear una nueva profesion
    public function create()
    {
        return view('professions.create');
    }

    //Guardar una nueva profesion en la base de datos
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
        ]);

        $profession = Profession::create($validated);

        // Si es una petición AJAX, devolver JSON
        if ($request->expectsJson() || $request->ajax()) {
            return response()->json([
                'success' => true,
                'profession' => $profession,
                'message' => 'Profesión creada correctamente.'
            ]);
        }

        return redirect()->route('professions.index')->with('success', 'Profesión creada correctamente.');
    }

    //Mostrar una profesion específica
    public function show(Profession $profession)
    {
        return view('professions.show', compact('profession'));
    }

    //Mostrar el formulario para editar una profesion
    public function edit(Profession $profession)
    {
        return view('professions.edit', compact('profession'));
    }

    //Actualizar una profesion en la base de datos
    public function update(Request $request, Profession $profession)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
        ]);

        $profession->update($validated);

        return redirect()->route('professions.index')->with('success', 'Profesión actualizada correctamente.');
    }

    //Alternar el estado activo/inactivo de una profesión
    public function toggleStatus(Profession $profession)
    {
        $profession->is_active = !$profession->is_active;
        $profession->save();

        return redirect()->route('professions.index')->with('success', 'Estado de la profesión actualizado correctamente.');
    }
}
